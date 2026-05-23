<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LineMessagingService
{
    protected $channelAccessToken;
    protected $apiBaseUrl = 'https://api.line.me/v2/bot';

    public function __construct()
    {
        $this->channelAccessToken = config('services.line.channel_token');
    }

    /**
     * Send push message to a single LINE user
     */
    public function pushMessage(string $lineUserId, array $messages): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->channelAccessToken,
                'Content-Type'  => 'application/json',
            ])->post($this->apiBaseUrl . '/message/push', [
                'to'       => $lineUserId,
                'messages' => $messages,
            ]);

            if ($response->successful()) {
                Log::info('LINE push message sent', ['to' => $lineUserId]);
                return true;
            }

            Log::error('LINE push message failed', [
                'to'     => $lineUserId,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('LINE push message exception', [
                'to'    => $lineUserId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send multicast message to multiple LINE users (max 500)
     */
    public function multicastMessage(array $lineUserIds, array $messages): bool
    {
        if (empty($lineUserIds)) {
            return false;
        }

        // LINE API limit: max 500 users per multicast
        $chunks = array_chunk($lineUserIds, 500);
        $allSuccess = true;

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $this->channelAccessToken,
                    'Content-Type'  => 'application/json',
                ])->post($this->apiBaseUrl . '/message/multicast', [
                    'to'       => $chunk,
                    'messages' => $messages,
                ]);

                if (!$response->successful()) {
                    Log::error('LINE multicast failed', [
                        'status' => $response->status(),
                        'body'   => $response->body(),
                    ]);
                    $allSuccess = false;
                }
            } catch (\Exception $e) {
                Log::error('LINE multicast exception', ['error' => $e->getMessage()]);
                $allSuccess = false;
            }
        }

        return $allSuccess;
    }

    /**
     * Build Flex Message for shipping notification
     */
    public function buildShippingNotification(string $customerno, string $etdDate, int $itemCount, string $viewUrl, int $shippingMethod = 1): array
    {
        $isAir = $shippingMethod == 2;
        $headerIcon = $isAir ? '✈️' : '📦';
        $headerText = $isAir ? "{$headerIcon} แจ้งเตือนสินค้าเข้าระบบ (ทางอากาศ)" : "{$headerIcon} แจ้งเตือนสินค้าเข้าระบบ";
        $etdLabel = $isAir ? 'รอบเที่ยวบิน' : 'รอบปิดตู้';
        $headerColor = $isAir ? '#2563EB' : '#C9301D';
        $btnColor = $isAir ? '#2563EB' : '#C9301D';

        return [
            [
                'type' => 'flex',
                'altText' => "แจ้งเตือน: สินค้ารหัส {$customerno} เข้าระบบแล้ว",
                'contents' => [
                    'type' => 'bubble',
                    'size' => 'mega',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => $headerText,
                                'weight' => 'bold',
                                'size' => 'lg',
                                'color' => '#FFFFFF',
                            ],
                        ],
                        'backgroundColor' => $headerColor,
                        'paddingAll' => '20px',
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => 'สินค้าของคุณเข้าระบบเรียบร้อยแล้ว',
                                'size' => 'sm',
                                'color' => '#555555',
                                'wrap' => true,
                            ],
                            [
                                'type' => 'separator',
                                'margin' => 'lg',
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'vertical',
                                'margin' => 'lg',
                                'spacing' => 'sm',
                                'contents' => [
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'contents' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'รหัสลูกค้า',
                                                'size' => 'sm',
                                                'color' => '#AAAAAA',
                                                'flex' => 0,
                                            ],
                                            [
                                                'type' => 'text',
                                                'text' => $customerno,
                                                'size' => 'sm',
                                                'color' => '#333333',
                                                'weight' => 'bold',
                                                'align' => 'end',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'contents' => [
                                            [
                                                'type' => 'text',
                                                'text' => $etdLabel,
                                                'size' => 'sm',
                                                'color' => '#AAAAAA',
                                                'flex' => 0,
                                            ],
                                            [
                                                'type' => 'text',
                                                'text' => $etdDate,
                                                'size' => 'sm',
                                                'color' => '#333333',
                                                'weight' => 'bold',
                                                'align' => 'end',
                                            ],
                                        ],
                                    ],
                                    [
                                        'type' => 'box',
                                        'layout' => 'horizontal',
                                        'contents' => [
                                            [
                                                'type' => 'text',
                                                'text' => 'จำนวนรายการ',
                                                'size' => 'sm',
                                                'color' => '#AAAAAA',
                                                'flex' => 0,
                                            ],
                                            [
                                                'type' => 'text',
                                                'text' => $itemCount . ' ชิ้น',
                                                'size' => 'sm',
                                                'color' => '#C9301D',
                                                'weight' => 'bold',
                                                'align' => 'end',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'paddingAll' => '20px',
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => [
                                    'type' => 'uri',
                                    'label' => 'ดูรายการสินค้า',
                                    'uri' => $viewUrl,
                                ],
                                'style' => 'primary',
                                'color' => $btnColor,
                                'height' => 'md',
                            ],
                        ],
                        'paddingAll' => '20px',
                    ],
                ],
            ],
        ];
    }

    /**
     * Build Flex Message สำหรับบรอดแคสเฉพาะรอบปิดตู้
     * - ใช้แจ้งข่าวเช่น "สินค้าล่าช้ากว่ากำหนด" ฯลฯ
     *
     * @param string $customerno    รหัสลูกค้า
     * @param string $etdDate       วันที่ปิดตู้ (รูปแบบ d/m/Y)
     * @param string $title         หัวข้อสั้นๆ บน header
     * @param string $messageBody   เนื้อหาที่ admin พิมพ์
     * @param int    $shippingMethod 1 = เรือ, 2 = อากาศ
     * @param string $viewUrl       ลิงก์ดูสถานะ
     * @param string $headerColor   สีพื้น header (hex) — default ส้ม
     */
    public function buildBroadcastNotification(
        string $customerno,
        string $etdDate,
        string $title,
        string $messageBody,
        int $shippingMethod = 1,
        string $viewUrl = 'https://skjjapanshipping.com/skjtrack/shippingview',
        string $headerColor = '#F59E0B'
    ): array {
        $isAir = $shippingMethod == 2;
        $methodIcon = $isAir ? '✈️' : '🚢';
        $etdLabel = $isAir ? 'รอบเที่ยวบิน' : 'รอบปิดตู้';

        // ลด/จำกัดความยาว message body เพื่อไม่ให้เกิน LINE limit
        $bodyText = mb_substr(trim($messageBody), 0, 700);
        $altTitle = mb_substr($title, 0, 50);

        return [
            [
                'type' => 'flex',
                'altText' => "📢 {$altTitle} (รอบ {$etdDate})",
                'contents' => [
                    'type' => 'bubble',
                    'size' => 'mega',
                    'header' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => '📢 ' . $title,
                                'weight' => 'bold',
                                'size' => 'lg',
                                'color' => '#FFFFFF',
                                'wrap' => true,
                            ],
                            [
                                'type' => 'text',
                                'text' => "{$methodIcon} {$etdLabel} {$etdDate}",
                                'size' => 'sm',
                                'color' => '#FFFFFFCC',
                                'margin' => 'sm',
                            ],
                        ],
                        'backgroundColor' => $headerColor,
                        'paddingAll' => '20px',
                    ],
                    'body' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'text',
                                'text' => $bodyText,
                                'size' => 'md',
                                'color' => '#1F2937',
                                'wrap' => true,
                            ],
                            [
                                'type' => 'separator',
                                'margin' => 'xl',
                            ],
                            [
                                'type' => 'box',
                                'layout' => 'horizontal',
                                'margin' => 'lg',
                                'contents' => [
                                    [
                                        'type' => 'text',
                                        'text' => 'รหัสลูกค้า',
                                        'size' => 'xs',
                                        'color' => '#9CA3AF',
                                        'flex' => 0,
                                    ],
                                    [
                                        'type' => 'text',
                                        'text' => strtoupper($customerno),
                                        'size' => 'xs',
                                        'color' => '#0c5e8e',
                                        'weight' => 'bold',
                                        'align' => 'end',
                                    ],
                                ],
                            ],
                        ],
                        'paddingAll' => '20px',
                    ],
                    'footer' => [
                        'type' => 'box',
                        'layout' => 'vertical',
                        'contents' => [
                            [
                                'type' => 'button',
                                'action' => [
                                    'type' => 'uri',
                                    'label' => 'ตรวจสอบสถานะสินค้า',
                                    'uri' => $viewUrl,
                                ],
                                'style' => 'primary',
                                'color' => $headerColor,
                                'height' => 'md',
                            ],
                        ],
                        'paddingAll' => '20px',
                    ],
                ],
            ],
        ];
    }
}
