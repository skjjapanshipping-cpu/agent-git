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
use Illuminate\Support\Facades\Auth;
class CustomershippigviewHtmlExport implements FromView,WithColumnFormatting,WithColumnWidths,WithEvents
{
    protected $etd;
    protected $customerno;
    protected $recipient_filter;
    public function __construct($etd,$customerno,$recipient_filter=null)
    {
        $this->etd=$etd;
        // Always use authenticated user's customerno for security
        $this->customerno = Auth::check() ? Auth::user()->customerno : $customerno;
        $this->recipient_filter=$recipient_filter;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        $queryAll = Customershipping::where('excel_status','=','1')
            ->where('customerno', $this->customerno);

        if (!empty($this->etd)) {
            $queryAll->whereRaw("DATE(etd)=?", [$this->etd]);
        }

        if (!empty($this->recipient_filter)) {
            if ($this->recipient_filter === '__empty__') {
                $queryAll->where(function($q) {
                    $q->whereNull('delivery_fullname')->orWhere('delivery_fullname', '');
                });
            } else {
                $queryAll->where('delivery_fullname', $this->recipient_filter);
            }
        }

        $customershippings = $queryAll->orderBy('ship_date', 'desc')->take(1000)->get();

        return view('customershippingview.export',[
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


    public function columnWidths(): array
    {
        return [
            'A' => 6,   // NO
            'B' => 13,  // การจัดส่ง
            'C' => 12,  // วันที่
            'D' => 16,  // รูปหน้ากล่อง
            'E' => 18,  // เลขพัสดุ
            'F' => 8,   // COD
            'G' => 8,   // น้ำหนัก
            'H' => 10,  // ค่านำเข้า
            'I' => 10,  // รูปสินค้า
            'J' => 10,  // เลขกล่อง
            'K' => 12,  // วันที่ใส่ตู้
            'L' => 10,  // ประเภท
            'M' => 16,  // สถานะ
            'N' => 30,  // ที่อยู่จัดส่ง
            'O' => 20,  // หมายเหตุ
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
                $event->sheet->getStyle('N')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('N')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

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
