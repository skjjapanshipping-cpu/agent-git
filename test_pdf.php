<?php
require "/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php";

$p = new Smalot\PdfParser\Parser();
$pdf = $p->parseFile("/var/www/vhosts/skjjapanshipping.com/httpdocs/skjtrack/shippop-invoices/shippop_invoice_1772728540_69a9b0dc319b7.pdf");
$text = $pdf->getText();

$totalAmount = 0;

// Strategy 1: number on same line as ยอดรวม
if (preg_match('/ยอดรวม\s*:?\s*([\d,]+\.\d{2})/u', $text, $matches)) {
    $totalAmount = (float) str_replace(',', '', $matches[1]);
    echo "Strategy 1 matched: " . $totalAmount . "\n";
}

// Strategy 2: last 3 numbers = ยอดรวม/รับเงิน/ทอนเงิน
if ($totalAmount == 0 && preg_match_all('/([\d,]+\.\d{2})\s*$/um', $text, $allMatches)) {
    $nums = $allMatches[1];
    $count = count($nums);
    echo "All end-of-line numbers: " . implode(", ", $nums) . "\n";
    echo "Count: " . $count . "\n";
    if ($count >= 3) {
        $totalAmount = (float) str_replace(',', '', $nums[$count - 3]);
        echo "Strategy 2 matched: " . $totalAmount . "\n";
    }
}

echo "FINAL TOTAL: " . $totalAmount . "\n";
