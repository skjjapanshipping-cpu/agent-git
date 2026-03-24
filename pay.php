<?php
/**
 * PromptPay Payment Page — แสดง QR Code พร้อมยอดเงิน
 * URL: https://skjjapanshipping.com/skjtrack/pay.php?amount=82.50
 */
require '/var/www/vhosts/skjjapanshipping.com/backoffice/vendor/autoload.php';

$amount = isset($_GET['amount']) ? floatval($_GET['amount']) : 0;
$promptPayId = '1102001570110';

if ($amount <= 0) {
    echo 'Invalid amount';
    exit;
}

// สร้าง PromptPay payload
$pp = new \KS\PromptPay();
$payload = $pp->generatePayload($promptPayId, $amount);

// สร้าง QR image เป็น base64
$renderer = new \BaconQrCode\Renderer\Image\Png();
$renderer->setHeight(400);
$renderer->setWidth(400);
$writer = new \BaconQrCode\Writer($renderer);
$qrPng = $writer->writeString($payload);
$qrBase64 = 'data:image/png;base64,' . base64_encode($qrPng);

$formattedAmount = number_format($amount, 2);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ชำระเงิน PromptPay - SKJ Japan Shipping</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            background: #fff;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 380px;
            width: 100%;
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1a1a2e, #16213e);
            padding: 20px;
            text-align: center;
            color: #fff;
        }
        .header img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            margin-bottom: 8px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: 600;
        }
        .header p {
            font-size: 13px;
            opacity: 0.8;
            margin-top: 4px;
        }
        .amount-section {
            text-align: center;
            padding: 24px 20px 8px;
        }
        .amount-label {
            font-size: 14px;
            color: #888;
        }
        .amount-value {
            font-size: 42px;
            font-weight: 700;
            color: #1a1a2e;
            margin-top: 4px;
        }
        .amount-value span {
            font-size: 24px;
            color: #666;
        }
        .qr-section {
            text-align: center;
            padding: 16px 30px 20px;
        }
        .qr-section img {
            width: 100%;
            max-width: 280px;
            border-radius: 12px;
            border: 3px solid #eee;
        }
        .qr-label {
            font-size: 13px;
            color: #999;
            margin-top: 12px;
        }
        .promptpay-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0f4ff;
            border: 1px solid #d0d8f0;
            border-radius: 20px;
            padding: 6px 14px;
            margin-top: 12px;
            font-size: 13px;
            color: #3b5998;
            font-weight: 500;
        }
        .footer {
            background: #f8f9fa;
            padding: 16px 20px;
            text-align: center;
            border-top: 1px solid #eee;
        }
        .footer p {
            font-size: 12px;
            color: #999;
        }
        .footer .company {
            font-weight: 600;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="header">
            <h1>SKJ JAPAN SHIPPING</h1>
            <p>PromptPay Payment</p>
        </div>

        <div class="amount-section">
            <div class="amount-label">ยอดชำระ</div>
            <div class="amount-value"><span>฿</span><?= $formattedAmount ?></div>
        </div>

        <div class="qr-section">
            <img src="<?= $qrBase64 ?>" alt="PromptPay QR Code">
            <div class="qr-label">สแกน QR Code ด้วยแอปธนาคารเพื่อชำระเงิน</div>
            <div class="promptpay-badge">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                PromptPay · ยอดเงินฝังใน QR แล้ว
            </div>
        </div>

        <div class="footer">
            <p class="company">SKJ Japan Shipping Co., Ltd.</p>
            <p>QR Code นี้ใช้ได้ครั้งเดียว กรุณาชำระตามยอดที่แสดง</p>
        </div>
    </div>
</body>
</html>
