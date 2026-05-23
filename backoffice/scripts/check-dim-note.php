<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Customershipping;

$rows = Customershipping::where('iswholeprice', 1)
    ->whereNotNull('picked_up_at')
    ->orderBy('picked_up_at', 'desc')
    ->limit(15)
    ->get(['id','customerno','box_no','width','length','height','note','picked_up_at']);

foreach ($rows as $r) {
    echo sprintf(
        "id=%d | %s | box=%s | WxLxH=%sx%sx%s | note=%s | picked=%s\n",
        $r->id, $r->customerno, $r->box_no,
        $r->width ?? '-', $r->length ?? '-', $r->height ?? '-',
        ($r->note ?: '(empty)'),
        $r->picked_up_at
    );
}
