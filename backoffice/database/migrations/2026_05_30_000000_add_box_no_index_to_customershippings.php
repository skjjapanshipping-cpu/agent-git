<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddBoxNoIndexToCustomershippings extends Migration
{
    /**
     * เพิ่ม index ให้ box_no — เดิมไม่มี index ทำให้ระบบสแกน (รับเข้า/จ่ายของ)
     * ต้อง full/half-table scan ทุกครั้งที่ยิงบาร์โค้ด (~30k แถว) → ค้าง/ช้า
     */
    public function up()
    {
        $exists = collect(DB::select("SHOW INDEX FROM customershippings"))
            ->contains(function ($i) { return $i->Key_name === 'idx_cs_box_excel'; });

        if (!$exists) {
            Schema::table('customershippings', function (Blueprint $table) {
                $table->index(['box_no', 'excel_status'], 'idx_cs_box_excel');
            });
        }
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropIndex('idx_cs_box_excel');
        });
    }
}
