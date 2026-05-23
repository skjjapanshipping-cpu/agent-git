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
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Customershipping::select(
            'delivery_fullname as raw_name',
            'box_no',
            'customerno',
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
            \DB::raw("'' as cod"),
            \DB::raw("'' as coddata"),
            'note'
        )
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

        $results = $query->orderByRaw('customershippings.delivery_fullname ASC, ship_date DESC')
            ->take(2000)->get();

        $pileMap = $this->buildPileMap();

        // Pre-compute group summaries: boxes per (customerno + delivery_fullname)
        $groupBoxes = [];
        foreach ($results as $item) {
            $key = $item->customerno . '|||' . $item->raw_name;
            if (!isset($groupBoxes[$key])) {
                $groupBoxes[$key] = [];
            }
            if ($item->box_no) {
                $groupBoxes[$key][] = $item->box_no;
            }
        }

        $seenGroups = [];

        return $results->map(function ($item) use ($groupBoxes, $pileMap, &$seenGroups) {
            $key = $item->customerno . '|||' . $item->raw_name;
            $isFirst = !isset($seenGroups[$key]);
            $seenGroups[$key] = true;

            if ($isFirst && isset($groupBoxes[$key]) && count($groupBoxes[$key]) > 0) {
                $boxes = $groupBoxes[$key];
                $boxList = implode('+', $boxes);
                $summary = '(Box.' . $boxList . ' รวม ' . count($boxes) . ' กล่อง)';
                if ($item->customerno === 'ANW-820' && isset($pileMap[$item->raw_name])) {
                    $nameWithBox = 'กอง ' . $pileMap[$item->raw_name] . ' - ' . $item->raw_name . ' ' . $summary;
                } else {
                    $nameWithBox = $item->raw_name . ' ' . $summary;
                }
            } else {
                if ($item->customerno === 'ANW-820' && isset($pileMap[$item->raw_name])) {
                    $nameWithBox = 'กอง ' . $pileMap[$item->raw_name] . ' - ' . $item->raw_name . ' (Box.' . $item->box_no . ')';
                } else {
                    $nameWithBox = $item->raw_name . ' (Box.' . $item->box_no . ')';
                }
            }

            return collect([
                'delivery_fullname' => $nameWithBox,
                'delivery_mobile' => $item->delivery_mobile,
                'delivery_address' => $item->delivery_address,
                'delivery_subdistrict' => $item->delivery_subdistrict,
                'delivery_district' => $item->delivery_district,
                'delivery_province' => $item->delivery_province,
                'delivery_postcode' => $item->delivery_postcode,
                'weight_in_grams' => $item->weight_in_grams,
                'width' => $item->width,
                'length' => $item->length,
                'height' => $item->height,
                'cod' => '',
                'coddata' => '',
                'note' => $item->note,
            ]);
        });
    }

    /**
     * Build pile map for ANW-820 (consistent with scanner page sort order)
     */
    private function buildPileMap()
    {
        if (empty($this->etd)) return [];

        // Pile sort order — must match scanner (resources/views/scanner/pickup.blade.php):
        //   1. Normal recipients (binary strcmp ascending)
        //   2. SB-prefixed names (รับเอง — known)
        //   3. Empty / unknown names (รับเอง — ไม่ระบุผู้รับ) → very last
        $allRecipients = Customershipping::where('excel_status', '1')
            ->where('customerno', 'ANW-820')
            ->whereRaw('DATE(etd)=?', [$this->etd])
            ->whereNotNull('box_no')->where('box_no', '!=', '')
            ->pluck('delivery_fullname')
            ->unique()
            ->sort(function ($a, $b) {
                $aU = ($a === null || trim((string)$a) === '');
                $bU = ($b === null || trim((string)$b) === '');
                if ($aU && !$bU) return 1;
                if (!$aU && $bU) return -1;
                $aSB = str_starts_with((string)$a, 'SB ');
                $bSB = str_starts_with((string)$b, 'SB ');
                if ($aSB && !$bSB) return 1;
                if (!$aSB && $bSB) return -1;
                return strcmp((string)$a, (string)$b);
            })
            ->values();

        if ($allRecipients->count() <= 1) return [];

        $map = [];
        foreach ($allRecipients as $idx => $name) {
            $map[$name] = $idx + 1;
        }
        return $map;
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
            'A' => 50,
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

                $sheet->getStyle("A1:{$highestCol}1")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'CC0000'],
                    ],
                ]);

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
                    $name = preg_replace('/\s*\(Box\..*$/', '', $cellValue);

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
