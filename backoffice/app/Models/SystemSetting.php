<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['key', 'value', 'label', 'description'];

    const CACHE_KEY = 'system_settings_all';
    const CACHE_TTL = 600; // 10 minutes

    public static function getValue(string $key, $default = null)
    {
        $all = self::all_settings();
        return $all[$key] ?? $default;
    }

    public static function setValue(string $key, ?string $value): void
    {
        self::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function all_settings(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return self::pluck('value', 'key')->toArray();
        });
    }

    /**
     * รวมทั้ง 2 โกดัง (เรือ + เครื่องบิน) ใช้แสดงผลฝั่งลูกค้า
     * ถ้าส่ง $customerno มา จะแทน placeholder "ANW-xxxx" ในที่อยู่ด้วยรหัสจริงให้เลย
     * (รองรับทั้ง "ANW-xxxx-Air" → "{customerno}-Air" เพราะ Air ต่อท้าย ANW-xxxx)
     */
    public static function warehouses(?string $customerno = null): array
    {
        $warehouses = [
            'sea' => self::warehouseSea(),
            'air' => self::warehouseAir(),
        ];

        if (empty($customerno)) {
            return $warehouses;
        }

        $code = strtoupper(trim($customerno));
        foreach (['sea', 'air'] as $type) {
            foreach (['address_jp', 'address_en'] as $field) {
                if (!empty($warehouses[$type][$field])) {
                    $warehouses[$type][$field] = self::replaceCustomerPlaceholder(
                        $warehouses[$type][$field],
                        $code
                    );
                }
            }
        }
        return $warehouses;
    }

    /**
     * แทนที่ placeholder "ANW-xxxx" (case-insensitive) ในที่อยู่ด้วยรหัสลูกค้าจริง
     * ใช้กับทั้ง JP และ EN
     */
    public static function replaceCustomerPlaceholder(string $text, string $customerno): string
    {
        $code = strtoupper(trim($customerno));
        // ใช้ regex แบบ case-insensitive รองรับ "ANW-xxxx", "anw-xxxx", "Anw-XXXX"
        return preg_replace('/ANW-xxxx/i', $code, $text);
    }

    /**
     * โกดังขนส่งทางเรือ 🚢
     */
    public static function warehouseSea(): array
    {
        $s = self::all_settings();
        return [
            'type'       => 'sea',
            'icon'       => '🚢',
            'label'      => 'ขนส่งทางเรือ',
            'name_jp'    => $s['warehouse_sea_name_jp']    ?? $s['warehouse_name_jp']    ?? null,
            'name_en'    => $s['warehouse_sea_name_en']    ?? $s['warehouse_name_en']    ?? null,
            'postcode'   => $s['warehouse_sea_postcode']   ?? $s['warehouse_postcode']   ?? null,
            'address_jp' => $s['warehouse_sea_address_jp'] ?? $s['warehouse_address_jp'] ?? null,
            'address_en' => $s['warehouse_sea_address_en'] ?? $s['warehouse_address_en'] ?? null,
            'phone'      => $s['warehouse_sea_phone']      ?? $s['warehouse_phone']      ?? null,
        ];
    }

    /**
     * โกดังขนส่งทางเครื่องบิน ✈️
     */
    public static function warehouseAir(): array
    {
        $s = self::all_settings();
        return [
            'type'       => 'air',
            'icon'       => '✈️',
            'label'      => 'ขนส่งทางเครื่องบิน',
            'name_jp'    => $s['warehouse_air_name_jp']    ?? null,
            'name_en'    => $s['warehouse_air_name_en']    ?? null,
            'postcode'   => $s['warehouse_air_postcode']   ?? null,
            'address_jp' => $s['warehouse_air_address_jp'] ?? null,
            'address_en' => $s['warehouse_air_address_en'] ?? null,
            'phone'      => $s['warehouse_air_phone']      ?? null,
        ];
    }

    /**
     * @deprecated  คงไว้เพื่อ backward compatibility — ใช้ warehouseSea() แทน
     */
    public static function warehouse(): array
    {
        $s = self::all_settings();
        $sea = self::warehouseSea();
        return [
            'name_jp'      => $sea['name_jp'],
            'name_en'      => $sea['name_en'],
            'postcode'     => $sea['postcode'],
            'address_jp'   => $sea['address_jp'],
            'address_en'   => $sea['address_en'],
            'phone'        => $sea['phone'],
            'contact_note' => $s['warehouse_contact_note'] ?? null,
        ];
    }

    /**
     * หมายเหตุการส่งพัสดุ (ใช้ร่วมกันทั้ง 2 โกดัง)
     */
    public static function contactNote(): ?string
    {
        $s = self::all_settings();
        return $s['warehouse_contact_note'] ?? null;
    }

    public static function support(): array
    {
        $s = self::all_settings();
        return [
            'line_url' => $s['support_line_url'] ?? null,
            'line_id'  => $s['support_line_id']  ?? null,
            'phone'    => $s['support_phone']    ?? null,
        ];
    }

    protected static function booted()
    {
        static::saved(fn () => Cache::forget(self::CACHE_KEY));
        static::deleted(fn () => Cache::forget(self::CACHE_KEY));
    }
}
