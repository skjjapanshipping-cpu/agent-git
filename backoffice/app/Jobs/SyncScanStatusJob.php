<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Customerorder;
use App\Models\Track;
use Illuminate\Support\Facades\Log;

/**
 * Sync สถานะหลังสแกน (รับเข้า/จ่ายของ) ไปยัง customerorder + tracks
 * ทำเบื้องหลังเพื่อให้ปุ่มยิงบาร์โค้ดตอบสนองทันที ไม่ต้องรอ sync
 *
 * ใช้ connection 'database' เฉพาะ job นี้ (default ของระบบยังเป็น 'sync')
 * เพื่อไม่ให้กระทบส่วนอื่นของแอป
 */
class SyncScanStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 60;

    protected $customerno;
    protected $itemno;
    protected $trackNo;
    protected $shippingStatus;
    protected $setDestinationDate;

    public function __construct($customerno, $itemno, $trackNo, $shippingStatus, $setDestinationDate)
    {
        // ตั้ง connection/queue ผ่าน property ที่สืบทอดจาก Queueable (ห้าม redeclare บน PHP 7.4)
        $this->connection = 'database';
        $this->queue = 'scan-sync';
        $this->customerno = $customerno;
        $this->itemno = $itemno;
        $this->trackNo = $trackNo;
        $this->shippingStatus = (int) $shippingStatus;
        $this->setDestinationDate = (bool) $setDestinationDate;
    }

    public function handle()
    {
        // 1) sync customerorder.shipping_status (index: customerno+itemno)
        try {
            Customerorder::where('customerno', $this->customerno)
                ->where('itemno', $this->itemno)
                ->update(['shipping_status' => $this->shippingStatus]);
        } catch (\Throwable $e) {
            Log::error('SyncScanStatusJob customerorder error: ' . $e->getMessage());
        }

        // 2) sync tracks.destination_date (เฉพาะตอนรับเข้า) — ใช้ index track_no
        if ($this->setDestinationDate && $this->trackNo) {
            try {
                $raw = (string) $this->trackNo;
                $noDash = str_replace('-', '', $raw);
                $variants = array_values(array_unique(array_filter([$raw, $noDash], 'strlen')));

                $affected = Track::where('status', 1)
                    ->whereIn('track_no', $variants)
                    ->whereNull('destination_date')
                    ->update(['destination_date' => now()->toDateString()]);

                if ($affected === 0) {
                    Track::where('status', 1)
                        ->whereRaw("REPLACE(track_no, '-', '') = ?", [$noDash])
                        ->whereNull('destination_date')
                        ->update(['destination_date' => now()->toDateString()]);
                }
            } catch (\Throwable $e) {
                Log::error('SyncScanStatusJob track error: ' . $e->getMessage());
            }
        }
    }
}
