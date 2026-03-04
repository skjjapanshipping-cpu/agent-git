<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPickupColumnsToCustomershippingsTable extends Migration
{
    public function up()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->timestamp('picked_up_at')->nullable()->after('scanned_at');
            $table->string('picked_up_by', 100)->nullable()->after('picked_up_at');
        });
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropColumn(['picked_up_at', 'picked_up_by']);
        });
    }
}
