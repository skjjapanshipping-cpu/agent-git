<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddScannedAtToCustomershippingsTable extends Migration
{
    public function up()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->timestamp('scanned_at')->nullable()->after('box_no');
        });
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropColumn('scanned_at');
        });
    }
}
