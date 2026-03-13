<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ShippopService
{
    protected $apiKey;
    protected $apiUrl;

    // ที่อยู่คลัง SKJ (ต้นทาง)
    protected $warehouse = [
        'name'     => 'SKJ JAPAN',
        'address'  => '36/1 หมู่ 3',
        'district' => 'บางกรวย',
        'state'    => 'มหาสวัสดิ์',
        'province' => 'นนทบุรี',
        'postcode' => '11130',
        'tel'      => '',
    ];

    public function __construct()
    {
        $this->apiKey = config('services.shippop.api_key');
        $this->apiUrl = rtrim(config('services.shippop.api_url'), '/');
    }

    /**
     * เช็คราคาค่าส่งจากขนส่งหลายเจ้า
     *
     * @param array $parcel  ['weight' => kg, 'width' => cm, 'length' => cm, 'height' => cm]
     * @param array $destination ['name', 'address', 'district', 'state', 'province', 'postcode', 'tel']
     * @return array
     */
    public function getPriceList(array $parcel, array $destination): array
    {
        if (empty($this->apiKey)) {
            return [
                'status' => false,
                'message' => 'Shippop API Key ยังไม่ได้ตั้งค่า กรุณาเพิ่ม SHIPPOP_API_KEY ใน .env',
                'data' => [],
            ];
        }

        try {
            $payload = [
                'api_key' => $this->apiKey,
                'data' => [
                    [
                        'from' => [
                            'name'     => $this->warehouse['name'],
                            'address'  => $this->warehouse['address'],
                            'district' => $this->warehouse['district'],
                            'state'    => $this->warehouse['state'],
                            'province' => $this->warehouse['province'],
                            'postcode' => $this->warehouse['postcode'],
                            'tel'      => $this->warehouse['tel'],
                        ],
                        'to' => [
                            'name'     => $destination['name'] ?? '',
                            'address'  => $destination['address'] ?? '',
                            'district' => $destination['district'] ?? '',
                            'state'    => $destination['state'] ?? '',
                            'province' => $destination['province'] ?? '',
                            'postcode' => $destination['postcode'] ?? '',
                            'tel'      => $destination['tel'] ?? '',
                        ],
                        'parcel' => [
                            'name'   => 'พัสดุ',
                            'weight' => (float) ($parcel['weight'] ?? 1),
                            'width'  => (float) ($parcel['width'] ?? 10),
                            'length' => (float) ($parcel['length'] ?? 10),
                            'height' => (float) ($parcel['height'] ?? 10),
                        ],
                    ],
                ],
            ];

            Log::info('Shippop pricelist request', ['postcode' => $destination['postcode'] ?? '']);

            $response = Http::timeout(15)->post($this->apiUrl . '/pricelist/', $payload);

            if ($response->successful()) {
                $body = $response->json();

                if ($body['status'] ?? false) {
                    $couriers = $this->parsePriceList($body);
                    return [
                        'status' => true,
                        'message' => 'สำเร็จ',
                        'data' => $couriers,
                    ];
                }

                return [
                    'status' => false,
                    'message' => $body['message'] ?? 'Shippop API ตอบกลับไม่สำเร็จ',
                    'data' => [],
                ];
            }

            Log::error('Shippop pricelist failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            return [
                'status' => false,
                'message' => 'ไม่สามารถเชื่อมต่อ Shippop ได้ (HTTP ' . $response->status() . ')',
                'data' => [],
            ];

        } catch (\Exception $e) {
            Log::error('Shippop pricelist exception', ['error' => $e->getMessage()]);
            return [
                'status' => false,
                'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                'data' => [],
            ];
        }
    }

    /**
     * Parse Shippop price list response into a clean array
     */
    protected function parsePriceList(array $body): array
    {
        $couriers = [];

        // Shippop returns data keyed by index, each containing courier_code => details
        $data = $body['data'] ?? [];

        foreach ($data as $item) {
            if (!is_array($item)) continue;

            foreach ($item as $courierCode => $info) {
                if (!is_array($info)) continue;

                // Skip unavailable couriers
                if (isset($info['available']) && !$info['available']) continue;

                $couriers[] = [
                    'courier_code' => $courierCode,
                    'name'         => $info['courier_name'] ?? $courierCode,
                    'price'        => (float) ($info['price'] ?? 0),
                    'estimate_time'=> $info['estimate_time'] ?? '-',
                    'logo'         => $info['courier_logo'] ?? '',
                    'available'    => $info['available'] ?? true,
                    'remark'       => $info['remark'] ?? '',
                ];
            }
        }

        // Sort by price ascending
        usort($couriers, fn($a, $b) => $a['price'] <=> $b['price']);

        return $couriers;
    }

    /**
     * จองขนส่ง (Booking Order)
     *
     * @param string $courierCode  รหัสขนส่ง (เช่น KEX, FLE, JNT, EMS)
     * @param array  $parcel       ['weight', 'width', 'length', 'height']
     * @param array  $destination  ['name', 'address', 'district', 'state', 'province', 'postcode', 'tel']
     * @param string $email        อีเมลผู้รับ (optional)
     * @return array
     */
    public function booking(string $courierCode, array $parcel, array $destination, string $email = ''): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'Shippop API Key ยังไม่ได้ตั้งค่า'];
        }

        try {
            $payload = [
                'api_key' => $this->apiKey,
                'email'   => $email,
                'data' => [
                    [
                        'from' => [
                            'name'     => $this->warehouse['name'],
                            'address'  => $this->warehouse['address'],
                            'district' => $this->warehouse['district'],
                            'state'    => $this->warehouse['state'],
                            'province' => $this->warehouse['province'],
                            'postcode' => $this->warehouse['postcode'],
                            'tel'      => $this->warehouse['tel'],
                        ],
                        'to' => [
                            'name'     => $destination['name'] ?? '',
                            'address'  => $destination['address'] ?? '',
                            'district' => $destination['district'] ?? '',
                            'state'    => $destination['state'] ?? '',
                            'province' => $destination['province'] ?? '',
                            'postcode' => $destination['postcode'] ?? '',
                            'tel'      => $destination['tel'] ?? '',
                        ],
                        'parcel' => [
                            'name'   => 'พัสดุ SKJ',
                            'weight' => (float) ($parcel['weight'] ?? 1),
                            'width'  => (float) ($parcel['width'] ?? 10),
                            'length' => (float) ($parcel['length'] ?? 10),
                            'height' => (float) ($parcel['height'] ?? 10),
                        ],
                        'courier_code' => $courierCode,
                    ],
                ],
            ];

            Log::info('Shippop booking request', ['courier' => $courierCode, 'to' => $destination['name'] ?? '']);

            $response = Http::timeout(30)->post($this->apiUrl . '/booking/', $payload);

            if ($response->successful()) {
                $body = $response->json();
                Log::info('Shippop booking response', ['body' => $body]);

                if ($body['status'] ?? false) {
                    // Extract purchase_id and tracking info from response
                    $orderData = $body['data'] ?? [];
                    $firstOrder = reset($orderData);

                    return [
                        'status'       => true,
                        'message'      => 'จองขนส่งสำเร็จ',
                        'purchase_id'  => $body['purchase_id'] ?? null,
                        'tracking_code'=> $firstOrder['tracking_code'] ?? null,
                        'courier_code' => $firstOrder['courier_code'] ?? $courierCode,
                        'courier_name' => $firstOrder['courier_name'] ?? $courierCode,
                        'price'        => (float) ($firstOrder['price'] ?? 0),
                        'data'         => $body,
                    ];
                }

                return [
                    'status'  => false,
                    'message' => $body['message'] ?? 'Shippop booking ไม่สำเร็จ',
                    'data'    => $body,
                ];
            }

            Log::error('Shippop booking failed', ['status' => $response->status(), 'body' => $response->body()]);
            return ['status' => false, 'message' => 'ไม่สามารถเชื่อมต่อ Shippop ได้ (HTTP ' . $response->status() . ')'];

        } catch (\Exception $e) {
            Log::error('Shippop booking exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    /**
     * ยืนยันการจอง (Confirm Order)
     *
     * @param string $purchaseId  รหัสจาก booking response
     * @return array
     */
    public function confirm(string $purchaseId): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'Shippop API Key ยังไม่ได้ตั้งค่า'];
        }

        try {
            $payload = [
                'api_key'     => $this->apiKey,
                'purchase_id' => $purchaseId,
            ];

            Log::info('Shippop confirm request', ['purchase_id' => $purchaseId]);

            $response = Http::timeout(30)->post($this->apiUrl . '/confirm/', $payload);

            if ($response->successful()) {
                $body = $response->json();
                Log::info('Shippop confirm response', ['body' => $body]);

                if ($body['status'] ?? false) {
                    return [
                        'status'  => true,
                        'message' => 'ยืนยันรายการสำเร็จ',
                        'data'    => $body,
                    ];
                }

                return [
                    'status'  => false,
                    'message' => $body['message'] ?? 'Shippop confirm ไม่สำเร็จ',
                    'data'    => $body,
                ];
            }

            return ['status' => false, 'message' => 'ไม่สามารถเชื่อมต่อ Shippop ได้ (HTTP ' . $response->status() . ')'];

        } catch (\Exception $e) {
            Log::error('Shippop confirm exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }

    /**
     * ตรวจสอบสถานะการจัดส่ง (Tracking)
     *
     * @param string $trackingCode  เลข tracking
     * @return array
     */
    public function tracking(string $trackingCode): array
    {
        if (empty($this->apiKey)) {
            return ['status' => false, 'message' => 'Shippop API Key ยังไม่ได้ตั้งค่า'];
        }

        try {
            $payload = [
                'api_key'       => $this->apiKey,
                'tracking_code' => $trackingCode,
            ];

            $response = Http::timeout(15)->post($this->apiUrl . '/tracking/', $payload);

            if ($response->successful()) {
                $body = $response->json();

                if ($body['status'] ?? false) {
                    return [
                        'status'          => true,
                        'message'         => 'สำเร็จ',
                        'tracking_status' => $body['data']['status'] ?? null,
                        'status_name'     => $body['data']['status_name'] ?? null,
                        'courier_tracking'=> $body['data']['courier_tracking_code'] ?? null,
                        'timeline'        => $body['data']['timeline'] ?? [],
                        'data'            => $body,
                    ];
                }

                return ['status' => false, 'message' => $body['message'] ?? 'ไม่พบข้อมูล tracking'];
            }

            return ['status' => false, 'message' => 'ไม่สามารถเชื่อมต่อ Shippop ได้'];

        } catch (\Exception $e) {
            Log::error('Shippop tracking exception', ['error' => $e->getMessage()]);
            return ['status' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()];
        }
    }
}
