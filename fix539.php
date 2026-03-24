<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
$updated = App\Models\Customershipping::where('customerno','anw-539')->where('excel_status','1')->whereIn('pay_status',[1,5])->update(['pay_status'=>2]);
echo 'Updated: ' . $updated . PHP_EOL;
