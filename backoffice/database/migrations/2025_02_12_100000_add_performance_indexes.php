<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPerformanceIndexes extends Migration
{
    public function up()
    {
        // customershippings — ตารางหลักที่ query หนักสุด
        Schema::table('customershippings', function (Blueprint $table) {
            $table->index('customerno', 'idx_cs_customerno');
            $table->index('etd', 'idx_cs_etd');
            $table->index('excel_status', 'idx_cs_excel_status');
            $table->index('status', 'idx_cs_status');
            $table->index('track_no', 'idx_cs_track_no');
            $table->index('ship_date', 'idx_cs_ship_date');
            $table->index('pay_status', 'idx_cs_pay_status');
            $table->index('delivery_type_id', 'idx_cs_delivery_type_id');
            $table->index(['customerno', 'etd'], 'idx_cs_customerno_etd');
            $table->index(['excel_status', 'etd'], 'idx_cs_excel_etd');
        });

        // customerorders — ตารางสั่งซื้อ
        Schema::table('customerorders', function (Blueprint $table) {
            $table->index('customerno', 'idx_co_customerno');
            $table->index('order_date', 'idx_co_order_date');
            $table->index('status', 'idx_co_status');
            $table->index('shipping_status', 'idx_co_shipping_status');
            $table->index('tracking_number', 'idx_co_tracking_number');
            $table->index(['customerno', 'order_date'], 'idx_co_customerno_date');
            $table->index(['customerno', 'itemno'], 'idx_co_customerno_itemno');
        });

        // tracks — ตาราง tracking
        Schema::table('tracks', function (Blueprint $table) {
            $table->index('status', 'idx_tr_status');
            $table->index('track_no', 'idx_tr_track_no');
            $table->index('customer_name', 'idx_tr_customer_name');
            $table->index('source_date', 'idx_tr_source_date');
            $table->index('ship_date', 'idx_tr_ship_date');
            $table->index(['status', 'source_date'], 'idx_tr_status_source');
        });
    }

    public function down()
    {
        Schema::table('customershippings', function (Blueprint $table) {
            $table->dropIndex('idx_cs_customerno');
            $table->dropIndex('idx_cs_etd');
            $table->dropIndex('idx_cs_excel_status');
            $table->dropIndex('idx_cs_status');
            $table->dropIndex('idx_cs_track_no');
            $table->dropIndex('idx_cs_ship_date');
            $table->dropIndex('idx_cs_pay_status');
            $table->dropIndex('idx_cs_delivery_type_id');
            $table->dropIndex('idx_cs_customerno_etd');
            $table->dropIndex('idx_cs_excel_etd');
        });

        Schema::table('customerorders', function (Blueprint $table) {
            $table->dropIndex('idx_co_customerno');
            $table->dropIndex('idx_co_order_date');
            $table->dropIndex('idx_co_status');
            $table->dropIndex('idx_co_shipping_status');
            $table->dropIndex('idx_co_tracking_number');
            $table->dropIndex('idx_co_customerno_date');
            $table->dropIndex('idx_co_customerno_itemno');
        });

        Schema::table('tracks', function (Blueprint $table) {
            $table->dropIndex('idx_tr_status');
            $table->dropIndex('idx_tr_track_no');
            $table->dropIndex('idx_tr_customer_name');
            $table->dropIndex('idx_tr_source_date');
            $table->dropIndex('idx_tr_ship_date');
            $table->dropIndex('idx_tr_status_source');
        });
    }
}
