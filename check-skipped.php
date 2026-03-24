<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Customers whose slips were skipped (validation error)
$skipped = [
    'anw-645', 'anw-684', 'anw-776', 'anw-573', 'anw-954',
    'anw-961', 'anw-521', 'anw-900', 'anw-910', 'anw-508',
    'anw-583', 'anw-548', 'anw-923', 'anw-634', 'anw-510',
    'anw-539', // group slip issue earlier
];

echo "=== Checking skipped customers — ETD 09/02/2026 pay_status ===\n\n";

foreach ($skipped as $cust) {
    $records = App\Models\Customershipping::where('customerno', $cust)
        ->where('etd', '2026-02-09')
        ->where('excel_status', '1')
        ->get();

    if ($records->count() == 0) continue;

    $pending = $records->where('pay_status', 1)->count();
    $paid = $records->where('pay_status', 2)->count();
    $other = $records->count() - $pending - $paid;

    $status = $pending > 0 ? "❌ PENDING ($pending)" : "✅ PAID ($paid)";
    echo sprintf("%-12s  %d items  paid=%d pending=%d  %s\n", strtoupper($cust), $records->count(), $paid, $pending, $status);
}
