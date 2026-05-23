<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$apply = in_array('--apply', $argv, true);

// ราคาเหมา rows ที่มีขนาดครบ แต่ import_cost = 0/null
$rows = Customershipping::where('iswholeprice', 1)
    ->whereNotNull('width')->where('width', '>', 0)
    ->whereNotNull('length')->where('length', '>', 0)
    ->whereNotNull('height')->where('height', '>', 0)
    ->where(function ($q) {
        $q->whereNull('import_cost')->orWhere('import_cost', '<=', 0);
    })
    ->orderBy('id')
    ->get(['id','customerno','box_no','etd','width','length','height','import_cost']);

$willUpdate = 0;
$preview = [];

foreach ($rows as $r) {
    $w = (float) $r->width;
    $l = (float) $r->length;
    $h = (float) $r->height;
    $calc = round($w * $l * $h * 0.01, 2);

    if ($calc <= 0) continue;

    $willUpdate++;
    if (count($preview) < 30) {
        $preview[] = sprintf(
            'id=%d %s box=%s etd=%s : %sx%sx%s -> %.2f (was %s)',
            $r->id, $r->customerno, $r->box_no, $r->etd,
            $w, $l, $h, $calc, ($r->import_cost ?? 'null')
        );
    }
    if ($apply) {
        Customershipping::where('id', $r->id)->update(['import_cost' => $calc]);
    }
}

echo "Rows scanned:    " . $rows->count() . "\n";
echo "Will update:     {$willUpdate}\n\n";
echo "Preview (first " . count($preview) . "):\n";
foreach ($preview as $p) { echo "  {$p}\n"; }

if (!$apply) {
    echo "\nDry-run only. Re-run with --apply to write.\n";
} else {
    echo "\nDONE. Updates applied.\n";
}
