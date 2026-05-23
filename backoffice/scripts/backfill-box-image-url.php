<?php
/**
 * Backfill box_image URLs:
 *   - Convert Google Drive share-link URLs (any format) to lh3 viewable URLs.
 *   - Run with --apply to actually save; default is dry-run preview.
 *   - Optional: --etd=2026-05-07 to limit to one round.
 *
 * Usage:
 *   cd /var/www/vhosts/skjjapanshipping.com/backoffice
 *   php scripts/backfill-box-image-url.php             # dry-run, all rows
 *   php scripts/backfill-box-image-url.php --etd=2026-05-07
 *   php scripts/backfill-box-image-url.php --apply --etd=2026-05-07
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$apply = in_array('--apply', $argv, true);
$etd   = null;
foreach ($argv as $a) {
    if (strpos($a, '--etd=') === 0) $etd = substr($a, 6);
}

function gdriveToImageUrl(?string $url): ?string {
    if (empty($url)) return $url;
    // Already a lh3 URL — leave alone
    if (strpos($url, 'lh3.googleusercontent.com') !== false) return $url;
    // Not a Drive URL at all — leave alone
    if (strpos($url, 'drive.google.com') === false) return $url;

    $patterns = [
        '/\/file\/d\/([a-zA-Z0-9_-]{25,})/',
        '/\/d\/([a-zA-Z0-9_-]{25,})/',
        '/[?&]id=([a-zA-Z0-9_-]{25,})/',
    ];
    foreach ($patterns as $p) {
        if (preg_match($p, $url, $m)) {
            return 'https://lh3.googleusercontent.com/d/' . $m[1] . '=w500';
        }
    }
    return $url;
}

$query = Customershipping::query()
    ->whereNotNull('box_image')
    ->where('box_image', '!=', '')
    ->where('box_image', 'like', '%drive.google.com%');

if ($etd) $query->whereRaw('DATE(etd) = ?', [$etd]);

$rows = $query->orderBy('id')->get(['id', 'customerno', 'box_no', 'etd', 'box_image']);

echo "MODE: " . ($apply ? "APPLY (will save)" : "DRY-RUN (no changes)") . "\n";
if ($etd) echo "ETD filter: $etd\n";
echo "Found " . $rows->count() . " rows with drive.google.com URL\n";
echo str_repeat('-', 100) . "\n";

$changed = 0;
$skipped = 0;
foreach ($rows as $r) {
    $newUrl = gdriveToImageUrl($r->box_image);
    if ($newUrl === $r->box_image) {
        $skipped++;
        continue;
    }
    $changed++;
    $etdLabel = $r->etd ? $r->etd->format('Y-m-d') : '-';
    echo "ID={$r->id} {$r->customerno} Box.{$r->box_no} ($etdLabel)\n";
    echo "  OLD: {$r->box_image}\n";
    echo "  NEW: {$newUrl}\n";
    if ($apply) {
        Customershipping::where('id', $r->id)->update(['box_image' => $newUrl]);
    }
}

echo str_repeat('-', 100) . "\n";
echo "Changed: $changed   Skipped: $skipped\n";
echo $apply
    ? "✅ Done. Records updated.\n"
    : "🔍 Dry-run complete. Re-run with --apply to save.\n";
