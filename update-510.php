<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$updated = App\Models\Customershipping::where('customerno', 'anw-510')
    ->where('etd', '2026-02-09')
    ->where('excel_status', '1')
    ->where('pay_status', 5)
    ->update(['pay_status' => 2]);

echo "ANW-510: updated $updated records to pay_status=2\n";
