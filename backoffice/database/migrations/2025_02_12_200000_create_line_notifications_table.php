<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLineNotificationsTable extends Migration
{
    public function up()
    {
        Schema::create('line_notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('customerno', 50);
            $table->date('etd');
            $table->string('line_user_id', 100);
            $table->integer('item_count')->default(0);
            $table->enum('status', ['success', 'failed'])->default('success');
            $table->text('error_message')->nullable();
            $table->unsignedBigInteger('sent_by');
            $table->timestamps();

            $table->index(['customerno', 'etd']);
            $table->index('sent_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('line_notifications');
    }
}
