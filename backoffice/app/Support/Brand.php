<?php

namespace App\Support;

class Brand
{
    /**
     * คืนค่าแบรนด์ของ request ปัจจุบัน (อิงจาก host)
     * fallback → config('brands.default')
     */
    public static function current(): array
    {
        $host = '';
        try {
            $host = strtolower((string) request()->getHost());
        } catch (\Throwable $e) {
            $host = '';
        }

        $domains = (array) config('brands.domains', []);
        $default = (array) config('brands.default', []);

        $brand = $domains[$host] ?? $default;
        $brand = array_merge($default, $brand); // เติม key ที่ขาดจาก default
        $brand['host'] = $host;

        return $brand;
    }

    /**
     * โดเมนนี้ให้ root '/' เป็นหน้าเช็คพัสดุหรือไม่
     */
    public static function isTrackingRoot(): bool
    {
        return (bool) (self::current()['tracking_root'] ?? false);
    }
}
