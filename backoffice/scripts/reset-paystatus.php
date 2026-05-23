<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;
use Illuminate\Support\Facades\DB;

$customer = $argv[1] ?? 'ANW-646';
$etdLike  = $argv[2] ?? '2026-03-23%';
$apply    = in_array('--apply', $argv, true);

$rows = Customershipping::where('customerno', $customer)
    ->where('etd', 'like', $etdLike)
    ->whereIn('pay_status', [5])
    ->get();

echo "Customer={$customer} etd LIKE {$etdLike}\n";
echo "Rows with pay_status=5: {$rows->count()}\n";
foreach ($rows as $r) {
    echo "  - id={$r->id} box={$r->box_no} track={$r->track_no} updated={$r->updated_at}\n";
}

if (!$apply) {
    echo "\nDry-run only. Add --apply to reset pay_status 5 -> 1 for these rows.\n";
    exit;
}

$affected = Customershipping::where('customerno', $customer)
    ->where('etd', 'like', $etdLike)
    ->where('pay_status', 5)
    ->update(['pay_status' => 1, 'updated_at' => now()]);

echo "\nUpdated rows: {$affected}\n";
