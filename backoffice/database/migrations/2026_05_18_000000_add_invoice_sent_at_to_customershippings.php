<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceSentAtToCustomershippings extends Migration
{
    public function up()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            // timestamp ของบิลที่ส่งให้ลูกค้า — ทุกแถวในบิลเดียวกัน = ค่าเดียวกัน
            // ใช้แยก batch กรณีออกบิลแยกกัน 2 ครั้งในรอบเดียวกัน (split bill)
            $table->timestamp('invoice_sent_at')->nullable()->after('pay_status');
            $table->index(['customerno', 'etd', 'invoice_sent_at'], 'idx_cs_invoice_batch');
        });
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropIndex('idx_cs_invoice_batch');
            $table->dropColumn('invoice_sent_at');
        });
    }
}
