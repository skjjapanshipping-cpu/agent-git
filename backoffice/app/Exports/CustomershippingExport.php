<?php

namespace App\Exports;

use App\Models\Customershipping;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CustomershippingExport implements FromCollection, WithHeadings, WithColumnWidths, WithEvents
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
            \DB::raw('weight * 1000 as weight_in_grams'),
            \DB::raw('width'),
            \DB::raw('length'),
            \DB::raw('height'),
            \DB::raw("'' as cod")
        ,\DB::raw("'' as coddata")
        ,'note')
            ->where('delivery_type_id', '!=', 1)
            ->where('excel_status', '=', '1');

        if (!empty($this->etd)) {
            $query->whereRaw('DATE(etd)=?', [$this->etd]);
        }
        if (!empty($this->customerno)) {
            $customerno = $this->customerno;
            $query->where(function($query) use ($customerno) {
                $query->whereRaw("customerno like ?", ['%' . $customerno . '%'])
                    ->orWhereRaw("REPLACE(track_no, '-', '') LIKE ?", ['%' . str_replace('-', '', $customerno) . '%']);
            });
        }

        return $query->orderByRaw('customershippings.delivery_fullname ASC, ship_date DESC')->take(2000)->get();
    }


    public function headings(): array
    {
        return [
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

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestCol = $sheet->getHighestColumn();

                // Header: พื้นแดงเข้ม ตัวหนังสือขาว
                $sheet->getStyle("A1:{$highestCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CC0000'],
                    ],
                ]);

                // สลับสีแดงอ่อนตามกลุ่มชื่อผู้รับ
                $currentName = '';
                $colorToggle = false;
                $redFill = [
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FFE0E0'],
                    ],
                ];

                for ($row = 2; $row <= $highestRow; $row++) {
                    $cellValue = $sheet->getCell('A' . $row)->getValue();
                    $name = preg_replace('/\s*\(Box\.\d+\)$/', '', $cellValue);

                    if ($name !== $currentName) {
                        $currentName = $name;
                        $colorToggle = !$colorToggle;
                    }

                    if ($colorToggle) {
                        $sheet->getStyle("A{$row}:{$highestCol}{$row}")->applyFromArray($redFill);
                    }
                }
            },
        ];
    }
}
