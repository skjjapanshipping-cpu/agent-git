<?php
$ch = curl_init('https://chat.skjjapanshipping.com/api/thai-bill-send');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'X-API-Key: skjchat-invoice-2026',
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'customerno' => 'ANW-500',
        'totalAmount' => 320,
        'pdfUrl' => 'https://skjjapanshipping.com/skjtrack/shippop-invoices/shippop_invoice_1772734505_69a9c829d5a39.pdf',
        'originalFilename' => 'ANW-502 28-02-2026.pdf',
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30,
]);
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);
echo "HTTP: {$code}\n";
echo "Error: {$err}\n";
echo "Response: {$resp}\n";
