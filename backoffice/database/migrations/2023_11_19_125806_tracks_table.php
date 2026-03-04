<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TracksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('tracks', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customer_name',255);
            $table->string('track_no',100);
            $table->string('cod',50)->nullable();
            $table->double('weight',6,2);
            $table->date('source_date');
            $table->date('ship_date')->nullable();
            $table->date('destination_date')->nullable();
            $table->string('note')->nullable();
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
        //
    }
}
