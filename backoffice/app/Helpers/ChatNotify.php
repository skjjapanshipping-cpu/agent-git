<?php

namespace App\Helpers;

use App\Models\Customershipping;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatNotify
{
    const CHAT_URL = 'https://chat.skjjapanshipping.com/api/webhooks/tracking';
    const API_KEY = 'skjchat-tracking-2026';

    /**
     * แจ้งเตือนลูกค้าเมื่อสถานะพัสดุเปลี่ยน
     * 
     * @param string $customerno เลขลูกค้า เช่น ANW-500
     * @param int $status สถานะ: 4=สำเร็จ
     * @param array|string|null $trackNo เลขพัสดุ (string หรือ array)
     * @param string|null $etd รอบปิดตู้
     * @param int|null $itemCount จำนวนชิ้น
     */
    public static function notifyTrackingStatus($customerno, $status, $trackNo = null, $etd = null, $itemCount = null)
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders(['X-API-Key' => self::API_KEY])
                ->post(self::CHAT_URL, [
                    'customerno' => $customerno,
                    'status' => $status,
                    'track_no' => $trackNo,
                    'etd' => $etd,
                    'item_count' => $itemCount,
                ]);

            $data = $response->json();
            
            if ($response->successful() && ($data['success'] ?? false)) {
                Log::info("[ChatNotify] ✅ Notified {$customerno} status={$status}");
                return true;
            } else {
                Log::warning("[ChatNotify] ⚠️ {$customerno}: " . ($data['message'] ?? $data['error'] ?? 'Unknown'));
                return false;
            }
        } catch (\Exception $e) {
            Log::error("[ChatNotify] ❌ {$customerno}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * แจ้งเตือนลูกค้าหลายคนพร้อมกัน (batch)
     * เช็ค chat_notified_at ก่อน — ไม่แจ้งซ้ำ
     */
    public static function notifyBatch($shippings, $status)
    {
        $grouped = collect($shippings)->groupBy('customerno');

        foreach ($grouped as $customerno => $items) {
            // เช็คว่าแจ้งไปแล้วหรือยัง (ถ้ามี item ใดที่แจ้งแล้ว = ข้ามทั้ง group)
            $alreadyNotified = $items->whereNotNull('chat_notified_at')->count();
            if ($alreadyNotified > 0) {
                Log::info("[ChatNotify] ⏭️ Skip {$customerno} — already notified");
                continue;
            }

            $first = $items->first();
            $etd = $first->etd ? $first->etd->format('d/m/Y') : null;
            $trackNos = $items->pluck('track_no')->filter()->unique()->values()->toArray();

            $success = self::notifyTrackingStatus($customerno, $status, $trackNos, $etd, $items->count());

            // บันทึก chat_notified_at ถ้าแจ้งสำเร็จ
            if ($success) {
                $ids = $items->pluck('id')->toArray();
                Customershipping::whereIn('id', $ids)->update(['chat_notified_at' => now()]);
            }
        }
    }

    /**
     * แจ้งเตือนเมื่อ Pickup Scan จ่ายครบทุกชิ้นของลูกค้าในรอบนั้น
     * เรียกจาก QrScanController::pickupScan หลังสแกนจ่ายของ
     * 
     * @param Customershipping $parcel พัสดุที่เพิ่ง scan
     * @param array|null $etdDates รอบ ETD ที่เลือก
     */
    public static function notifyIfPickupComplete($parcel, $etdDates = null)
    {
        try {
            // ถ้าแจ้งไปแล้ว ข้าม
            if ($parcel->chat_notified_at) {
                return;
            }

            // นับจำนวนพัสดุทั้งหมดของลูกค้าในรอบ ETD นี้
            $query = Customershipping::where('excel_status', '1')
                ->where('customerno', $parcel->customerno)
                ->whereNotNull('box_no')->where('box_no', '!=', '');

            if ($etdDates && count($etdDates) > 0) {
                $query->where(function ($q) use ($etdDates) {
                    foreach ($etdDates as $d) {
                        $q->orWhereDate('etd', $d);
                    }
                });
            } else {
                $query->whereDate('etd', $parcel->etd);
            }

            $allParcels = $query->get();
            $total = $allParcels->count();
            $pickedUp = $allParcels->whereNotNull('picked_up_at')->count();

            // ยังจ่ายไม่ครบ → ไม่แจ้ง
            if ($pickedUp < $total) {
                return;
            }

            // เช็คว่าแจ้งไปแล้วหรือยัง
            $alreadyNotified = $allParcels->whereNotNull('chat_notified_at')->count();
            if ($alreadyNotified > 0) {
                return;
            }

            // จ่ายครบ → แจ้งลูกค้า
            $etd = $parcel->etd ? $parcel->etd->format('d/m/Y') : null;
            $trackNos = $allParcels->pluck('track_no')->filter()->unique()->values()->toArray();

            $success = self::notifyTrackingStatus($parcel->customerno, 4, $trackNos, $etd, $total);

            if ($success) {
                $ids = $allParcels->pluck('id')->toArray();
                Customershipping::whereIn('id', $ids)->update(['chat_notified_at' => now()]);
                Log::info("[ChatNotify] 📦 Pickup complete: {$parcel->customerno} ({$total} items)");
            }
        } catch (\Exception $e) {
            Log::error("[ChatNotify] Pickup notify error: " . $e->getMessage());
        }
    }
}
