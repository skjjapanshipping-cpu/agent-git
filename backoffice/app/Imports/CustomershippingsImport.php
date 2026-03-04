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


    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {

        if ($row['ship_date'] == 'วันที่่' || empty($row['customerno'])) {
//            echo "Row:" . $row['image_index'] . " <br>";
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

            $unit_price = !empty($customer->cus_unit_price)?$customer->cus_unit_price:150; //default price [ถ้าใน excel มาเป็นค่าว่างหรือ 0]
            $import_cost = $unit_price*$weight;
            
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
                'shipping_method'=>!empty($row['shipping_method']) ? intval($row['shipping_method']) : 1 //1=ทางเรือ, 2=ทางเครื่องบิน
            ]);
        } catch (QueryException $e) {
            Log::error('CustomershippingsImport QueryException: ' . $e->getMessage(), ['sql' => $e->getSql()]);
            return null;
        } catch (\Exception $e) {
            Log::error('CustomershippingsImport Exception: ' . $e->getMessage(), ['row' => $row]);
            return null;
        }
    }

    protected function getGoogleDriveFileId($url)
    {

        // if (preg_match('/[-\w]{25,}/', $url, $matches)) {
        //     return $matches[0];
        // }

        preg_match('/id=([^&]+)/', $url, $matches);
      
        if (!isset($matches[1])) {
            return $url;
        }
        return 'https://lh3.googleusercontent.com/d/' . $matches[1] . '=w500';
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
