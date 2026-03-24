<?php
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';

use KS\PromptPay;

// Step 1: Generate PromptPay payload with amount
$pp = new PromptPay();
$payload = $pp->generatePayload('1102001570110', 320.00);
echo "Payload: " . $payload . "\n";

// Step 2: BaconQrCode v1 API
$webRoot = '/var/www/vhosts/skjjapanshipping.com/httpdocs/skjtrack/promptpay-qr';
if (!is_dir($webRoot)) {
    mkdir($webRoot, 0755, true);
}

// BaconQrCode v1: use Renderer\Image\Png or Renderer\Image\Svg
$classes = [
    'BaconQrCode\Renderer\Image\Png',
    'BaconQrCode\Renderer\Image\Svg',
    'BaconQrCode\Renderer\Image\ImagickImageBackEnd',
    'BaconQrCode\Renderer\ImageRenderer',
    'BaconQrCode\Writer',
];
echo "\n--- Available classes ---\n";
foreach ($classes as $c) {
    echo "$c: " . (class_exists($c) ? 'YES' : 'NO') . "\n";
}

// Try v1 API
try {
    $renderer = new \BaconQrCode\Renderer\Image\Png();
    $renderer->setHeight(300);
    $renderer->setWidth(300);
    $writer = new \BaconQrCode\Writer($renderer);
    $filename = 'test_qr_' . time() . '.png';
    $writer->writeFile($payload, $webRoot . '/' . $filename);
    echo "\nPNG saved: " . $webRoot . '/' . $filename . "\n";
    echo "URL: https://skjjapanshipping.com/skjtrack/promptpay-qr/" . $filename . "\n";
} catch (Exception $e) {
    echo "\nv1 PNG failed: " . $e->getMessage() . "\n";
    
    // Try SVG v1
    try {
        $renderer = new \BaconQrCode\Renderer\Image\Svg();
        $renderer->setHeight(300);
        $renderer->setWidth(300);
        $writer = new \BaconQrCode\Writer($renderer);
        $filename = 'test_qr_' . time() . '.svg';
        $writer->writeFile($payload, $webRoot . '/' . $filename);
        echo "SVG saved: " . $webRoot . '/' . $filename . "\n";
        echo "URL: https://skjjapanshipping.com/skjtrack/promptpay-qr/" . $filename . "\n";
    } catch (Exception $e2) {
        echo "v1 SVG failed: " . $e2->getMessage() . "\n";
    }
}
