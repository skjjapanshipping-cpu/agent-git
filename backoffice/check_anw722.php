<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$rows = \App\Models\Customershipping::where('excel_status','1')
    ->where('customerno','ANW-722')
    ->whereDate('etd','2026-02-16')
    ->select('id','customerno','delivery_type_id','thai_bill_status','thai_delivery_status','status')
    ->get();

foreach($rows as $r) {
    echo "id={$r->id} | delivery_type_id={$r->delivery_type_id} | thai_bill_status={$r->thai_bill_status} | thai_delivery_status={$r->thai_delivery_status} | status={$r->status}\n";
}

// Also check distinct delivery_type_id values and status values for this ETD
echo "\n--- Distinct delivery_type_id for ETD 2026-02-16 ---\n";
$types = \Illuminate\Support\Facades\DB::table('customershippings')
    ->where('excel_status',1)->whereDate('etd','2026-02-16')
    ->select('delivery_type_id', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
    ->groupBy('delivery_type_id')->get();
foreach($types as $t) echo "  delivery_type_id={$t->delivery_type_id} count={$t->cnt}\n";

echo "\n--- Distinct status for ETD 2026-02-16 ---\n";
$statuses = \Illuminate\Support\Facades\DB::table('customershippings')
    ->where('excel_status',1)->whereDate('etd','2026-02-16')
    ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
    ->groupBy('status')->get();
foreach($statuses as $s) echo "  status={$s->status} count={$s->cnt}\n";

echo "\n--- Distinct thai_delivery_status for ETD 2026-02-16 ---\n";
$tds = \Illuminate\Support\Facades\DB::table('customershippings')
    ->where('excel_status',1)->whereDate('etd','2026-02-16')
    ->select('thai_delivery_status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
    ->groupBy('thai_delivery_status')->get();
foreach($tds as $t) echo "  thai_delivery_status={$t->thai_delivery_status} count={$t->cnt}\n";
