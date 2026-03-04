<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('purchase_requests', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_no')->unique()->comment('PR-YYYYMMDD-XXXX');
            $table->string('customerno')->comment('Customer number');
            $table->string('product_url')->comment('Mercari/Yahoo URL');
            $table->string('site')->nullable()->comment('mercari/yahoo/rakuten/other');
            $table->string('product_title')->nullable();
            $table->string('product_image')->nullable();
            $table->integer('quantity')->default(1);
            $table->decimal('estimated_price_yen', 12, 2)->default(0)->comment('Estimated price in JPY');
            $table->decimal('actual_price_yen', 12, 2)->nullable()->comment('Actual purchase price in JPY');
            $table->decimal('shipping_jp_yen', 12, 2)->default(0)->comment('Domestic JP shipping');
            $table->decimal('rate', 10, 4)->nullable()->comment('JPY-THB rate at purchase');
            $table->string('purchase_ref')->nullable()->comment('Order ID from Mercari/Yahoo');
            $table->tinyInteger('status')->default(0)->comment('0=pending,1=approved,2=purchasing,3=purchased,4=in_warehouse,5=shipped,6=cancelled');
            $table->unsignedBigInteger('admin_id')->nullable()->comment('Admin who processes');
            $table->unsignedBigInteger('boss_id')->nullable()->comment('Boss/buyer assignment');
            $table->text('customer_note')->nullable()->comment('Note from customer');
            $table->text('admin_note')->nullable()->comment('Note from admin');
            $table->unsignedBigInteger('customerorder_id')->nullable()->comment('Linked order after purchase');
            $table->timestamps();

            $table->index('customerno');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchase_requests');
    }
}
