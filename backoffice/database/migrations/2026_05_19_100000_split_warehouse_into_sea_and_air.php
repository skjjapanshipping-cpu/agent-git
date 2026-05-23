<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SplitWarehouseIntoSeaAndAir extends Migration
{
    public function up()
    {
        $now = now();

        // 1) Rename existing warehouse_* (treat as ขนส่งทางเรือ) → warehouse_sea_*
        $renames = [
            'warehouse_name_jp'    => ['warehouse_sea_name_jp',    'ชื่อโกดัง 🚢 ทางเรือ (ญี่ปุ่น)',  'ชื่อโกดังขนส่งทางเรือ (ภาษาญี่ปุ่น)'],
            'warehouse_name_en'    => ['warehouse_sea_name_en',    'ชื่อโกดัง 🚢 ทางเรือ (อังกฤษ)',   'ชื่อโกดังขนส่งทางเรือ (ภาษาอังกฤษ)'],
            'warehouse_postcode'   => ['warehouse_sea_postcode',   'รหัสไปรษณีย์ 🚢',                'รหัสไปรษณีย์โกดังทางเรือในญี่ปุ่น'],
            'warehouse_address_jp' => ['warehouse_sea_address_jp', 'ที่อยู่ 🚢 (ญี่ปุ่น)',            'ที่อยู่โกดังทางเรือเต็มในภาษาญี่ปุ่น'],
            'warehouse_address_en' => ['warehouse_sea_address_en', 'ที่อยู่ 🚢 (อังกฤษ)',             'ที่อยู่โกดังทางเรือเต็มในภาษาอังกฤษ'],
            'warehouse_phone'      => ['warehouse_sea_phone',      'เบอร์โทรโกดัง 🚢',                'เบอร์โทรศัพท์โกดังทางเรือในญี่ปุ่น'],
        ];

        foreach ($renames as $oldKey => [$newKey, $label, $desc]) {
            // ถ้า key ใหม่ยังไม่มี → rename
            $newExists = DB::table('system_settings')->where('key', $newKey)->exists();
            if (!$newExists) {
                DB::table('system_settings')->where('key', $oldKey)->update([
                    'key'         => $newKey,
                    'label'       => $label,
                    'description' => $desc,
                    'updated_at'  => $now,
                ]);
            }
        }

        // 2) เพิ่ม warehouse_air_* (ใช้ค่า default placeholder)
        $airDefaults = [
            ['key' => 'warehouse_air_name_jp',    'value' => '株式会社 SKJ JAPAN SHIPPING (Air)',  'label' => 'ชื่อโกดัง ✈️ ทางเครื่องบิน (ญี่ปุ่น)',   'description' => 'ชื่อโกดังขนส่งทางเครื่องบิน (ภาษาญี่ปุ่น)'],
            ['key' => 'warehouse_air_name_en',    'value' => 'SKJ JAPAN SHIPPING CO., LTD. (Air)', 'label' => 'ชื่อโกดัง ✈️ ทางเครื่องบิน (อังกฤษ)',    'description' => 'ชื่อโกดังขนส่งทางเครื่องบิน (ภาษาอังกฤษ)'],
            ['key' => 'warehouse_air_postcode',   'value' => '〒000-0000',                          'label' => 'รหัสไปรษณีย์ ✈️',                       'description' => 'รหัสไปรษณีย์โกดังทางเครื่องบินในญี่ปุ่น'],
            ['key' => 'warehouse_air_address_jp', 'value' => '東京都 〇〇区 〇〇 0-00-0',           'label' => 'ที่อยู่ ✈️ (ญี่ปุ่น)',                   'description' => 'ที่อยู่โกดังทางเครื่องบินเต็มในภาษาญี่ปุ่น'],
            ['key' => 'warehouse_air_address_en', 'value' => '0-00-0 OO, OO-ku, Tokyo, JAPAN',     'label' => 'ที่อยู่ ✈️ (อังกฤษ)',                    'description' => 'ที่อยู่โกดังทางเครื่องบินเต็มในภาษาอังกฤษ'],
            ['key' => 'warehouse_air_phone',      'value' => '+81-00-0000-0000',                   'label' => 'เบอร์โทรโกดัง ✈️',                       'description' => 'เบอร์โทรศัพท์โกดังทางเครื่องบินในญี่ปุ่น'],
        ];

        foreach ($airDefaults as $row) {
            DB::table('system_settings')->updateOrInsert(
                ['key' => $row['key']],
                array_merge($row, ['created_at' => $now, 'updated_at' => $now])
            );
        }

        // เคลียร์ cache
        try { Cache::forget('system_settings_all'); } catch (\Throwable $e) {}
    }

    public function down()
    {
        // ย้อนกลับ: rename warehouse_sea_* กลับเป็น warehouse_* + ลบ warehouse_air_*
        $reverts = [
            'warehouse_sea_name_jp'    => 'warehouse_name_jp',
            'warehouse_sea_name_en'    => 'warehouse_name_en',
            'warehouse_sea_postcode'   => 'warehouse_postcode',
            'warehouse_sea_address_jp' => 'warehouse_address_jp',
            'warehouse_sea_address_en' => 'warehouse_address_en',
            'warehouse_sea_phone'      => 'warehouse_phone',
        ];

        foreach ($reverts as $seaKey => $oldKey) {
            DB::table('system_settings')->where('key', $seaKey)->update(['key' => $oldKey]);
        }

        DB::table('system_settings')->where('key', 'like', 'warehouse_air_%')->delete();

        try { Cache::forget('system_settings_all'); } catch (\Throwable $e) {}
    }
}
