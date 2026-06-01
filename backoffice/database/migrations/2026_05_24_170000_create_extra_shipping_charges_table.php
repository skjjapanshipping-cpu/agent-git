<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtraShippingChargesTable extends Migration
{
    public function up()
    {
        Schema::create('extra_shipping_charges', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customerno', 50)->index();
            $table->date('etd_date')->nullable()->index();
            $table->string('ref_no', 100)->nullable();
            $table->string('courier', 100)->nullable();
            $table->string('recipient_name', 255)->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('description', 255)->nullable()->comment('เช่น Repack, ค่าบริการเพิ่ม');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->index(['customerno', 'etd_date'], 'idx_cust_etd');
        });
    }

    public function down()
    {
        Schema::dropIfExists('extra_shipping_charges');
    }
}
