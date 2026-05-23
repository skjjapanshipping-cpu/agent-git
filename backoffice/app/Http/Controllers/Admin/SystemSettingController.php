<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SystemSettingController extends Controller
{
    public function warehouse()
    {
        $seaKeys = [
            'warehouse_sea_name_jp', 'warehouse_sea_name_en',
            'warehouse_sea_postcode', 'warehouse_sea_address_jp', 'warehouse_sea_address_en',
            'warehouse_sea_phone',
        ];
        $airKeys = [
            'warehouse_air_name_jp', 'warehouse_air_name_en',
            'warehouse_air_postcode', 'warehouse_air_address_jp', 'warehouse_air_address_en',
            'warehouse_air_phone',
        ];
        $sharedKeys = [
            'warehouse_contact_note',
            'support_line_url', 'support_line_id', 'support_phone',
        ];

        $allKeys = array_merge($seaKeys, $airKeys, $sharedKeys);

        // ดึงเรียงตาม key เพื่อใช้แบบ keyed array
        $rows = SystemSetting::whereIn('key', $allKeys)->orderBy('id')->get()->keyBy('key');

        $seaSettings    = collect($seaKeys)->map(fn($k) => $rows->get($k))->filter()->values();
        $airSettings    = collect($airKeys)->map(fn($k) => $rows->get($k))->filter()->values();
        $sharedSettings = collect($sharedKeys)->map(fn($k) => $rows->get($k))->filter()->values();

        return view('admin.settings.warehouse', compact('seaSettings', 'airSettings', 'sharedSettings'));
    }

    public function updateWarehouse(Request $request)
    {
        $request->validate([
            'settings'   => 'required|array',
            'settings.*' => 'nullable|string|max:1000',
        ]);

        $allowedKeys = [
            // ขนส่งทางเรือ
            'warehouse_sea_name_jp', 'warehouse_sea_name_en',
            'warehouse_sea_postcode', 'warehouse_sea_address_jp', 'warehouse_sea_address_en',
            'warehouse_sea_phone',
            // ขนส่งทางเครื่องบิน
            'warehouse_air_name_jp', 'warehouse_air_name_en',
            'warehouse_air_postcode', 'warehouse_air_address_jp', 'warehouse_air_address_en',
            'warehouse_air_phone',
            // ใช้ร่วมกัน
            'warehouse_contact_note',
            'support_line_url', 'support_line_id', 'support_phone',
        ];

        foreach ($request->input('settings', []) as $key => $value) {
            if (!in_array($key, $allowedKeys, true)) {
                continue;
            }
            SystemSetting::setValue($key, $value);
        }

        Cache::forget(SystemSetting::CACHE_KEY);

        return redirect()->route('admin.settings.warehouse')
            ->with('success', 'บันทึกข้อมูลที่อยู่โกดังเรียบร้อย');
    }
}
