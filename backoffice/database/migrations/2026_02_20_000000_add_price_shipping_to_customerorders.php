<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriceShippingToCustomerorders extends Migration
{
    public function up()
    {
        Schema::table('customerorders', function (Blueprint $table) {
            $table->decimal('product_price_yen', 12, 2)->nullable()->after('product_cost_yen');
            $table->decimal('shipping_jp_yen', 12, 2)->nullable()->after('product_price_yen');
        });
    }

    public function down()
    {
        Schema::table('customerorders', function (Blueprint $table) {
            $table->dropColumn(['product_price_yen', 'shipping_jp_yen']);
        });
    }
}
