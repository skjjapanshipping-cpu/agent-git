<?php

namespace App\Services;

use KS\PromptPay;
use Illuminate\Support\Facades\Log;

class PromptPayQrService
{
    // PromptPay ID (เลขบัตรประชาชน)
    const PROMPTPAY_ID = '1102001570110';

    /**
     * สร้าง PromptPay QR Code แบบ dynamic พร้อมยอดเงิน
     * บันทึกเป็นไฟล์ PNG แล้วคืน URL
     *
     * @param float $amount ยอดเงินที่ต้องชำระ
     * @param string $prefix ชื่อ prefix ไฟล์ (เช่น 'invoice', 'thai-bill')
     * @return string URL ของ QR image
     */
    public static function generateQrUrl(float $amount, string $prefix = 'qr'): string
    {
        // Fallback: ถ้ายอดเงิน 0 หรือติดลบ ใช้ QR แบบไม่มียอด
        if ($amount <= 0) {
            return 'https://chat.skjjapanshipping.com/uploads/qr-payment.jpg';
        }

        try {
            // สร้าง PromptPay QR payload (ฝังยอดเงิน)
            $pp = new PromptPay();
            $payload = $pp->generatePayload(self::PROMPTPAY_ID, $amount);

            // สร้าง QR image ด้วย BaconQrCode v1
            $renderer = new \BaconQrCode\Renderer\Image\Png();
            $renderer->setHeight(300);
            $renderer->setWidth(300);
            $writer = new \BaconQrCode\Writer($renderer);

            // สร้างชื่อไฟล์ unique
            $filename = $prefix . '_' . time() . '_' . uniqid() . '.png';
            $webRoot = '/var/www/vhosts/skjjapanshipping.com/httpdocs/skjtrack/promptpay-qr';

            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!is_dir($webRoot)) {
                mkdir($webRoot, 0755, true);
            }

            $filePath = $webRoot . '/' . $filename;
            $writer->writeFile($payload, $filePath);

            $url = 'https://skjjapanshipping.com/skjtrack/promptpay-qr/' . $filename;

            Log::info('[PromptPayQR] Generated', [
                'amount' => $amount,
                'file' => $filename,
            ]);

            return $url;

        } catch (\Exception $e) {
            Log::error('[PromptPayQR] Error: ' . $e->getMessage());
            // Fallback กลับไปใช้ QR เดิม
            return 'https://chat.skjjapanshipping.com/uploads/qr-payment.jpg';
        }
    }
}
