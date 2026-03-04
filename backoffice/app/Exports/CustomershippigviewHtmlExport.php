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
use PhpOffice\PhpSpreadsheet\Worksheet\Column;
class CustomershippigviewHtmlExport implements FromView,WithColumnFormatting,WithColumnWidths,WithEvents
{
    protected $etd;
    public function __construct($etd,$customerno)
    {
        $this->etd=$etd;
        $this->customerno=$customerno;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $params = ['etd'=>$this->etd,'customerno'=>$this->customerno];
        $queryAll = Customershipping::latest('ship_date')->where('excel_status','=','1');
//            ->selectRaw("*,CONCAT(delivery_fullname,' ',delivery_address,' ต.',delivery_subdistrict,' อ.',delivery_district,' จ.',delivery_province,' ',delivery_postcode) as delivery_address");;
        $customershippings = $queryAll->where(function ($query) use ($params) {
            if (!empty($params['etd']))
                $customershippings = $query->whereRaw("DATE(etd)=?",[$params['etd']]);
            if (!empty($params['customerno']))
                $customershippings = $query->whereRaw("lower(customerno)=?",[strtolower($params['customerno'])]);
         })->orderByRaw('customerno asc')->take(1000);

//        dd($queryAll->toSql(),$customershippings->get()->toArray());
        return view('customershippingview.export',[
            'customershippings'=>$customershippings->get()
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


    public function columnWidths(): array
    {
        return [
            'A' => 11,
            'B' => 16,
            'C' => 10,
            'D' => 15,
            'E' => 8,
            'F' => 8,
            'G' => 8,
            'H' => 8,
            'I' => 16,
            'J' => 8,
            'K' => 8,
            'L' => 11,
            'M' => 15,
            'N' => 27,
            'O' => 27
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Center align text in columns A and B
                $event->sheet->getStyle('A:O')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                $event->sheet->getColumnDimension('K')->setVisible(false);
                $event->sheet->getStyle('N')->getAlignment()->setWrapText(true);

                $event->sheet->getStyle('A1:O' . $event->sheet->getHighestRow())
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
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
}
