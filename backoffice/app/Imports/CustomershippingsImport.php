<?php

namespace App\Imports;

use App\Models\Customerorder;
use App\Models\Customershipping;
use App\Models\Track;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Maatwebsite\Excel\Concerns\ToModel;
use App\User;
use Illuminate\Support\Facades\Log;


class CustomershippingsImport implements ToModel
{
    protected $errors = [];
    protected $skipped = [];
    protected $rowIndex = 0;

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSkipped(): array
    {
        return $this->skipped;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->rowIndex++;

        // Header row — silently ignore (no warning).
        if (isset($row['ship_date']) && $row['ship_date'] === 'วันที่่') {
            return null;
        }

        // True empty row — silently ignore.
        $hasAnyData = !empty($row['customerno']) || !empty($row['track_no'])
            || !empty($row['box_no']) || !empty($row['box_image']);
        if (!$hasAnyData) {
            return null;
        }

        // Partial data (admin probably forgot a field) — log so admin can fix.
        if (empty($row['customerno'])) {
            $this->skipped[] = "แถว {$this->rowIndex}: ขาด 'รหัสลูกค้า' "
                . "(track_no=" . ($row['track_no'] ?? '-') . ", "
                . "box_no=" . ($row['box_no'] ?? '-') . ")";
            return null;
        }
//        dd($row);
        $shipDate = !empty($row['ship_date'])
            ? (is_numeric($row['ship_date'])
                ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['ship_date']))->format('Y-m-d')
                : Carbon::createFromFormat('d/m/Y', $row['ship_date'])->format('Y-m-d'))
            : null;

        $etd = !empty($row['etd'])
            ? (is_numeric($row['etd'])
                ? Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['etd']))->format('Y-m-d')
                : Carbon::createFromFormat('d/m/Y', $row['etd'])->format('Y-m-d'))
            : null;
