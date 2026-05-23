<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$rows = Customershipping::where('customerno','ANW-820')
    ->where('delivery_fullname','Sarunrat Chatchayapiwat')
    ->orderBy('etd')->orderBy('box_no')
    ->get(['id','customerno','box_no','track_no','etd','excel_status','status','delivery_type_id','shipping_method','pay_status','thai_bill_status','delivery_fullname','picked_up_at','ship_date']);

echo "Total rows for Sarunrat (any etd, any excel_status): " . $rows->count() . "\n";
echo str_repeat('-', 80) . "\n";
foreach ($rows as $r) {
    echo sprintf(
        "id=%d box=%s track=%s ship_date=%s etd=%s excel_status=%s status=%s deliv_type=%s ship_method=%s pay=%s thai=%s picked=%s\n",
        $r->id,
        $r->box_no,
        $r->track_no ?? '-',
        $r->ship_date ? $r->ship_date->format('Y-m-d') : '-',
        $r->etd ? $r->etd->format('Y-m-d') : '-',
        $r->excel_status,
        $r->status,
        $r->delivery_type_id,
        $r->shipping_method,
        $r->pay_status,
        $r->thai_bill_status,
        $r->picked_up_at ? '1' : '0'
    );
}

echo "\n=== with excel_status=1 only ===\n";
$rows2 = Customershipping::where('customerno','ANW-820')
    ->where('delivery_fullname','Sarunrat Chatchayapiwat')
    ->where('excel_status','1')
    ->orderBy('etd')->orderBy('box_no')
    ->get(['id','box_no','etd','excel_status']);
foreach ($rows2 as $r) {
    echo "  id=$r->id box=$r->box_no etd=" . ($r->etd ? $r->etd->format('Y-m-d') : '-') . "\n";
}

echo "\n=== filter etd 2026-03-30 only ===\n";
$rows3 = Customershipping::where('customerno','ANW-820')
    ->where('delivery_fullname','Sarunrat Chatchayapiwat')
    ->whereDate('etd', '2026-03-30')
    ->orderBy('box_no')
    ->get(['id','box_no','etd','excel_status']);
foreach ($rows3 as $r) {
    echo "  id=$r->id box=$r->box_no etd=" . ($r->etd ? $r->etd->format('Y-m-d') : '-') . " excel=$r->excel_status\n";
}
