<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Find customers with ETD 09/02/2026, invoice sent (pay_status=1), not yet paid
$records = App\Models\Customershipping::where('etd', '2026-02-09')
    ->where('excel_status', '1')
    ->where('pay_status', 1)
    ->selectRaw('customerno, count(*) as cnt')
    ->groupBy('customerno')
    ->orderBy('customerno')
    ->get();

echo "=== ETD 09/02/2026 - pay_status=1 (pending) ===\n";
echo "Total customers: " . $records->count() . "\n\n";
foreach ($records as $r) {
    echo sprintf("%-12s  %2d items\n", $r->customerno, $r->cnt);
}
