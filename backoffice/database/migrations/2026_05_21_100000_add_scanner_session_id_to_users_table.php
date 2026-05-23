<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * เพิ่ม column scanner_session_id ใน users
 * ใช้บันทึก session_id ปัจจุบันของ Scanner ต่อ 1 บัญชี → 1 อุปกรณ์เท่านั้น
 * ถ้ามีอุปกรณ์ใหม่ login เข้ามา → ค่าจะถูกอัปเดตเป็น session ใหม่
 * → middleware ตรวจสอบ ถ้า session_id ปัจจุบันไม่ตรงกับใน DB → kick out
 */
class AddScannerSessionIdToUsersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('users', 'scanner_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('scanner_session_id', 100)->nullable()->after('remember_token');
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'scanner_session_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('scanner_session_id');
            });
        }
    }
}
