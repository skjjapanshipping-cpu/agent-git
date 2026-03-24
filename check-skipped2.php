<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';
$app = require '/var/www/vhosts/skjjapanshipping.com/backoffice/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$skipped = [
    'anw-645', 'anw-684', 'anw-776', 'anw-573', 'anw-954',
    'anw-961', 'anw-521', 'anw-900', 'anw-910', 'anw-508',
    'anw-583', 'anw-548', 'anw-923', 'anw-634', 'anw-510',
];

echo "=== ETD 09/02/2026 — pay_status breakdown ===\n\n";

foreach ($skipped as $cust) {
    $records = App\Models\Customershipping::where('customerno', $cust)
        ->where('etd', '2026-02-09')
        ->where('excel_status', '1')
        ->selectRaw('pay_status, count(*) as cnt')
        ->groupBy('pay_status')
        ->get();

    if ($records->isEmpty()) continue;

    $parts = [];
    foreach ($records as $r) {
        $ps = (int)$r->pay_status;
        if ($ps === 0) $label = 'ยังไม่ส่งบิล';
        elseif ($ps === 1) $label = 'รอชำระ';
        elseif ($ps === 2) $label = 'ชำระแล้ว';
        elseif ($ps === 5) $label = 'ส่งบิลแล้ว';
        else $label = "status={$ps}";
        $parts[] = "{$label}({$r->cnt})";
    }
    echo sprintf("%-12s  %s\n", strtoupper($cust), implode('  ', $parts));
}
