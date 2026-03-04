<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerordersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('customerorders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('order_date');
            $table->string('customerno');
            $table->string('category');
            $table->string('image_link');
            $table->string('link');
            $table->integer('quantity');
            $table->decimal('product_cost_yen', 10, 2);
            $table->decimal('rateprice', 10, 2);
            $table->decimal('product_cost_baht', 10, 2);
            $table->string('status');
            $table->string('tracking_number')->nullable();
            $table->date('cutoff_date');
            $table->string('shipping_status');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('customerorders');
    }
}
