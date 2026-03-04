<?php

namespace App\Exports;

use App\Models\Customerorder;

use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CustomerorderHtmlExport implements FromView, WithColumnFormatting, WithColumnWidths, WithEvents
{
    protected $start_date;
    protected $end_date;
    protected $customerno;
    protected $status;
    protected $shipping_status;
    protected $customerorder_ids;
    protected $include_image;

    public function __construct($start_date, $end_date, $customerno, $status, $shipping_status, $customerorder_ids = null, $include_image = true)
    {
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->customerno = $customerno;
        $this->status = $status;
        $this->shipping_status = $shipping_status;
        $this->customerorder_ids = $customerorder_ids;
        $this->include_image = $include_image;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function view(): View
    {
        $queryAll = Customerorder::latest('created_at');

        // ถ้ามี customerorder_ids ที่ติ๊ก ให้ filter ตาม IDs เหล่านั้นเท่านั้น
        if (!empty($this->customerorder_ids)) {
            $ids = is_array($this->customerorder_ids) ? $this->customerorder_ids : explode(',', $this->customerorder_ids);
            $customerorders = $queryAll->whereIn('id', $ids)->orderByRaw('customerno asc')->get();
        } else {
            $customerorders = $queryAll->where(function ($query) {
                if (!empty($this->customerno)) {
                    $query->where(function ($q) {
                        $q->whereRaw("customerno like ?", ['%' . $this->customerno . '%'])
                            ->orWhereRaw("link like ?", ['%' . $this->customerno . '%'])
                            ->orWhereRaw("DATE_FORMAT(order_date, '%d/%m/%Y') like ?", ['%' . $this->customerno . '%'])
                            ->orWhereRaw("REPLACE(tracking_number, '-', '') LIKE ?", ['%' . str_replace('-', '', $this->customerno) . '%']);
                    });
                }

                if (!empty($this->status)) {
                    $query->where('status', $this->status);
                }

                if (!empty($this->shipping_status)) {
                    $query->where('shipping_status', $this->shipping_status);
                }

                if (!empty($this->start_date) && !empty($this->end_date)) {
                    // ค้นหาจากวันที่ (DATE) 
                    $query->whereRaw('DATE(order_date) >= ?', [$this->start_date])
                          ->whereRaw('DATE(order_date) <= ?', [$this->end_date]);
                } else if (!empty($this->start_date)) {
                    // ค้นหาจากวันที่ (DATE) เฉพาะวันเดียว
                    $query->whereRaw('DATE(order_date) = ?', [$this->start_date]);
                }
            })->orderByRaw('customerno asc')->take(3000)->get();
        }

        return view('customerorder.export', [
            'customerorders' => $customerorders,
            'include_image' => $this->include_image,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date
        ]);
    }

    /**
     * @return array
     */
    public function columnFormats(): array
    {
        return [
            // No formatting needed for columns
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,   // No
            'B' => 18,  // วันที่
            'C' => 18,  // รหัสลูกค้า (รวม itemno)
            'D' => 15,  // รูปภาพ
            'E' => 30,  // URL
            'F' => 8,   // จำนวน
            'G' => 12,  // เงินเยน
            'H' => 10,  // เรท
            'I' => 12,  // เงินบาท
            'J' => 15,  // Buyer Status
            'K' => 15,  // เลขพัสดุ
            'L' => 12,  // รอบปิดตู้
            'M' => 15,  // สถานะขนส่ง
            'N' => 30,  // หมายเหตุ
            'O' => 30,  // Note Admin
            'P' => 10,  // Items2
            'Q' => 10,  // Boss
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $highestRow = $event->sheet->getHighestRow();
                
                // Row 1: Title row - ชิดซ้าย, ตัวหนา, สีแดง, ฟอนต์ใหญ่
                $event->sheet->getStyle('A1')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['argb' => 'FFFF0000'],
                        'size' => 14,
                    ],
                ]);
                // ลบ border ของ row 1
                $event->sheet->getStyle('A1:Q1')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);
                
                // Row 2 onwards: Center align
                $event->sheet->getStyle('A2:Q' . $highestRow)->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);

                // Wrap text for columns with long content
                $event->sheet->getStyle('E')->getAlignment()->setWrapText(true); // URL
                $event->sheet->getStyle('N')->getAlignment()->setWrapText(true); // หมายเหตุ

                // Set borders for data rows (row 2 onwards)
                $event->sheet->getStyle('A2:Q' . $highestRow)
                    ->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => '000000'],
                            ],
                        ],
                    ]);
            },
        ];
    }
}

