<?php

namespace App\Console\Commands;

use App\Models\Track;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SyncGoogleSheets extends Command
{
    protected $signature = 'sheets:sync 
                            {--all : Sync all sheets instead of latest 3}
                            {--dry-run : Show what would be imported without saving}';
    
    protected $description = 'Sync tracking data from Google Sheets automatically';

    const SPREADSHEET_ID = '16trhUzB8WPNRmCLAn5jByf444oB2kjcFZZJaEb6czxo';

    public function handle()
    {
        $this->info('🔄 Starting Google Sheets sync...');
        Log::info('[SheetsSync] Starting sync');

        // Step 1: Get list of sheet tabs
        $sheets = $this->getSheetList();

        if (empty($sheets)) {
            $this->error('❌ Could not fetch sheet list from Google Sheets');
            Log::error('[SheetsSync] Could not fetch sheet list');
            return 1;
        }

        $this->info('📋 Found ' . count($sheets) . ' sheet tabs');

        // Only sync latest 3 sheets by default (newest rounds)
        if (!$this->option('all')) {
            $sheets = array_slice($sheets, -3, 3, true);
            $this->info('📌 Syncing latest 3 sheets only (use --all for all)');
        }

        $totalNew = 0;
        $totalSkipped = 0;
        $totalErrors = 0;

        foreach ($sheets as $gid => $name) {
            $this->info("  📄 Sheet: {$name} (gid={$gid})");

            $rows = $this->fetchSheetCsv($gid);

            if (empty($rows)) {
                $this->warn("     ⚠️ No data rows found");
                continue;
            }

            [$new, $skipped, $errors] = $this->importRows($rows, $name);
            $totalNew += $new;
            $totalSkipped += $skipped;
            $totalErrors += $errors;

            $this->info("     ✅ New: {$new} | Skipped: {$skipped} | Errors: {$errors}");
        }

        $summary = "Sync complete — New: {$totalNew}, Skipped: {$totalSkipped}, Errors: {$totalErrors}";
        $this->info("🏁 {$summary}");
        Log::info("[SheetsSync] {$summary}");

        return 0;
    }

    /**
     * Fetch list of sheet tabs (gid → name) from the spreadsheet HTML
     */
    private function getSheetList(): array
    {
        // ใช้ htmlview endpoint ซึ่งมี JavaScript ที่ระบุ gid + name ของทุก Tab
        $url = 'https://docs.google.com/spreadsheets/d/' . self::SPREADSHEET_ID . '/htmlview';

        try {
            $response = Http::withOptions([
                'allow_redirects' => ['max' => 5],
                'timeout' => 30,
                'verify' => false,
            ])->get($url);

            if (!$response->successful()) {
                Log::warning('[SheetsSync] Failed to fetch htmlview: ' . $response->status());
                return [];
            }

            $html = $response->body();
            $sheets = [];

            // htmlview page contains: items.push({name: "09.03.2026(เหมาห่วง)", ... gid: "200523624"});
            if (preg_match_all('/items\.push\(\{name:\s*"([^"]+)".*?gid:\s*"(\d+)"/s', $html, $matches)) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $name = $this->decodeUnicode($matches[1][$i]);
                    $gid = $matches[2][$i];
                    $sheets[$gid] = $name;
                }
            }

            return $sheets;

        } catch (\Exception $e) {
            Log::error('[SheetsSync] getSheetList error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Decode unicode escape sequences in sheet names
     */
    private function decodeUnicode(string $str): string
    {
        return preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($m) {
            return mb_convert_encoding(pack('H*', $m[1]), 'UTF-8', 'UCS-2BE');
        }, $str);
    }

    /**
     * Fetch CSV data for a specific sheet tab
     */
    private function fetchSheetCsv(string $gid): array
    {
        // ใช้ export?format=csv แทน gviz/tq เพราะ gviz รวมแถวแรกๆ เป็น header ทำให้ข้อมูลหาย
        $url = 'https://docs.google.com/spreadsheets/d/' . self::SPREADSHEET_ID
             . '/export?format=csv&gid=' . $gid;

        try {
            $response = Http::withOptions([
                'allow_redirects' => ['max' => 10],
                'timeout' => 60,
                'verify' => false,
            ])->get($url);

            if (!$response->successful()) {
                Log::warning("[SheetsSync] Failed to fetch CSV for gid={$gid}: " . $response->status());
                return [];
            }

            $csv = $response->body();
            $rows = [];
            $isFirstRow = true;

            // Parse CSV line by line
            $lines = str_getcsv($csv, "\n");
            foreach ($lines as $line) {
                $fields = str_getcsv($line);

                if (empty($fields) || count($fields) < 6) {
                    continue;
                }

                // Skip header row (first row contains column names)
                if ($isFirstRow) {
                    $isFirstRow = false;
                    continue;
                }

                // Column A should be a number (sequence)
                $seq = trim($fields[0] ?? '');
                if ($seq === '' || !is_numeric($seq)) {
                    continue;
                }

                $rows[] = $fields;
            }

            return $rows;

        } catch (\Exception $e) {
            Log::error("[SheetsSync] fetchSheetCsv error (gid={$gid}): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Import parsed rows into the tracks table
     * 
     * Column mapping from Google Sheet:
     * 0 = ลำดับ (sequence)
     * 1 = วันที่ (date) → source_date
     * 2 = URL หน้ากล่อง (image) → skip
     * 3 = รหัสลูกค้า (customer) → customer_name
     * 4 = รหัสสินค้า (item code) → skip
     * 5 = เลขพัสดุ (tracking no) → track_no
     * 6 = COD → cod
     * 7 = น้ำหนัก (weight) → weight
     * 8 = เลขกล่อง (box no) → skip
     * 9 = โกดัง (warehouse) → skip
     * 10 = รอบปิดตู้ (ETD) → ship_date
     * 11 = หมายเหตุ (note) → note
     */
    private function importRows(array $rows, string $sheetName): array
    {
        $new = 0;
        $skipped = 0;
        $errors = 0;

        // Build lookup of existing track_no (normalized without hyphens)
        $existingTrackNos = Track::pluck('track_no')
            ->map(fn($t) => str_replace('-', '', (string)$t))
            ->flip()
            ->toArray();

        // Excluded track_no keywords
        $excluded = ['ไม่มีเลขพัสดุ', 'เลขพัสดุไม่ชัด', 'เลขพัสดุขาดครึ่ง', 'รับตามบ้าน'];

        foreach ($rows as $fields) {
            $trackNo = trim($fields[5] ?? '');
            if (empty($trackNo)) continue;

            // Skip excluded keywords
            $skip = false;
            foreach ($excluded as $keyword) {
                if (mb_stripos($trackNo, $keyword) !== false) {
                    $skip = true;
                    break;
                }
            }
            if ($skip) continue;

            // Check duplicate by normalized track_no
            $normalized = str_replace('-', '', $trackNo);
            if (isset($existingTrackNos[$normalized])) {
                $skipped++;
                continue;
            }

            // Parse customer name (อนุญาตให้ว่างได้)
            $customerName = trim($fields[3] ?? '') ?: null;

            // Parse source_date (DD/MM/YYYY)
            $sourceDate = $this->parseDate(trim($fields[1] ?? ''), 'd/m/Y');

            // Parse ship_date / ETD (DD.MM.YYYY)
            $shipDate = $this->parseDate(trim($fields[10] ?? ''), 'd.m.Y');

            // Parse COD (remove ¥ and commas)
            $cod = $this->parseCod(trim($fields[6] ?? ''));

            // Parse weight
            $weight = $this->parseWeight(trim($fields[7] ?? ''));

            // Note
            $note = trim($fields[11] ?? '') ?: null;

            if ($this->option('dry-run')) {
                $this->line("     [DRY] {$customerName} | {$trackNo} | {$weight}kg | {$sourceDate}");
                $new++;
                $existingTrackNos[$normalized] = true;
                continue;
            }

            try {
                Track::create([
                    'customer_name' => $customerName,
                    'track_no' => $trackNo,
                    'cod' => $cod,
                    'weight' => $weight,
                    'source_date' => $sourceDate,
                    'ship_date' => $shipDate,
                    'note' => $note,
                    'status' => 1, // auto-confirmed
                ]);

                $existingTrackNos[$normalized] = true;
                $new++;
            } catch (\Exception $e) {
                $errors++;
                Log::error("[SheetsSync] Insert error: " . $e->getMessage(), [
                    'track_no' => $trackNo,
                    'sheet' => $sheetName,
                ]);
            }
        }

        return [$new, $skipped, $errors];
    }

    /**
     * Parse date string to Y-m-d format
     */
    private function parseDate(string $dateStr, string $format): ?string
    {
        if (empty($dateStr)) return null;

        try {
            return Carbon::createFromFormat($format, $dateStr)->format('Y-m-d');
        } catch (\Exception $e) {
            // Try alternative formats
            $formats = ['d/m/Y', 'd.m.Y', 'Y-m-d', 'd-m-Y'];
            foreach ($formats as $f) {
                try {
                    return Carbon::createFromFormat($f, $dateStr)->format('Y-m-d');
                } catch (\Exception $e) {
                    continue;
                }
            }
            return null;
        }
    }

    /**
     * Parse COD value (remove ¥, commas, spaces)
     */
    private function parseCod(string $codStr): ?float
    {
        if (empty($codStr)) return null;
        $cleaned = str_replace(['¥', ',', ' ', '　'], '', $codStr);
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    /**
     * Parse weight value
     */
    private function parseWeight(string $weightStr): ?float
    {
        if (empty($weightStr)) return null;
        $cleaned = str_replace([',', ' '], '', $weightStr);
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }
}
