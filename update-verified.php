<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$customers = ['anw-776', 'anw-583', 'anw-548'];

foreach ($customers as $cust) {
    $updated = App\Models\Customershipping::where('customerno', $cust)
        ->where('etd', '2026-02-09')
        ->where('excel_status', '1')
        ->where('pay_status', 5)
        ->update(['pay_status' => 2]);
    echo strtoupper($cust) . ": updated $updated records to pay_status=2\n";
}
