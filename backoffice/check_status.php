<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- Shipping Statuses table ---\n";
$statuses = \Illuminate\Support\Facades\DB::table('shipping_statuses')->get();
foreach($statuses as $s) echo "  id={$s->id} name={$s->name}\n";

echo "\n--- ANW-722 (ETD 2026-02-16) ---\n";
$rows = \App\Models\Customershipping::where('excel_status','1')
    ->where('customerno','ANW-722')->whereDate('etd','2026-02-16')
    ->select('id','status','delivery_type_id','thai_bill_status')->get();
foreach($rows as $r) echo "  id={$r->id} status={$r->status} delivery_type_id={$r->delivery_type_id} thai_bill_status={$r->thai_bill_status}\n";

echo "\n--- Status breakdown for ETD 2026-02-16 with delivery_type_id=1 ---\n";
$breakdown = \Illuminate\Support\Facades\DB::table('customershippings')
    ->where('excel_status',1)->whereDate('etd','2026-02-16')->where('delivery_type_id',1)
    ->select('status', \Illuminate\Support\Facades\DB::raw('COUNT(*) as cnt'))
    ->groupBy('status')->get();
foreach($breakdown as $b) echo "  status={$b->status} count={$b->cnt}\n";
