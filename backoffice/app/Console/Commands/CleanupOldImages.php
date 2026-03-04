<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupOldImages extends Command
{
    protected $signature = 'images:cleanup 
                            {--dry-run : แสดงไฟล์ที่จะลบ โดยไม่ลบจริง}
                            {--environment=local : Environment (local or server)}';

    protected $description = 'ลบรูปภาพถาวรที่เก่ากว่า 6 เดือน (เรียงจากเก่าสุด, ครั้งละ 500 ไฟล์)';

    private $basePath;
    private $uploadsPath;

    const MONTHS_OLD = 6;           // ลบไฟล์ที่เก่ากว่า 6 เดือน
    const MAX_FILES_PER_RUN = 500;  // ลบครั้งละ 500 ไฟล์ (ไม่ให้ CPU หนัก)

    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        $env = $this->option('environment');

        $this->setupPaths($env);

        $cutoffDate = Carbon::now()->subMonths(self::MONTHS_OLD);

        $this->info("=== ลบรูปภาพเก่ากว่า " . self::MONTHS_OLD . " เดือน (ก่อน {$cutoffDate->format('Y-m-d')}) ===");
        $this->info("Environment: {$env} | ลบสูงสุด: " . self::MAX_FILES_PER_RUN . " ไฟล์/รอบ");

        if ($isDryRun) {
            $this->warn("โหมด Dry Run - จะไม่ลบจริง");
        }

        $totalDeleted = 0;

        // 1. ลบรูปจาก customerorders (เรียงเก่าสุดก่อน)
        $totalDeleted += $this->cleanupOrderImages($cutoffDate, $isDryRun, self::MAX_FILES_PER_RUN - $totalDeleted);

        // 2. ลบรูปจาก customershippings (เรียงเก่าสุดก่อน)
        if ($totalDeleted < self::MAX_FILES_PER_RUN) {
            $totalDeleted += $this->cleanupShippingImages($cutoffDate, $isDryRun, self::MAX_FILES_PER_RUN - $totalDeleted);
        }

        // 3. ลบไฟล์ orphan (อยู่ใน uploads แต่ไม่มีใน database)
        if ($totalDeleted < self::MAX_FILES_PER_RUN) {
            $totalDeleted += $this->cleanupOrphanFiles($cutoffDate, $isDryRun, self::MAX_FILES_PER_RUN - $totalDeleted);
        }

        $this->info("\n=== สรุป: " . ($isDryRun ? "จะลบ" : "ลบแล้ว") . " {$totalDeleted} ไฟล์ ===");
        Log::info("images:cleanup - {$env} - ลบ {$totalDeleted} ไฟล์" . ($isDryRun ? " (dry-run)" : ""));

        return 0;
    }

    private function setupPaths($env)
    {
        if ($env === 'server') {
            $this->basePath = '/var/www/vhosts/skjjapanshipping.com/httpdocs/skjtrack';
        } else {
            $this->basePath = public_path();
        }
        $this->uploadsPath = $this->basePath . '/uploads';
    }

    /**
     * ลบรูปจาก customerorders — เรียงจากเก่าสุด
     */
    private function cleanupOrderImages($cutoffDate, $isDryRun, $limit)
    {
        $this->info("\n--- customerorders ---");

        $records = DB::table('customerorders')
            ->whereNotNull('image_link')
            ->where('image_link', '!=', '')
            ->where('created_at', '<', $cutoffDate)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get(['id', 'image_link', 'created_at']);

        $deletedCount = 0;
        $deletedFiles = [];

        foreach ($records as $record) {
            $fileName = $record->image_link;
            // ข้ามไฟล์ซ้ำ
            if (in_array($fileName, $deletedFiles)) continue;

            $fullPath = $this->uploadsPath . '/' . $fileName;

            if (file_exists($fullPath)) {
                if ($isDryRun) {
                    $this->line("  จะลบ: {$fileName} (ID:{$record->id}, {$record->created_at})");
                } else {
                    try {
                        unlink($fullPath);
                        $this->line("  ลบแล้ว: {$fileName} ({$record->created_at})");
                    } catch (\Exception $e) {
                        $this->error("  ลบไม่ได้: {$fileName} - " . $e->getMessage());
                        continue;
                    }
                }
                $deletedFiles[] = $fileName;
                $deletedCount++;
            }
        }

        $this->info("  ผลลัพธ์: {$deletedCount} ไฟล์ (จาก {$records->count()} รายการ)");
        return $deletedCount;
    }

    /**
     * ลบรูปจาก customershippings — เรียงจากเก่าสุด
     */
    private function cleanupShippingImages($cutoffDate, $isDryRun, $limit)
    {
        $this->info("\n--- customershippings ---");

        $records = DB::table('customershippings')
            ->whereNotNull('product_image')
            ->where('product_image', '!=', '')
            ->where('created_at', '<', $cutoffDate)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get(['id', 'product_image', 'created_at']);

        $deletedCount = 0;
        $deletedFiles = [];

        foreach ($records as $record) {
            $imagePath = $record->product_image;
            $fileName = basename($imagePath);
            if (in_array($fileName, $deletedFiles)) continue;

            // product_image อาจเก็บเป็น "uploads/xxx" หรือ "uploads/excel_images/xxx"
            $fullPath = $this->basePath . '/' . $imagePath;

            if (file_exists($fullPath)) {
                if ($isDryRun) {
                    $this->line("  จะลบ: {$imagePath} (ID:{$record->id}, {$record->created_at})");
                } else {
                    try {
                        unlink($fullPath);
                        $this->line("  ลบแล้ว: {$imagePath} ({$record->created_at})");
                    } catch (\Exception $e) {
                        $this->error("  ลบไม่ได้: {$imagePath} - " . $e->getMessage());
                        continue;
                    }
                }
                $deletedFiles[] = $fileName;
                $deletedCount++;
            }
        }

        $this->info("  ผลลัพธ์: {$deletedCount} ไฟล์ (จาก {$records->count()} รายการ)");
        return $deletedCount;
    }

    /**
     * ลบไฟล์ orphan (อยู่ใน uploads แต่ไม่มีใน database) — เรียงจากเก่าสุด
     */
    private function cleanupOrphanFiles($cutoffDate, $isDryRun, $limit)
    {
        $this->info("\n--- ไฟล์ orphan (ไม่มีใน database) ---");

        $cutoffTimestamp = $cutoffDate->timestamp;

        // หาไฟล์รูปทั้งหมดใน uploads
        $files = glob($this->uploadsPath . '/*.{jpg,jpeg,png,gif,JPG,JPEG,PNG,GIF}', GLOB_BRACE);
        if (empty($files)) {
            $this->info("  ไม่พบไฟล์ใน uploads/");
            return 0;
        }

        // เรียงจากเก่าสุด
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        // ดึงรายชื่อไฟล์ที่ยังใช้ใน database
        $usedFiles = $this->getUsedFiles();

        $deletedCount = 0;

        foreach ($files as $file) {
            if ($deletedCount >= $limit) break;
            $mtime = @filemtime($file);
            if ($mtime === false || $mtime >= $cutoffTimestamp) continue;

            $fileName = basename($file);

            // ข้ามไฟล์ที่ยังใช้ใน database
            if (isset($usedFiles[$fileName])) continue;

            $fileDate = date('Y-m-d', @filemtime($file) ?: 0);

            if ($isDryRun) {
                $this->line("  จะลบ orphan: {$fileName} ({$fileDate})");
            } else {
                try {
                    unlink($file);
                    $this->line("  ลบ orphan: {$fileName} ({$fileDate})");
                } catch (\Exception $e) {
                    $this->error("  ลบไม่ได้: {$fileName} - " . $e->getMessage());
                    continue;
                }
            }
            $deletedCount++;
        }

        $this->info("  ผลลัพธ์: {$deletedCount} ไฟล์ orphan");
        return $deletedCount;
    }

    /**
     * ดึงรายชื่อไฟล์ที่ยังใช้ใน database (ใช้ hash map เพื่อค้นหาเร็ว)
     */
    private function getUsedFiles()
    {
        $usedFiles = [];

        // จาก customerorders — เฉพาะข้อมูลที่ยังไม่เก่ากว่า 6 เดือน
        $orders = DB::table('customerorders')
            ->whereNotNull('image_link')
            ->where('image_link', '!=', '')
            ->where('created_at', '>=', Carbon::now()->subMonths(self::MONTHS_OLD))
            ->pluck('image_link');

        foreach ($orders as $file) {
            $usedFiles[basename($file)] = true;
        }

        // จาก customershippings — เฉพาะข้อมูลที่ยังไม่เก่ากว่า 6 เดือน
        $shippings = DB::table('customershippings')
            ->whereNotNull('product_image')
            ->where('product_image', '!=', '')
            ->where('created_at', '>=', Carbon::now()->subMonths(self::MONTHS_OLD))
            ->pluck('product_image');

        foreach ($shippings as $file) {
            $usedFiles[basename($file)] = true;
        }

        return $usedFiles;
    }
}
