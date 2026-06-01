<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSequenceNoToExtraShippingCharges extends Migration
{
    public function up()
    {
        Schema::table('extra_shipping_charges', function (Blueprint $table) {
            $table->unsignedSmallInteger('sequence_no')->default(0)->after('description')
                ->comment('ลำดับใน batch (กัน dedup ซ้ำ — รายการเหมือนกันก็ save แยก)');
        });
    }

    public function down()
    {
        Schema::table('extra_shipping_charges', function (Blueprint $table) {
            $table->dropColumn('sequence_no');
        });
    }
}
