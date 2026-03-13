<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShippopColumnsToCustomershippingsTable extends Migration
{
    public function up()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->string('shippop_purchase_id', 100)->nullable()->after('picked_up_by');
            $table->string('thai_tracking_no', 100)->nullable()->after('shippop_purchase_id');
            $table->string('thai_courier', 100)->nullable()->after('thai_tracking_no');
            $table->decimal('thai_shipping_price', 10, 2)->nullable()->after('thai_courier');
            $table->string('thai_delivery_status', 50)->nullable()->after('thai_shipping_price');
            $table->timestamp('shippop_booked_at')->nullable()->after('thai_delivery_status');
        });
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropColumn([
                'shippop_purchase_id',
                'thai_tracking_no',
                'thai_courier',
                'thai_shipping_price',
                'thai_delivery_status',
                'shippop_booked_at',
            ]);
        });
    }
}
