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
}
