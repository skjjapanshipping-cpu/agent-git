<?php

namespace App\Exports;

use App\Models\Customershipping;
use Maatwebsite\Excel\Concerns\FromCollection;

use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class CustomershippingExport implements FromCollection, WithHeadings, WithColumnWidths
{
    protected $sheet;
    protected $etd;
    protected $customerno;

    public function __construct($etd,$customerno)
    {
        $this->etd=$etd;
        $this->customerno=$customerno;
    }

    /**
     * @return Customershipping
     */
    public function collection()
    {
        $query = Customershipping::select(
//            'box_no'
//            ,'delivery_fullname',
        \DB::raw("concat(delivery_fullname,' (Box.',box_no,')') as delivery_fullname"),
            \DB::raw("REPLACE(REPLACE(delivery_mobile, '-', ''), ' ', '') as delivery_mobile"),
            'delivery_address',
            'delivery_subdistrict',
            'delivery_district',
            'delivery_province',
            'delivery_postcode',
            \DB::raw('weight * 1000 as weight_in_grams'), // Convert grams to kilograms
            \DB::raw('width'),
            \DB::raw('length'),
            \DB::raw('height'),
            \DB::raw("'' as cod")
        ,\DB::raw("'' as coddata")
        ,'note')
            ->latest('etd')
            ->where('delivery_type_id', '!=', 1)
            ->where('excel_status', '=', '1');

        if (!empty($this->etd)) {
            $query->whereRaw('DATE(etd)=?', [$this->etd]);
        }
        if (!empty($this->customerno)) {
            // ใช้ where(function) เพื่อให้เงื่อนไข etd และ delivery_type_id ยังทำงานอยู่
            $customerno = $this->customerno;
            $query->where(function($query) use ($customerno) {
                $query->whereRaw("customerno like ?", ['%' . $customerno . '%'])
                    ->orWhereRaw("REPLACE(track_no, '-', '') LIKE ?", ['%' . str_replace('-', '', $customerno) . '%']);
            });
        }

//        $query->orderBy('box_no');
//        dd($this->customerno,$query->toSql(),$query->get()->toArray());
        return $query->orderByRaw('etd DESC, customerno ASC, ship_date DESC')->take(2000)->get();
    }


    public function headings(): array
    {
        return [
//            'เลขหน้ากล่อง',
            'ชื่อ-นามสกุล',
            'เบอร์โทรศัพท์',
            'ที่อยู่',
            'ตำบล',
            'อำเภอ',
            'จังหวัด',
            'รหัสไปรษณีย์',
            'น้ำหนัก (กรัม)',
            'กว้าง (ซม.)',
            'ยาว (ซม.)',
            'สูง (ซม.)',
            'เก็บเงินปลายทาง (เฉพาะรายการ COD)',
            'ข้อมูลสินค้า COD',
            'หมายเหตุ'
        ];
    }

    public function columnWidths(): array
    {
        return [
//            'A' => 11, // ขนาดเลขหน้ากล่อง
            'A' => 18,
            'B' => 11,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 11,
            'G' => 11,
            'H' => 9,
            'I' => 9,
            'J' => 9,
            'K' => 9,
            'L' => 30,
            'M' => 9,
            'N' => 30

        ];
    }


}
