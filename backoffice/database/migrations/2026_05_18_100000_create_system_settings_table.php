<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSystemSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $now = now();
        $defaults = [
            [
                'key' => 'warehouse_name_jp',
                'value' => '株式会社 SKJ JAPAN SHIPPING',
                'label' => 'ชื่อโกดัง (ญี่ปุ่น)',
                'description' => 'ชื่อบริษัท/โกดังในญี่ปุ่น (ภาษาญี่ปุ่น)',
            ],
            [
                'key' => 'warehouse_name_en',
                'value' => 'SKJ JAPAN SHIPPING CO., LTD.',
                'label' => 'ชื่อโกดัง (อังกฤษ)',
                'description' => 'ชื่อบริษัท/โกดังในญี่ปุ่น (ภาษาอังกฤษ)',
            ],
            [
                'key' => 'warehouse_postcode',
                'value' => '〒000-0000',
                'label' => 'รหัสไปรษณีย์',
                'description' => 'รหัสไปรษณีย์ของโกดังในญี่ปุ่น',
            ],
            [
                'key' => 'warehouse_address_jp',
                'value' => '東京都 〇〇区 〇〇 0-00-0',
                'label' => 'ที่อยู่ (ญี่ปุ่น)',
                'description' => 'ที่อยู่โกดังเต็มในภาษาญี่ปุ่น (ใช้สำหรับเขียนบนพัสดุ)',
            ],
            [
                'key' => 'warehouse_address_en',
                'value' => '0-00-0 OO, OO-ku, Tokyo, JAPAN',
                'label' => 'ที่อยู่ (อังกฤษ)',
                'description' => 'ที่อยู่โกดังเต็มในภาษาอังกฤษ (สำรองสำหรับร้านค้าต่างประเทศ)',
            ],
            [
                'key' => 'warehouse_phone',
                'value' => '+81-00-0000-0000',
                'label' => 'เบอร์โทรโกดัง',
                'description' => 'เบอร์โทรศัพท์โกดังในญี่ปุ่น',
            ],
            [
                'key' => 'warehouse_contact_note',
                'value' => 'กรุณาเขียน "รหัสลูกค้า ANW-XXXX" บนกล่องพัสดุทุกครั้ง เพื่อให้โกดังจัดส่งให้คุณได้ถูกต้อง',
                'label' => 'หมายเหตุการส่งพัสดุ',
                'description' => 'ข้อความแนะนำลูกค้าก่อนส่งของเข้าโกดัง',
            ],
            [
                'key' => 'support_line_url',
                'value' => 'https://line.me/R/ti/p/@skjjapan',
                'label' => 'LINE ติดต่อแอดมิน',
                'description' => 'URL LINE Official Account สำหรับติดต่อขอเปิดบัญชี',
            ],
            [
                'key' => 'support_line_id',
                'value' => '@skjjapan',
                'label' => 'LINE ID',
                'description' => 'LINE ID สำหรับติดต่อแอดมิน (แสดงเป็นตัวอักษร)',
            ],
            [
                'key' => 'support_phone',
                'value' => '02-XXX-XXXX',
                'label' => 'เบอร์โทรติดต่อ',
                'description' => 'เบอร์โทรสำหรับติดต่อแอดมิน',
            ],
        ];

        foreach ($defaults as $row) {
            DB::table('system_settings')->insert(array_merge($row, [
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }
    }

    public function down()
    {
        Schema::dropIfExists('system_settings');
    }
}
