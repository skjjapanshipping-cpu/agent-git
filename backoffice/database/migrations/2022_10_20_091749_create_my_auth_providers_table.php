<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMyAuthProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('my_auth_providers', function (Blueprint $table) {
            $table->bigIncrements('myauthproviderid');
           $table->BigInteger('userid')->nullable();
            $table->string('provider')->nullable()->default('NULL');
            $table->string('providerid')->nullable()->default('NULL');
            $table->timestamps();
          //$table->primary('myauthproviderid');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('my_auth_providers');
    }
}
