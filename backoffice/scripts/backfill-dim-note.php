<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$apply = in_array('--apply', $argv, true);

$fmt = function ($v) {
    $v = $v + 0;
    return is_int($v) ? (string) $v : rtrim(rtrim(number_format($v, 2, '.', ''), '0'), '.');
};

// Only rows with dimensions measured; preserve any non-auto note
$rows = Customershipping::where('iswholeprice', 1)
    ->whereNotNull('width')->where('width', '>', 0)
    ->whereNotNull('length')->where('length', '>', 0)
    ->whereNotNull('height')->where('height', '>', 0)
    ->orderBy('id')
    ->get(['id','customerno','box_no','width','length','height','note','etd']);

$willUpdate = 0;
$skippedCustom = 0;
$alreadyCorrect = 0;
$preview = [];

foreach ($rows as $r) {
    $dimNote = $fmt($r->width) . '*' . $fmt($r->length) . '*' . $fmt($r->height) . 'cm';
    $existing = trim((string) $r->note);

    $isEmpty = $existing === '';
    $isAutoDim = (bool) preg_match('/^\d+(?:\.\d+)?\*\d+(?:\.\d+)?\*\d+(?:\.\d+)?cm$/u', $existing);

    if ($isEmpty) {
        $willUpdate++;
        if (count($preview) < 15) {
            $preview[] = "id={$r->id} {$r->customerno} box={$r->box_no} etd={$r->etd} : (empty) -> {$dimNote}";
        }
        if ($apply) {
            Customershipping::where('id', $r->id)->update(['note' => $dimNote]);
        }
    } elseif ($isAutoDim) {
        if ($existing === $dimNote) {
            $alreadyCorrect++;
        } else {
            $willUpdate++;
            if (count($preview) < 15) {
                $preview[] = "id={$r->id} {$r->customerno} box={$r->box_no} : {$existing} -> {$dimNote}";
            }
            if ($apply) {
                Customershipping::where('id', $r->id)->update(['note' => $dimNote]);
            }
        }
    } else {
        // Non-auto customer note → never touch
        $skippedCustom++;
    }
}

echo "Rows scanned:          " . $rows->count() . "\n";
echo "Already correct:       {$alreadyCorrect}\n";
echo "Will update:           {$willUpdate}\n";
echo "Skipped (customer note): {$skippedCustom}\n";
echo "\nPreview (first " . count($preview) . "):\n";
foreach ($preview as $p) { echo "  {$p}\n"; }

if (!$apply) {
    echo "\nDry-run only. Re-run with --apply to write.\n";
} else {
    echo "\nDONE. Updates applied.\n";
}
