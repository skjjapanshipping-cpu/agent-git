<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddThaiBillColumnsToCustomershippings extends Migration
{
    public function up()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->tinyInteger('thai_bill_status')->default(0)->after('thai_delivery_status')->comment('0=ยังไม่ออกบิล,1=ออกบิลแล้ว/รอโอน,2=โอนแล้ว');
            $table->decimal('thai_bill_amount', 10, 2)->nullable()->after('thai_bill_status');
            $table->string('thai_bill_pdf', 500)->nullable()->after('thai_bill_amount');
            $table->timestamp('thai_billed_at')->nullable()->after('thai_bill_pdf');
        });
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropColumn(['thai_bill_status', 'thai_bill_amount', 'thai_bill_pdf', 'thai_billed_at']);
        });
    }
}
