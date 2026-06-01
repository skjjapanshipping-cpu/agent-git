<?php

/*
|--------------------------------------------------------------------------
| White-label Brand Config (per-domain)
|--------------------------------------------------------------------------
| ใช้ทำ multi-brand / white-label: แต่ละโดเมนแสดงแบรนด์ของตัวเอง
| - tracking_root = true  → root '/' ของโดเมนนั้นแสดง "หน้าเช็คพัสดุ" ทันที
| - neutral       = true  → ไม่แสดง identity ของ SKJ (โลโก้/ลิงก์/ผู้ติดต่อ)
| resolve ด้วย \App\Support\Brand::current() (อิงจาก request host)
*/

return [

    // โดเมน → ค่าแบรนด์
    'domains' => [

        'japantracking.org' => [
            'name'          => 'Japan Tracking',
            'neutral'       => true,
            'tracking_root' => true,
            'logo'          => null,            // null = ใช้ wordmark (ไม่มีโลโก้ SKJ)
            'tagline'       => 'ติดตามสถานะพัสดุของคุณแบบเรียลไทม์',
            'show_login'    => false,
            'show_contact'  => false,
            'website'       => null,
            'footer'        => 'Japan Tracking',
        ],

    ],

    // ค่าเริ่มต้น (SKJ) — ใช้กับ skjjapanshipping.com/skjtrack ตามเดิม
    'default' => [
        'name'          => 'SKJ Japan Shipping',
        'neutral'       => false,
        'tracking_root' => false,
        'logo'          => 'img/skj-logo-full.png',
        'tagline'       => 'ติดตามสถานะการขนส่งสินค้าจากญี่ปุ่นมาไทยแบบเรียลไทม์',
        'show_login'    => true,
        'show_contact'  => true,
        'website'       => 'https://skjjapanshipping.com/',
        'footer'        => 'SKJ Japan Shipping Company',
    ],

];
