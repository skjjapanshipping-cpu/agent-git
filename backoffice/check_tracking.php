<?php
// Quick DB check script
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$etd = '2026-02-16';
$total = \App\Models\Customershipping::where('excel_status','1')->whereDate('etd',$etd)->count();
$withTracking = \App\Models\Customershipping::where('excel_status','1')->whereDate('etd',$etd)->whereNotNull('thai_tracking_no')->where('thai_tracking_no','!=','')->count();
$withBill = \App\Models\Customershipping::where('excel_status','1')->whereDate('etd',$etd)->where('thai_bill_status','>',0)->count();

echo "ETD: $etd\n";
echo "Total items: $total\n";
echo "With thai_tracking_no: $withTracking\n";
echo "With thai_bill (status>0): $withBill\n\n";

// Sample some that have bill but no tracking
$samples = \App\Models\Customershipping::where('excel_status','1')
    ->whereDate('etd',$etd)
    ->where('thai_bill_status','>',0)
    ->select('customerno','thai_tracking_no','thai_courier','thai_bill_status','thai_bill_amount','shippop_purchase_id')
    ->limit(5)->get();
echo "Samples with bill:\n";
foreach($samples as $s) {
    echo "  {$s->customerno} | tracking={$s->thai_tracking_no} | courier={$s->thai_courier} | bill_status={$s->thai_bill_status} | bill_amt={$s->thai_bill_amount} | shippop={$s->shippop_purchase_id}\n";
}

// Check which field actually indicates "shipped in thai"
$withShippop = \App\Models\Customershipping::where('excel_status','1')->whereDate('etd',$etd)->whereNotNull('shippop_purchase_id')->where('shippop_purchase_id','!=','')->count();
echo "\nWith shippop_purchase_id: $withShippop\n";
