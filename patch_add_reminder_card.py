#!/usr/bin/env python3
"""Add buildReminderFlexCard method to ShippopController"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/ShippopController.php'
with open(path, 'r') as f:
    content = f.read()

if 'function buildReminderFlexCard' in content:
    print('Method already exists')
    exit()

# Find the fallbackReminderLine method's closing brace pattern
marker = "        return $sent ? 'ส่ง LINE โดยตรงสำเร็จ' : '';\n    }\n\n}"
new_code = """        return $sent ? 'ส่ง LINE โดยตรงสำเร็จ' : '';
    }

    /**
     * สร้าง Flex card สำหรับแจ้งเตือนค้างจ่าย (ไม่มีปุ่มเปิดบิล)
     */
    protected function buildReminderFlexCard(string $customerno, float $totalAmount): array
    {
        $qrPaymentUrl = \\App\\Services\\PromptPayQrService::generateQrUrl($totalAmount, 'thai');
        $paymentPageUrl = 'https://skjjapanshipping.com/skjtrack/pay.php?amount=' . number_format($totalAmount, 2, '.', '');

        $bodyContents = [
            ['type' => 'text', 'text' => 'รหัสลูกค้า', 'size' => 'xs', 'color' => '#AAAAAA'],
            ['type' => 'text', 'text' => strtoupper($customerno), 'size' => 'lg', 'color' => '#333333', 'weight' => 'bold'],
            ['type' => 'separator', 'margin' => 'lg'],
            [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm',
                'contents' => [
                    ['type' => 'text', 'text' => 'ยอดรวมค่าส่งพัสดุในไทย', 'size' => 'sm', 'color' => '#555555'],
                    ['type' => 'text', 'text' => '฿' . number_format($totalAmount, 2), 'size' => 'xxl', 'color' => '#E53935', 'weight' => 'bold', 'align' => 'center', 'margin' => 'md'],
                ],
            ],
            ['type' => 'separator', 'margin' => 'lg'],
            [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm',
                'alignItems' => 'center',
                'contents' => [
                    ['type' => 'text', 'text' => 'สแกน QR Code เพื่อชำระเงิน', 'size' => 'sm', 'color' => '#555555', 'align' => 'center', 'weight' => 'bold'],
                    [
                        'type' => 'image', 'url' => $qrPaymentUrl, 'size' => 'lg', 'aspectMode' => 'fit', 'margin' => 'md',
                        'action' => ['type' => 'uri', 'label' => 'ชำระเงิน', 'uri' => $paymentPageUrl],
                    ],
                    [
                        'type' => 'button',
                        'action' => ['type' => 'uri', 'label' => 'กดเพื่อชำระเงิน', 'uri' => $paymentPageUrl],
                        'style' => 'primary', 'color' => '#4CAF50', 'height' => 'sm', 'margin' => 'md',
                    ],
                ],
            ],
        ];

        return [
            'type' => 'flex',
            'altText' => 'แจ้งเตือนค่าส่งไทย - ' . strtoupper($customerno) . ' ฿' . number_format($totalAmount, 2),
            'contents' => [
                'type' => 'bubble', 'size' => 'mega',
                'header' => [
                    'type' => 'box', 'layout' => 'horizontal', 'paddingAll' => '16px',
                    'backgroundColor' => '#ef4444', 'spacing' => 'md',
                    'contents' => [
                        ['type' => 'image', 'url' => 'https://skjjapanshipping.com/skjtrack/img/skj-logo-icon.png', 'size' => 'xxs', 'aspectMode' => 'fit', 'flex' => 0],
                        [
                            'type' => 'box', 'layout' => 'vertical', 'flex' => 1,
                            'contents' => [
                                ['type' => 'text', 'text' => '⏰ แจ้งเตือนค่าส่งไทย', 'weight' => 'bold', 'size' => 'md', 'color' => '#FFFFFF'],
                                ['type' => 'text', 'text' => 'SKJ JAPAN SHIPPING', 'size' => 'xs', 'color' => '#FECACA'],
                            ],
                        ],
                    ],
                ],
                'body' => ['type' => 'box', 'layout' => 'vertical', 'paddingAll' => '20px', 'spacing' => 'sm', 'contents' => $bodyContents],
            ],
        ];
    }

}"""

content = content.replace(marker, new_code)
with open(path, 'w') as f:
    f.write(content)
print('Added buildReminderFlexCard method')
