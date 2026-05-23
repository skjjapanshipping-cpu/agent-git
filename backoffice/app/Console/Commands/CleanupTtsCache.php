<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class CleanupTtsCache extends Command
{
    protected $signature = 'tts:cleanup
                            {--days=30 : ลบไฟล์ที่ไม่ได้ใช้นานกว่า N วัน (อิงเวลาแก้ไขล่าสุด)}
                            {--dry-run : แสดงไฟล์ที่จะลบโดยไม่ลบจริง}';

    protected $description = 'ลบไฟล์ TTS cache (.mp3) ที่ไม่ได้ใช้นานกว่า N วัน';

    public function handle()
    {
        $days = (int) $this->option('days');
        if ($days < 1) {
            $days = 30;
        }
        $isDryRun = (bool) $this->option('dry-run');

        $dir = storage_path('app/tts-cache');

        if (!is_dir($dir)) {
            $this->warn("ไม่มี cache directory: {$dir}");
            return 0;
        }

        $cutoffTs = Carbon::now()->subDays($days)->getTimestamp();
        $cutoffDate = date('Y-m-d H:i:s', $cutoffTs);

        $this->info("=== ลบ TTS cache เก่ากว่า {$days} วัน (mtime < {$cutoffDate}) ===");
        if ($isDryRun) {
            $this->warn('โหมด Dry Run - จะไม่ลบจริง');
        }

        $deleted = 0;
        $deletedBytes = 0;
        $kept = 0;
        $keptBytes = 0;
        $errors = 0;

        $files = glob($dir . DIRECTORY_SEPARATOR . '*.mp3');
        if ($files === false) {
            $this->error('อ่าน directory ไม่ได้');
            return 1;
        }

        foreach ($files as $file) {
            $mtime = @filemtime($file);
            $size  = @filesize($file) ?: 0;
            if ($mtime === false) {
                $errors++;
                continue;
            }

            if ($mtime < $cutoffTs) {
                if ($isDryRun) {
                    $deleted++;
                    $deletedBytes += $size;
                } else {
                    if (@unlink($file)) {
                        $deleted++;
                        $deletedBytes += $size;
                    } else {
                        $errors++;
                    }
                }
            } else {
                $kept++;
                $keptBytes += $size;
            }
        }

        $fmt = function ($bytes) {
            if ($bytes < 1024) return $bytes . ' B';
            if ($bytes < 1024 * 1024) return round($bytes / 1024, 1) . ' KB';
            return round($bytes / 1024 / 1024, 2) . ' MB';
        };

        $this->info("ลบแล้ว: {$deleted} ไฟล์ ({$fmt($deletedBytes)})");
        $this->info("คงเหลือ: {$kept} ไฟล์ ({$fmt($keptBytes)})");
        if ($errors > 0) {
            $this->warn("Errors: {$errors}");
        }

        Log::info('[tts:cleanup]', [
            'days'    => $days,
            'dry_run' => $isDryRun,
            'deleted' => $deleted,
            'deleted_bytes' => $deletedBytes,
            'kept'    => $kept,
            'kept_bytes'    => $keptBytes,
            'errors'  => $errors,
        ]);

        return 0;
    }
}
