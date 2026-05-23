<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$rows = Customershipping::where('customerno','ANW-820')
    ->where('excel_status','1')
    ->whereNotNull('box_no')->where('box_no','!=','')
    ->whereDate('etd','2026-03-30')
    ->get(['id','box_no','delivery_fullname','picked_up_at']);

echo 'Total parcels: '.$rows->count().PHP_EOL;

$grouped = $rows->groupBy('delivery_fullname');
echo 'Unique recipients (raw key): '.$grouped->count().PHP_EOL;

// Try normalized grouping (trim + collapse whitespace)
$normalized = $rows->groupBy(function ($r) {
    $n = trim((string) $r->delivery_fullname);
    $n = preg_replace('/\s+/u', ' ', $n);
    return $n;
});
echo 'Unique recipients (normalized): '.$normalized->count().PHP_EOL.PHP_EOL;

echo '=== Raw groups ==='.PHP_EOL;
$i = 0;
foreach ($grouped as $name => $items) {
    $i++;
    $picked = $items->whereNotNull('picked_up_at')->count();
    $bytes = strlen((string) $name);
    echo sprintf('  %2d. %s (raw len=%d) → %d boxes (%d picked)'.PHP_EOL,
        $i, json_encode((string) $name, JSON_UNESCAPED_UNICODE), $bytes, $items->count(), $picked);
}

echo PHP_EOL.'=== Names that differ between raw vs normalized ==='.PHP_EOL;
$rawKeys = $grouped->keys()->all();
$normKeys = $normalized->keys()->all();
$normMap = [];
foreach ($rawKeys as $k) {
    $n = trim((string) $k);
    $n = preg_replace('/\s+/u', ' ', $n);
    if ((string) $k !== $n) {
        echo "  RAW=".json_encode((string) $k, JSON_UNESCAPED_UNICODE).PHP_EOL;
        echo "  NORM=".json_encode($n, JSON_UNESCAPED_UNICODE).PHP_EOL;
        echo "  ----".PHP_EOL;
    }
}

// Find duplicates (multiple raw keys mapping to same normalized)
echo PHP_EOL.'=== Duplicates after normalization ==='.PHP_EOL;
$buckets = [];
foreach ($rawKeys as $k) {
    $n = trim((string) $k);
    $n = preg_replace('/\s+/u', ' ', $n);
    if (!isset($buckets[$n])) $buckets[$n] = [];
    $buckets[$n][] = $k;
}
foreach ($buckets as $n => $rawList) {
    if (count($rawList) > 1) {
        echo "  Normalized=".json_encode($n, JSON_UNESCAPED_UNICODE)." merges:".PHP_EOL;
        foreach ($rawList as $r) {
            echo "    - ".json_encode((string) $r, JSON_UNESCAPED_UNICODE).PHP_EOL;
        }
    }
}
