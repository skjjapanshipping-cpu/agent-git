<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomershippingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('customershippings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->date('ship_date');
            $table->string('customerno',255);
            $table->string('track_no',100);
            $table->string('cod',50)->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->decimal('unit_price', 10, 2)->nullable();
            $table->decimal('import_cost', 10, 2)->nullable();
            $table->string('box_image')->nullable();
            $table->string('product_image')->nullable();
            $table->string('box_no')->nullable();
            $table->string('warehouse')->nullable();
            $table->string('status')->nullable();
            $table->string('delivery_address')->nullable();
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
        Schema::dropIfExists('customershippings');
    }
}
