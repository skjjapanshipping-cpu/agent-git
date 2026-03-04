<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ลบรูปภาพเก่า > 6 เดือน ถาวร (500 ไฟล์/วัน เรียงจากเก่าสุด) ทุกวันเวลา 03:00 น.
        $schedule->command('images:cleanup --environment=server')
                 ->dailyAt('03:00')
                 ->timezone('Asia/Bangkok');

        // Sync ข้อมูลจาก Google Sheets เข้า tracking ทุก 2 ชม. (19:00-01:00)
        $schedule->command('sheets:sync')
                 ->cron('0 19,21,23,1 * * *')
                 ->timezone('Asia/Bangkok')
                 ->withoutOverlapping()
                 ->appendOutputTo(storage_path('logs/sheets-sync.log'));
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