// dd($etd);
        try {
            $boxImg ='';
            $prodImg='';
            if (!empty($row['box_image'])) {
             
                
                $boxImg=$this->getGoogleDriveFileId($row['box_image']);//$this->uploadImage($row['box_image'], $row['image_index']);
                
            }



            $import_cost=0;
            $iswholeprice=0; //ราคาเหมา 0/1
            $weight = !empty(trim($row['weight']))?trim($row['weight']):1;

            $customer = User::where('customerno', $row['customerno'])->first();

            // ป้องกัน fatal error: ถ้าหาลูกค้าไม่เจอ → log แล้ว skip แถว
            if (!$customer) {
                $errMsg = "แถว {$this->rowIndex}: ไม่พบลูกค้ารหัส '{$row['customerno']}' ในระบบ — ข้ามแถวนี้";
                Log::warning('CustomershippingsImport: customer not found', ['customerno' => $row['customerno']]);
                $this->errors[] = $errMsg;
                return null;
            }

            // === อ่าน shipping_method ก่อนคำนวณ unit_price (1=ทางเรือ, 2=ทางเครื่องบิน) ===
            $shippingMethod = !empty($row['shipping_method'])
                ? intval($row['shipping_method'])
                : Customershipping::METHOD_SEA;

            // เลือก unit_price ของลูกค้าตาม mode (เครื่องบิน → cus_unit_price_air, เรือ → cus_unit_price)
            if ($shippingMethod == Customershipping::METHOD_AIR) {
                $unitPriceFromCustomer = $customer->cus_unit_price_air ?? null;
            } else {
                $unitPriceFromCustomer = $customer->cus_unit_price ?? null;
            }
            // Fallback: ถ้าลูกค้าไม่ได้ตั้งราคาของ mode นั้น → ใช้ default ของระบบ (เรือ=150, อากาศ=450)
            $unit_price = !empty($unitPriceFromCustomer)
                ? $unitPriceFromCustomer
                : Customershipping::getDefaultUnitPrice($shippingMethod);
            $import_cost = $unit_price * $weight;

            // ตั้งค่า delivery_type_id และข้อมูลที่อยู่ตามลูกค้า
            $delivery_type_id = 1; // default = รับเอง
            $delivery_fullname = '';
            $delivery_mobile = '';
            $delivery_address = '';
            $delivery_province = '';
            $delivery_district = '';
            $delivery_subdistrict = '';
            $delivery_postcode = '';
            
            if ($customer && $customer->delivery_type_id == 2) {
                // ถ้าลูกค้าตั้งค่าเป็น "ที่อยู่ปัจจุบัน" ให้ใช้ข้อมูลของลูกค้า
                $delivery_type_id = 2;
                $delivery_fullname = $customer->name ?? '';
                $delivery_mobile = $customer->mobile ?? '';
                $delivery_address = $customer->addr ?? '';
                $delivery_province = $customer->province ?? '';
                $delivery_district = $customer->distrinct ?? '';
                $delivery_subdistrict = $customer->subdistrinct ?? '';
                $delivery_postcode = $customer->postcode ?? '';
            }

            if(!empty(trim($row['unit_price']))){
                if(trim($row['unit_price'])=='ราคาเหมา'){
                    $unit_price=0;
                    $iswholeprice=1;//ราคาเหมา
                    $import_cost=$row['import_cost'];
                }else{
                    $unit_price=trim($row['unit_price']);
                    $import_cost = $unit_price*$weight;
                }

            }
            $customerorder = null;
           

//            dd($customerorder->toArray(),$row);
            if (!empty($row['product_image'])) {
        //    echo $row['customerno'].'[p]:'.$row['product_image'] ." <br>";
                $prodImg=$this->uploadImage($row['product_image'], $row['image_index'] . '' . $row['image_index']);
            }else{
//                dd($customerorder->toArray(),$row);
                if(!empty($row['itemno']) && 
                   ($customerorder = Customerorder::where('customerno', $row['customerno'])
                        ->where('itemno', $row['itemno'])
                        ->first())){
                    $prodImg = 'uploads/'.$customerorder->image_link;
                    // dd($prodImg);
                }else{
                    $prodImg = '';
                }
            }
// if( strtoupper($row['customerno'])=='ANW-734'){
//     dd($row,$customer);
// }

            return  Customershipping::create([
                'ship_date' => $shipDate,
                'customerno' => str_replace(' ', '', $row['customerno']),//รหัสลูกค้า
                'track_no' => $row['track_no'],//เลขพัสดุ
                'cod' => $row['cod'],//cod
                'cod_rate' => \App\Models\Dailyrate::getCodRate(),//cod rate ณ วันที่ import
                'weight' => $row['weight'],//น้ำหนัก
                'unit_price' => $unit_price,//หน่วยละ
                'import_cost' => $import_cost,//ค่านำเข้า
                'box_image' => $boxImg,//รูปหน้ากล่อง
                'product_image' => $prodImg,//รูปสินค้า
                'box_no' => $row['box_no'],//เลขกล่อง
                'warehouse' => $row['warehouse'],//โกดัง
                'etd' => $etd,//รอบปีดตู้
                'status' => $row['status'],//สถานะ
                'delivery_type_id' => $delivery_type_id,//วิธีการจัดส่ง
                'delivery_fullname' => $delivery_fullname,//ชื่อ-นามสกุล
                'delivery_mobile' => $delivery_mobile,//เบอร์โทร
                'delivery_address' => $delivery_address,//ที่อยู่จัดส่งในไทย
                'delivery_province' => $delivery_province,//จังหวัด
                'delivery_district' => $delivery_district,//อำเภอ
                'delivery_subdistrict' => $delivery_subdistrict,//ตำบล
                'delivery_postcode' => $delivery_postcode,//รหัสไปรษณีย์
                'note' => $row['note'],//หมายเหตุ
                'width'=>$row['width'],//กว้าง
                'length'=>$row['length'],//ยาว
                'height'=>$row['height'],//สูง
                'itemno'=>$row['itemno'],//ItemNo
                'iswholeprice'=>$iswholeprice, //ราคาเหมา
                'note_admin'=>$row['note_admin'], //หมายเหตุจากผู้ดูแลระบบ
                'shipping_method'=>$shippingMethod //1=ทางเรือ, 2=ทางเครื่องบิน
            ]);
        } catch (QueryException $e) {
            $errMsg = "แถว {$this->rowIndex}: {$row['customerno']} (Track: {$row['track_no']}) — " . $this->friendlyDbError($e->getMessage());
            Log::error('CustomershippingsImport QueryException: ' . $e->getMessage(), ['sql' => $e->getSql()]);
            $this->errors[] = $errMsg;
            return null;
        } catch (\Exception $e) {
            $errMsg = "แถว {$this->rowIndex}: {$row['customerno']} (Track: {$row['track_no']}) — {$e->getMessage()}";
            Log::error('CustomershippingsImport Exception: ' . $e->getMessage(), ['row' => $row]);
            $this->errors[] = $errMsg;
            return null;
        }
    }

    protected function friendlyDbError(string $msg): string
    {
        if (preg_match("/Data too long for column '(\w+)'/", $msg, $m)) {
            return "ข้อมูลคอลัมน์ '{$m[1]}' ยาวเกินกว่าที่ฐานข้อมูลรองรับ";
        }
        if (preg_match("/Duplicate entry '(.+)' for key/", $msg, $m)) {
            return "ข้อมูลซ้ำ: {$m[1]}";
        }
        if (str_contains($msg, 'cannot be null')) {
            return "มีข้อมูลบังคับที่เป็นค่าว่าง";
        }
        return $msg;
    }

    /**
     * Convert any Google Drive share URL into a directly-viewable image URL.
     * Supports all common Drive URL formats:
     *   - https://drive.google.com/file/d/FILE_ID/view?usp=sharing   (new)
     *   - https://drive.google.com/open?id=FILE_ID                   (old)
     *   - https://drive.google.com/uc?id=FILE_ID                     (download)
     *   - https://drive.google.com/d/FILE_ID/                        (short)
     *   - bare FILE_ID
     */
    protected function getGoogleDriveFileId($url)
    {
        if (empty($url)) return $url;

        $patterns = [
            '/\/file\/d\/([a-zA-Z0-9_-]{25,})/',   // /file/d/ID/view
            '/\/d\/([a-zA-Z0-9_-]{25,})/',         // /d/ID/...
            '/[?&]id=([a-zA-Z0-9_-]{25,})/',       // ?id=ID or &id=ID
            '/^([a-zA-Z0-9_-]{25,})$/',            // bare ID
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $url, $matches)) {
                return 'https://lh3.googleusercontent.com/d/' . $matches[1] . '=w500';
            }
        }

        return $url;
    }


    protected function uploadImage($image, $number)
    {
        $imageData = file_get_contents($image);
//        dd($imageData);
        $imagePath = ''; // กำหนดตัวแปรเก็บที่อยู่ของไฟล์รูปภาพ
        $uploadUrl = config('app.upload_url') . '/excel_images';

        usleep(100000);
        $imageName = time() . '-' . $number . '.png'; // สร้างชื่อไฟล์รูปภาพ

        $imagePath = $uploadUrl . '/' . $imageName; // สร้างที่อยู่ของไฟล์รูปภาพ


        file_put_contents($imagePath, $imageData);
        return $imagePath;

    }

    protected function isImage($value)
    {
        // ตรวจสอบค่าว่าเป็น URL ของรูปภาพหรือไม่
        // โดยอาจใช้เงื่อนไขต่าง ๆ อื่น ๆ ตามลักษณะของข้อมูลที่คุณจะต้องการตรวจสอบ
        return filter_var($value, FILTER_VALIDATE_URL) && getimagesize($value);
    }


}
