<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Only ETD 09/02/2026 should be pay_status=2 (the invoice that was paid)
// All ETDs AFTER 09/02/2026 should be reverted to pay_status=1

$reverted = App\Models\Customershipping::where('customerno', 'anw-539')
    ->where('excel_status', '1')
    ->where('pay_status', 2)
    ->where('etd', '>', '2026-02-09')
    ->update(['pay_status' => 1]);

echo "Reverted: $reverted records (ETD > 2026-02-09) back to pay_status=1\n";

// Verify
$check = App\Models\Customershipping::where('customerno', 'anw-539')
    ->where('excel_status', '1')
    ->where('etd', '>=', '2026-02-09')
    ->selectRaw('etd, pay_status, count(*) as cnt')
    ->groupBy('etd', 'pay_status')
    ->orderBy('etd', 'desc')
    ->get();

echo "\nCurrent status for recent ETDs:\n";
foreach ($check as $r) {
    $status = $r->pay_status == 2 ? 'PAID' : ($r->pay_status == 1 ? 'PENDING' : "status={$r->pay_status}");
    echo "  ETD={$r->etd}  {$status}  count={$r->cnt}\n";
}
