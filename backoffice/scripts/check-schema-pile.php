<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Schema;

$cols = Schema::getColumnListing('customershippings');
foreach ($cols as $c) {
    if (stripos($c, 'pile') !== false || stripos($c, 'group') !== false || stripos($c, 'delivery') !== false) {
        echo "* $c\n";
    }
}
echo "\n=== sample rows for ANW-820 30/03/2026 ===\n";
$r = \DB::table('customershippings')
    ->where('customerno', 'ANW-820')
    ->where('excel_status', '1')
    ->whereDate('etd', '2026-03-30')
    ->limit(3)
    ->get();
foreach ($r as $row) {
    echo "id=$row->id box=$row->box_no name='".$row->delivery_fullname."'\n";
    foreach ((array) $row as $k => $v) {
        if (stripos($k, 'pile') !== false || stripos($k, 'group') !== false || stripos($k, 'order') !== false) {
            echo "  $k = '$v'\n";
        }
    }
}
