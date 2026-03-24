#!/usr/bin/env python3
"""
1. Update reminder text to show each bill separately (บิลแรก/บิลสอง with count + amount)
2. Remove 'เปิดดูบิลค่าส่ง' button from Flex card in reminder
"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/ShippopController.php'
with open(path, 'r') as f:
    content = f.read()

# 1. Replace the reminder text building section in sendReminder
old_reminder = '''            $billAmount = $items->pluck('thai_bill_amount')->filter()->unique()->sum() ?: 0;
            $billPdf    = $items->max('thai_bill_pdf') ?: '';
            $itemCount  = $items->count();

            // สร้างข้อความแจ้งเตือน
            $reminderText = "⏰ แจ้งเตือนค่าส่งพัสดุในไทย\\n"
                . "รอบปิดตู้: {$etdFormatted}\\n"
                . "รหัสลูกค้า: " . strtoupper($customerno) . "\\n"
                . "จำนวน: {$itemCount} ชิ้น\\n";
            if ($billAmount > 0) {
                $reminderText .= "ยอดค้างชำระ: ฿" . number_format($billAmount, 2) . "\\n";
            }
            $reminderText .= "\\nกรุณาชำระเงินด้วยนะครับ 🙏";

            if ($message) {
                $reminderText .= "\\n\\n" . $message;
            }

            // สร้าง Flex Messages (ข้อความ + บิลเดิม)
            $flexMessages = [['type' => 'text', 'text' => $reminderText]];

            // ถ้ามี PDF บิลเดิม ให้สร้าง Flex card แสดงยอด + ปุ่มเปิดบิล + QR
            if ($billAmount > 0) {
                $flexMessages[] = $this->buildThaiShippingFlexMessage(
                    $customerno, $billAmount, $billPdf ?: 'https://skjjapanshipping.com', false, '', []
                );
            }'''

new_reminder = '''            // รวมยอดบิลค้างจ่ายทั้งหมด (แยกตามจำนวนเงินแต่ละบิล)
            $billGroups = $items->groupBy('thai_bill_amount')->filter(function($group, $key) {
                return $key > 0;
            });
            $billAmount = $items->pluck('thai_bill_amount')->filter()->unique()->sum() ?: 0;
            $billPdf    = $items->max('thai_bill_pdf') ?: '';

            // สร้างข้อความแจ้งเตือน — แสดงรายละเอียดแต่ละบิล
            $ordinals = ['บิลแรก', 'บิลสอง', 'บิลสาม', 'บิลสี่', 'บิลห้า'];
            $reminderText = "⏰ แจ้งเตือนค่าส่งพัสดุในไทย\\n"
                . "รอบปิดตู้: {$etdFormatted}\\n"
                . "รหัสลูกค้า: " . strtoupper($customerno) . "\\n";

            $billIdx = 0;
            foreach ($billGroups as $amt => $group) {
                $label = $ordinals[$billIdx] ?? ('บิลที่ ' . ($billIdx + 1));
                $reminderText .= "{$label} จำนวน: " . $group->count() . " ชิ้น ฿" . number_format((float)$amt, 0) . "\\n";
                $billIdx++;
            }
            // ถ้ามีแค่บิลเดียว ก็แสดงจำนวนรวมด้วย
            if ($billIdx === 0) {
                $reminderText .= "จำนวน: " . $items->count() . " ชิ้น\\n";
            }

            if ($billAmount > 0) {
                $reminderText .= "ยอดค้างชำระ: ฿" . number_format($billAmount, 2) . "\\n";
            }
            $reminderText .= "\\nกรุณาชำระเงินด้วยนะครับ 🙏";

            if ($message) {
                $reminderText .= "\\n\\n" . $message;
            }

            // สร้าง Flex Messages (ข้อความ + การ์ด QR ไม่มีปุ่มเปิดบิล)
            $flexMessages = [['type' => 'text', 'text' => $reminderText]];

            if ($billAmount > 0) {
                $flexMessages[] = $this->buildReminderFlexCard($customerno, $billAmount);
            }'''

content = content.replace(old_reminder, new_reminder)
print('[1] Updated reminder text with per-bill details')

# 2. Add buildReminderFlexCard method (no bill PDF button)
# Insert before the last closing brace
method = '''
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

'''

if 'buildReminderFlexCard' not in content:
    last_brace = content.rfind('}')
    content = content[:last_brace] + method + content[last_brace:]
    print('[2] Added buildReminderFlexCard method (no bill PDF button)')
else:
    print('[2] buildReminderFlexCard already exists')

with open(path, 'w') as f:
    f.write(content)

print('Done!')
