<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require_once '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Customershipping;

// Revert ETD 2026-01-26 records that were wrongly set to pay_status=2
// These should be pay_status=5 (invoice sent, not yet paid)
$updated = Customershipping::whereIn('id', [102863, 102864])
    ->where('pay_status', 2)
    ->update(['pay_status' => 5]);

echo "Reverted {$updated} records (ETD 2026-01-26) to pay_status=5\n";

// Verify
$rows = Customershipping::whereIn('id', [102863, 102864])->get(['id', 'etd', 'pay_status', 'import_cost']);
foreach ($rows as $r) {
    $etd = $r->etd ? $r->etd->format('Y-m-d') : 'null';
    echo "id={$r->id} ETD={$etd} pay_status={$r->pay_status} import={$r->import_cost}\n";
}

// Also verify ETD 2026-02-09 thai_bill_status is correct
$thaiRows = Customershipping::where('customerno', 'anw-830')
    ->where('excel_status', '1')
    ->whereNotNull('thai_bill_amount')
    ->where('thai_bill_amount', '>', 0)
    ->get(['id', 'etd', 'pay_status', 'thai_bill_status', 'thai_bill_amount']);

echo "\n=== Thai bill records ===\n";
foreach ($thaiRows as $r) {
    $etd = $r->etd ? $r->etd->format('Y-m-d') : 'null';
    echo "id={$r->id} ETD={$etd} pay={$r->pay_status} thaiBill={$r->thai_bill_status} thaiAmt={$r->thai_bill_amount}\n";
}
