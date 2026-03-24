<?php
// Test SKJ Chat API response format
// Test invoice-check
$ch = curl_init('https://chat.skjjapanshipping.com/api/invoice-check');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'X-API-Key: skjchat-invoice-2026',
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode(['customer_nos' => ['ANW-500', 'ANW-501']]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
$response = curl_exec($ch);
curl_close($ch);
echo "invoice-check:\n{$response}\n\n";

// Test invoice-send check_only mode (might have platform info)
$ch2 = curl_init('https://chat.skjjapanshipping.com/api/invoice-check');
curl_setopt_array($ch2, [
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'X-API-Key: skjchat-invoice-2026',
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode(['customer_nos' => ['ANW-500', 'ANW-501'], 'include_platform' => true]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 15,
]);
$response2 = curl_exec($ch2);
curl_close($ch2);
echo "with include_platform:\n{$response2}\n";
