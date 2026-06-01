<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCusUnitPriceAirToUsers extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->decimal('cus_unit_price_air', 10, 2)->nullable()->after('cus_unit_price')
                ->comment('ราคาต่อหน่วยทางเครื่องบิน (บาท/กก.)');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('cus_unit_price_air');
        });
    }
}
