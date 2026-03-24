<?php
// Run from backoffice dir: php /tmp/check-anw830-server.php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require_once '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Customershipping;

$rows = Customershipping::where('customerno', 'anw-830')
    ->where('excel_status', '1')
    ->get(['id', 'etd', 'pay_status', 'import_cost', 'cod', 'thai_bill_status', 'thai_bill_amount']);

echo "=== ANW-830 records (" . $rows->count() . ") ===\n";
foreach ($rows as $r) {
    $etd = $r->etd ? $r->etd->format('Y-m-d') : 'null';
    echo "id={$r->id} ETD={$etd} pay={$r->pay_status} import={$r->import_cost} cod={$r->cod} thaiBill={$r->thai_bill_status} thaiAmt={$r->thai_bill_amount}\n";
}

// Group by ETD and show totals
$groups = $rows->groupBy(function($s) { return $s->etd ? $s->etd->format('Y-m-d') : 'null'; });
echo "\n=== ETD Group Totals ===\n";
foreach ($groups as $etd => $group) {
    $importTotal = $group->sum(function($s) {
        $codRate = $s->cod_rate ?? 0.25;
        return $s->import_cost + ($s->cod * $codRate);
    });
    $payStatuses = $group->pluck('pay_status')->unique()->implode(',');
    $thaiBillStatuses = $group->pluck('thai_bill_status')->unique()->implode(',');
    echo "ETD={$etd}: count={$group->count()} importTotal={$importTotal} payStatuses=[{$payStatuses}] thaiBillStatuses=[{$thaiBillStatuses}]\n";
}
