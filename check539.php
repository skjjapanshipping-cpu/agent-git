<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Check how many records ANW-539 has per ETD and pay_status
$records = App\Models\Customershipping::where('customerno','anw-539')
    ->where('excel_status','1')
    ->selectRaw('etd, pay_status, count(*) as cnt')
    ->groupBy('etd','pay_status')
    ->orderBy('etd','desc')
    ->get();

foreach ($records as $r) {
    echo "ETD={$r->etd} pay_status={$r->pay_status} count={$r->cnt}\n";
}
