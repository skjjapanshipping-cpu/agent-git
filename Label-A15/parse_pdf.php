<?php
$pdf = file_get_contents('C:/skjtrack/Label-A15/sample.pdf');

// Get rectangles (label borders)
preg_match_all('/(\d+\.?\d*)\s+(\d+\.?\d*)\s+(\d+\.?\d*)\s+(\d+\.?\d*)\s+re/', $pdf, $matches, PREG_SET_ORDER);
echo "=== Rectangles ===\n";
foreach (array_slice($matches, 0, 30) as $i => $r) {
    $x = round($r[1] * 0.3528, 2);
    $y = round($r[2] * 0.3528, 2);
    $w = round($r[3] * 0.3528, 2);
    $h = round($r[4] * 0.3528, 2);
    echo "Rect $i: x={$x}mm y={$y}mm w={$w}mm h={$h}mm (raw: {$r[1]} {$r[2]} {$r[3]} {$r[4]})\n";
}

// Get line positions
preg_match_all('/(\d+\.?\d*)\s+(\d+\.?\d*)\s+m\s+(\d+\.?\d*)\s+(\d+\.?\d*)\s+l/', $pdf, $lines, PREG_SET_ORDER);
echo "\n=== Lines ===\n";
foreach (array_slice($lines, 0, 30) as $i => $l) {
    $x1 = round($l[1] * 0.3528, 2);
    $y1 = round($l[2] * 0.3528, 2);
    $x2 = round($l[3] * 0.3528, 2);
    $y2 = round($l[4] * 0.3528, 2);
    echo "Line $i: ({$x1},{$y1}) -> ({$x2},{$y2}) mm\n";
}

// Get text positioning (Td/Tm commands)
preg_match_all('/(\d+\.?\d*)\s+(\d+\.?\d*)\s+Td/', $pdf, $td, PREG_SET_ORDER);
echo "\n=== Text Positions (Td) ===\n";
foreach (array_slice($td, 0, 30) as $i => $t) {
    $x = round($t[1] * 0.3528, 2);
    $y = round($t[2] * 0.3528, 2);
    echo "Td $i: x={$x}mm y={$y}mm\n";
}
