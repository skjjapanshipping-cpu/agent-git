<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class ImproveBoxNoIndexOnCustomershippings extends Migration
{
    /**
     * เปลี่ยนเป็น index 3 คอลัมน์ (box_no, excel_status, etd)
     * ให้ครอบทั้ง equality (box_no+excel_status) และ ORDER BY etd DESC LIMIT 1
     * เพื่อไม่ให้ optimizer หลุดไปเลือก index ที่เรียง etd แล้วสแกนทั้งตาราง
     */
    public function up()
    {
        $indexes = collect(DB::select("SHOW INDEX FROM customershippings"))->pluck('Key_name')->unique();

        if ($indexes->contains('idx_cs_box_excel')) {
            Schema::table('customershippings', function (Blueprint $table) {
                $table->dropIndex('idx_cs_box_excel');
            });
        }

        if (!$indexes->contains('idx_cs_box_excel_etd')) {
            Schema::table('customershippings', function (Blueprint $table) {
                $table->index(['box_no', 'excel_status', 'etd'], 'idx_cs_box_excel_etd');
            });
        }
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropIndex('idx_cs_box_excel_etd');
            $table->index(['box_no', 'excel_status'], 'idx_cs_box_excel');
        });
    }
}
