<?php
// Parse PDF by decompressing streams
$raw = file_get_contents('C:/skjtrack/Label-A15/sample.pdf');

// Find and decompress FlateDecode streams
preg_match_all('/stream\r?\n(.+?)\r?\nendstream/s', $raw, $streams);

echo "Found " . count($streams[1]) . " streams\n\n";

foreach ($streams[1] as $i => $stream) {
    $decoded = @gzuncompress($stream);
    if (!$decoded) {
        $decoded = @gzinflate($stream);
    }
    if (!$decoded) {
        // Try removing first 2 bytes (zlib header)
        $decoded = @gzinflate(substr($stream, 2));
    }
    if ($decoded) {
        echo "=== Stream $i (decoded " . strlen($decoded) . " bytes) ===\n";
        
        // Find rectangles
        preg_match_all('/([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+re/', $decoded, $rects, PREG_SET_ORDER);
        foreach ($rects as $r) {
            $x = round($r[1] * 0.3528, 2);
            $y = round($r[2] * 0.3528, 2);
            $w = round($r[3] * 0.3528, 2);
            $h = round($r[4] * 0.3528, 2);
            echo "  RECT: x={$x}mm y={$y}mm w={$w}mm h={$h}mm\n";
        }
        
        // Find lines
        preg_match_all('/([\d.]+)\s+([\d.]+)\s+m\s+([\d.]+)\s+([\d.]+)\s+l/', $decoded, $lines, PREG_SET_ORDER);
        foreach ($lines as $l) {
            $x1 = round($l[1] * 0.3528, 2);
            $y1 = round($l[2] * 0.3528, 2);
            $x2 = round($l[3] * 0.3528, 2);
            $y2 = round($l[4] * 0.3528, 2);
            echo "  LINE: ({$x1},{$y1}) -> ({$x2},{$y2}) mm\n";
        }

        // Find text matrix (Tm)
        preg_match_all('/([\d.]+)\s+[\d.]+\s+[\d.]+\s+([\d.]+)\s+([\d.]+)\s+([\d.]+)\s+Tm/', $decoded, $tms, PREG_SET_ORDER);
        foreach ($tms as $t) {
            $fontSize = round($t[1], 1);
            $x = round($t[3] * 0.3528, 2);
            $y = round($t[4] * 0.3528, 2);
            echo "  TEXT: x={$x}mm y={$y}mm fontSize={$fontSize}\n";
        }

        // Find BT..ET blocks with text
        preg_match_all('/BT\s*(.*?)\s*ET/s', $decoded, $btBlocks, PREG_SET_ORDER);
        foreach ($btBlocks as $bt) {
            $block = $bt[1];
            // Get Td positions
            preg_match_all('/([\d.-]+)\s+([\d.-]+)\s+Td/', $block, $tds, PREG_SET_ORDER);
            // Get Tj text
            preg_match_all('/\((.*?)\)\s*Tj/', $block, $tjs, PREG_SET_ORDER);
            if (!empty($tds)) {
                $tx = round($tds[0][1] * 0.3528, 2);
                $ty = round($tds[0][2] * 0.3528, 2);
                $text = !empty($tjs) ? $tjs[0][1] : '?';
                echo "  BT: x={$tx}mm y={$ty}mm text='{$text}'\n";
            }
        }

        echo "\n";
    }
}
