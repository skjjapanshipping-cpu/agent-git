<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$customer = $argv[1] ?? 'ANW-646';
$etdLike = $argv[2] ?? '2026-03-23%';

$rows = Customershipping::where('customerno', $customer)
    ->where('etd', 'like', $etdLike)
    ->orderBy('id')
    ->get();

echo "Rows for {$customer} etd LIKE {$etdLike}:\n";
echo str_repeat('-', 80)."\n";
foreach ($rows as $r) {
    $arr = $r->toArray();
    echo "id={$r->id} | box={$r->box_no} | track={$r->track_no} | pay_status={$r->pay_status} | excel={$r->excel_status} | updated={$r->updated_at}\n";
    if ($argv[0] == 'dump') {
        print_r($arr);
    }
}
echo "\nColumns available: " . implode(',', array_keys($rows->first() ? $rows->first()->toArray() : [])) . "\n";
echo "Total: ".$rows->count()."\n";

// Also check unique pay_status values
$byStatus = $rows->groupBy('pay_status')->map->count();
echo "\nBy pay_status:\n";
foreach ($byStatus as $k => $v) {
    echo "  pay_status={$k}: {$v}\n";
}
