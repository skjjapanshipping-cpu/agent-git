<?php

namespace App\Exports;

use App\Models\Customershipping;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;


class CustomershippigHtmlExport implements FromView,WithColumnFormatting,WithColumnWidths,WithEvents
{
    protected $etd;
    protected $end_date;
    protected $customerno;
    protected $status;
    protected $pay_status;
    public function __construct($etd,$end_date,$customerno,$status,$pay_status)
    {
        $this->etd=$etd;
        $this->customerno=$customerno;
        $this->status=$status;
        $this->pay_status=$pay_status;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $params = ['etd'=>$this->etd,'customerno'=>$this->customerno];
        // dd($params);
        $queryAll = Customershipping::where('excel_status','=','1')->whereRaw("DATE(etd)=?",[$this->etd]);
//        ->selectRaw("CONCAT(delivery_fullname,' ',delivery_address,' ต.',delivery_subdistrict,' อ.',delivery_district),' จ.',delivery_province,' ',delivery_postcode) as ");
        $customershippings = $queryAll->where(function ($query) use ($params) {
            if (!empty($params['etd']))
                $customershippings = $query->whereRaw("DATE(etd)=?",[$params['etd']]);
            if (!empty($params['customerno']))
                $customershippings = $query->whereRaw("lower(customerno)=?",[strtolower($params['customerno'])]);
         })->orderByRaw('etd DESC, customerno ASC, ship_date DESC')->take(2000)->get();

         // นับ box_count ต่อลูกค้า ด้วย query เดียว (แทน N+1)
         $customerStats = Customershipping::where('excel_status', '1')
             ->whereRaw("DATE(etd)=?", [$this->etd])
             ->selectRaw('customerno, COUNT(*) as cnt')
             ->groupBy('customerno')
             ->get()
             ->pluck('cnt', 'customerno')
             ->toArray();

         foreach ($customershippings as $shipping) {
             $shipping->box_count = $customerStats[$shipping->customerno] ?? 0;
         }

    //    dd($queryAll->toSql());
        return view('customershipping.export',[
            'customershippings'=>$customershippings
        ]);
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            // No formatting needed for columns A and B (already set width)
        ];
    }

    public function columnAlignment(){

    }
    public function columnWidths(): array
    {
        return [
            'A' => 11,
            'B' => 10,
            'C' => 10,
            'D' => 15,
            'E' => 8,
            'F' => 8,
            'G' => 11,
            'H' => 13,
            'I' => 33
            // 'J' => 8.5,
            // 'K' => 8,
            // 'L' => 11,
            // 'M' =>18,
            // 'N' => 18
//            'O' => 27
        ];
    }
    public function registerEvents(): array
    {
        
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Center align text in columns A and B
                $event->sheet->getStyle('A:I')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $event->sheet->getStyle('I')->getAlignment()->setWrapText(true);

                // ตั้งค่า border สำหรับทุกคอลัมน์
                $event->sheet->getStyle('A1:I' . $event->sheet->getHighestRow())
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);
                
                // ลบ border ในส่วนที่ merge (เซลล์ว่างในคอลัมน์ A)
                $this->removeMergedCellBorders($event);
                
                // ตั้งค่าสีแดงสำหรับคอลัมน์ C 
                $event->sheet->getStyle('C1:C' . $event->sheet->getHighestRow())
                    ->applyFromArray([
                        'font' => [
                            'color' => ['argb' => Color::COLOR_RED],
                        ],
                    ]);
                
                // ตั้งค่าให้คอลัมน์ A-C เป็นตัวหนา
                $event->sheet->getStyle('A1:C' . $event->sheet->getHighestRow())
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                    ]);
                // Right align text in columns C and D
//                $event->sheet->getStyle('C:D')->applyFromArray([
//                    'alignment' => [
//                        'horizontal' => Alignment::HORIZONTAL_RIGHT,
//                        'vertical' => Alignment::VERTICAL_CENTER,
//                    ],
//                ]);
            },
        ];
    }
    
    private function removeMergedCellBorders(AfterSheet $event)
    {
        $sheet = $event->sheet;
        $highestRow = $sheet->getHighestRow();
        
        // วนลูปผ่านทุกแถวเพื่อหาส่วนที่ merge
        for ($row = 2; $row <= $highestRow; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            
            // ถ้าเซลล์ว่าง (ส่วนที่ merge) ให้ลบ border
            if (empty($cellValue)) {
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);
            } else {
                $sheet->getStyle('A' . $row)->applyFromArray([
                    'borders' => [
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN, 
                            'color' => ['argb' => '000000'],
                        ],
                        'top' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => '000000'],
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_NONE,
                            
                        ],
                    ],
                ]);
            }
        }
    }
}
