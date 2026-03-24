<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Customershipping;

// Check ANW-830 import cost records from old ETD 2026-01-26 that might have been wrongly updated
$rows = Customershipping::where('customerno', 'anw-830')
    ->where('excel_status', '1')
    ->get(['id', 'etd', 'pay_status', 'import_cost', 'cod', 'thai_bill_status', 'thai_bill_amount']);

echo "=== ANW-830 all records ===\n";
foreach ($rows as $r) {
    $etd = $r->etd ? $r->etd->format('Y-m-d') : 'null';
    echo "id={$r->id} ETD={$etd} pay={$r->pay_status} import={$r->import_cost} cod={$r->cod} thaiBill={$r->thai_bill_status} thaiAmt={$r->thai_bill_amount}\n";
}
