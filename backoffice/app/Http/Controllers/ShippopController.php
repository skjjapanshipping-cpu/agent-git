<?php

namespace App\Http\Controllers;

use App\Models\Customershipping;
use App\Services\LineMessagingService;
use App\User;
use App\MyAuthProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ShippopController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * แจ้งค่าส่งไทยให้ลูกค้าหลายรายผ่าน LINE — ส่งบิล Shippop + ดึงยอดรวมจาก PDF อัตโนมัติ
     */
    public function notifyThaiShipping(Request $request)
    {
        $request->validate([
            'customer_nos'   => 'required|array|min:1',
            'invoice_files'  => 'required|array|min:1',
            'invoice_files.*' => 'file|max:10240',
        ]);

        $customerNos = $request->input('customer_nos');
        $message     = $request->input('message', '');
        $etd         = $request->input('etd', '');

        // รับ customer_map (JSON) จาก frontend — เก็บ shipping IDs ที่ Admin เลือกต่อลูกค้า
        $customerMap = [];
        if ($request->has('customer_map')) {
            $customerMap = json_decode($request->input('customer_map'), true) ?: [];
        }

        // 1) อัพโหลดไฟล์บิล (รองรับหลายไฟล์)
        $webRoot = '/var/www/vhosts/skjjapanshipping.com/httpdocs/skjtrack/shippop-invoices';
        $uploadedFiles = []; // [{url, path, originalName, isPdf}]
        $originalFilenames = [];

        foreach ($request->file('invoice_files') as $file) {
            $origName = $file->getClientOriginalName();
            $ext = $file->getClientOriginalExtension();
            $isPdf = strtolower($ext) === 'pdf';

            $safeName = preg_replace('/[^a-zA-Z0-9ก-๛._\-\s]/u', '', pathinfo($origName, PATHINFO_FILENAME));
            $safeName = trim($safeName) ?: 'invoice';
            $filename = $safeName . '_' . time() . '_' . uniqid() . '.' . $ext;
            $file->move($webRoot, $filename);

            $uploadedFiles[] = [
                'url'  => 'https://skjjapanshipping.com/skjtrack/shippop-invoices/' . rawurlencode($filename),
                'path' => $webRoot . '/' . $filename,
                'originalName' => $origName,
                'isPdf' => $isPdf,
            ];
            $originalFilenames[] = $origName;
        }

        // ใช้ไฟล์แรกเป็น main (สำหรับ Flex Message card)
        $fileUrl = $uploadedFiles[0]['url'];
        $originalFilename = implode(', ', $originalFilenames);

        // 2) ดึงยอดรวมจาก PDF อัตโนมัติ (รวมยอดจากทุกไฟล์ PDF)
        $totalAmount = 0;
        foreach ($uploadedFiles as $uf) {
            if (!$uf['isPdf']) continue;
            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($uf['path']);
                $text = $pdf->getText();
                $pdfTotal = 0;

                if (preg_match('/ยอดรวม\s*:?\s*([\d,]+\.\d{2})/u', $text, $matches)) {
                    $pdfTotal = (float) str_replace(',', '', $matches[1]);
                }
                if ($pdfTotal == 0 && preg_match_all('/([\d,]+\.\d{2})\s*$/um', $text, $allMatches)) {
                    $nums = $allMatches[1];
                    $count = count($nums);
                    if ($count >= 3) {
                        $pdfTotal = (float) str_replace(',', '', $nums[$count - 3]);
                    }
                }
                Log::info('[THAI-BILL] PDF parsed', ['file' => $uf['originalName'], 'total' => $pdfTotal]);
                $totalAmount += $pdfTotal;
            } catch (\Exception $e) {
                Log::warning('[THAI-BILL] PDF parse failed', ['file' => $uf['originalName'], 'error' => $e->getMessage()]);
            }
        }
        Log::info('[THAI-BILL] Total from all PDFs', ['fileCount' => count($uploadedFiles), 'totalAmount' => $totalAmount]);

        // 3) ส่งผ่าน SKJ Chat API + บันทึก DB ให้ลูกค้าแต่ละราย
        $chatApiUrl = 'https://chat.skjjapanshipping.com/api/thai-bill-send';
        $chatApiKey = 'skjchat-invoice-2026';
        $results = ['success' => 0, 'failed' => 0, 'details' => []];

        foreach ($customerNos as $customerno) {
            $sent = false;
            $statusMsg = '';

            // สร้าง PromptPay QR แบบ dynamic (ฝังยอดเงิน)
            $dynamicQrUrl = \App\Services\PromptPayQrService::generateQrUrl($totalAmount > 0 ? round($totalAmount, 2) : 0, 'thai');

            // สร้าง Flex Message สำหรับ LINE (รองรับหลายไฟล์)
            $hasPdf = collect($uploadedFiles)->contains('isPdf', true);
            $flexMsg = $this->buildThaiShippingFlexMessage($customerno, $totalAmount, $uploadedFiles[0]['url'], $hasPdf, $originalFilename, $uploadedFiles);
            $flexMessages = [];
            if ($message) {
                $flexMessages[] = ['type' => 'text', 'text' => $message];
            }
            $flexMessages[] = $flexMsg;

            // ลองส่งผ่าน SKJ Chat API ก่อน
            try {
                $response = Http::withHeaders([
                    'X-API-Key' => $chatApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($chatApiUrl, [
                    'customerno' => $customerno,
                    'totalAmount' => $totalAmount > 0 ? round($totalAmount, 2) : null,
                    'pdfUrl' => $fileUrl,
                    'originalFilename' => $originalFilename,
                    'message' => $message ?: null,
                    'qrImageUrl' => $dynamicQrUrl,
                    'flexMessages' => $flexMessages,
                    'allFileUrls' => collect($uploadedFiles)->pluck('url')->toArray(),
                ]);

                $data = $response->json();

                if ($response->successful() && ($data['success'] ?? false)) {
                    $sent = true;
                    $contactName = $data['contactName'] ?? '';
                    $platform = $data['platform'] ?? '';
                    $statusMsg = "ส่งผ่านแชทสำเร็จ → {$contactName} ({$platform})";
                } elseif ($response->status() === 404) {
                    // ไม่พบในระบบแชท — fallback ส่ง LINE โดยตรง
                    $statusMsg = $this->fallbackSendLine($customerno, $message, $totalAmount, $fileUrl, $isPdf, $originalFilename);
                    $sent = !empty($statusMsg);
                    if (!$sent) $statusMsg = 'ไม่พบในระบบแชท + ไม่มี LINE account';
                } else {
                    $statusMsg = $data['error'] ?? 'Chat API error';
                }
            } catch (\Exception $e) {
                Log::error('[THAI-BILL] Chat API exception', ['customerno' => $customerno, 'error' => $e->getMessage()]);
                // Fallback ส่ง LINE โดยตรง
                $statusMsg = $this->fallbackSendLine($customerno, $message, $totalAmount, $fileUrl, $isPdf, $originalFilename);
                $sent = !empty($statusMsg);
                if (!$sent) $statusMsg = 'Chat API error + ไม่มี LINE account';
            }

            if ($sent) {
                // อัพเดท DB: thai_bill_status = 1 (ออกบิลแล้ว/รอโอน) — เฉพาะรายการที่ Admin เลือก
                $updateData = [
                    'thai_bill_status' => 1,
                    'thai_bill_amount' => $totalAmount > 0 ? $totalAmount : null,
                    'thai_bill_pdf'    => $fileUrl,
                    'thai_billed_at'   => Carbon::now(),
                ];

                $selectedIds = $customerMap[$customerno] ?? [];
                if (!empty($selectedIds)) {
                    // อัพเดทเฉพาะรายการที่เลือก
                    Customershipping::whereIn('id', $selectedIds)
                        ->where('customerno', $customerno)
                        ->update($updateData);
                } else {
                    // fallback: ถ้าไม่มี IDs ให้ใช้วิธีเดิม
                    $query = Customershipping::where('customerno', $customerno)
                        ->where('excel_status', '1');
                    if ($etd) {
                        $query->whereDate('etd', $etd);
                    }
                    $query->update($updateData);
                }

                $results['success']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'success',
                    'message' => $statusMsg,
                ];
            } else {
                $results['failed']++;
                $results['details'][] = ['customerno' => $customerno, 'status' => 'failed', 'message' => $statusMsg];
            }
        }

        return response()->json([
            'success' => true,
            'message' => "ส่งบิลค่าส่งไทยเสร็จสิ้น: สำเร็จ {$results['success']} ราย, ไม่สำเร็จ {$results['failed']} ราย",
            'results' => $results,
            'total_amount' => $totalAmount,
        ]);
    }

    /**
     * Fallback: ส่ง LINE โดยตรง กรณีลูกค้าไม่อยู่ในระบบแชท
     */
    protected function fallbackSendLine(string $customerno, string $message, float $totalAmount, string $fileUrl, bool $isPdf, string $originalFilename): string
    {
        $user = User::where('customerno', $customerno)->first();
        if (!$user) return '';

        $authProvider = MyAuthProvider::where('userid', $user->id)->where('provider', 'line')->first();
        if (!$authProvider) return '';

        $lineService = new LineMessagingService();
        $messages = [];
        if ($message) {
            $messages[] = ['type' => 'text', 'text' => $message];
        }
        $messages[] = $this->buildThaiShippingFlexMessage($customerno, $totalAmount, $fileUrl, $isPdf, $originalFilename);

        $sent = $lineService->pushMessage($authProvider->providerid, $messages);
        return $sent ? 'ส่ง LINE โดยตรงสำเร็จ (ไม่พบในแชท)' : '';
    }

    /**
     * สร้าง Flex Message สำหรับบิลค่าส่งไทย — แสดงยอดรวม + ปุ่มเปิดบิล
     */
    protected function buildThaiShippingFlexMessage(string $customerno, float $totalAmount, string $fileUrl, bool $isPdf, string $originalFilename = '', array $uploadedFiles = []): array
    {
        // สร้าง PromptPay QR แบบ dynamic (ฝังยอดเงิน) — fallback เป็น static ถ้า amount = 0
        $qrPaymentUrl = \App\Services\PromptPayQrService::generateQrUrl($totalAmount, 'thai');

        $bodyContents = [
            ['type' => 'text', 'text' => 'รหัสลูกค้า', 'size' => 'xs', 'color' => '#AAAAAA'],
            ['type' => 'text', 'text' => strtoupper($customerno), 'size' => 'lg', 'color' => '#333333', 'weight' => 'bold'],
            ['type' => 'separator', 'margin' => 'lg'],
        ];

        if ($totalAmount > 0) {
            $bodyContents[] = [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm',
                'contents' => [
                    ['type' => 'text', 'text' => 'ยอดรวมค่าส่งพัสดุในไทย', 'size' => 'sm', 'color' => '#555555'],
                    ['type' => 'text', 'text' => '฿' . number_format($totalAmount, 2), 'size' => 'xxl', 'color' => '#E53935', 'weight' => 'bold', 'align' => 'center', 'margin' => 'md'],
                ],
            ];
            $bodyContents[] = ['type' => 'separator', 'margin' => 'lg'];
        }

        // QR Code สำหรับสแกนจ่าย + ปุ่มกดชำระเงิน
        $paymentPageUrl = 'https://skjjapanshipping.com/skjtrack/pay.php?amount=' . number_format($totalAmount, 2, '.', '');
        $bodyContents[] = [
            'type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm',
            'alignItems' => 'center',
            'contents' => [
                ['type' => 'text', 'text' => 'สแกน QR Code เพื่อชำระเงิน', 'size' => 'sm', 'color' => '#555555', 'align' => 'center', 'weight' => 'bold'],
                [
                    'type' => 'image',
                    'url' => $qrPaymentUrl,
                    'size' => 'lg',
                    'aspectMode' => 'fit',
                    'margin' => 'md',
                    'action' => ['type' => 'uri', 'label' => 'ชำระเงิน', 'uri' => $paymentPageUrl],
                ],
                [
                    'type' => 'button',
                    'action' => ['type' => 'uri', 'label' => 'กดเพื่อชำระเงิน', 'uri' => $paymentPageUrl],
                    'style' => 'primary', 'color' => '#4CAF50', 'height' => 'sm', 'margin' => 'md',
                ],
            ],
        ];

        $bodyContents[] = ['type' => 'separator', 'margin' => 'lg'];

        // แสดงจำนวนไฟล์บิล
        $fileCount = count($uploadedFiles) ?: 1;
        $footerTexts = [
            ['type' => 'text', 'text' => 'กดปุ่มด้านล่างเพื่อดูรายละเอียดบิล (' . $fileCount . ' ไฟล์)', 'size' => 'xs', 'color' => '#AAAAAA', 'wrap' => true, 'align' => 'center'],
        ];
        $bodyContents[] = [
            'type' => 'box', 'layout' => 'vertical', 'margin' => 'md',
            'contents' => $footerTexts,
        ];

        // สร้างปุ่มเปิดบิลแต่ละไฟล์ใน footer
        $footerButtons = [];
        if (!empty($uploadedFiles) && count($uploadedFiles) > 1) {
            foreach ($uploadedFiles as $idx => $uf) {
                $label = 'เปิดบิล ' . ($idx + 1) . ': ' . mb_substr($uf['originalName'], 0, 20);
                $footerButtons[] = [
                    'type' => 'button',
                    'action' => ['type' => 'uri', 'label' => $label, 'uri' => $uf['url']],
                    'style' => 'primary', 'color' => '#0ea5e9', 'height' => 'sm', 'margin' => $idx === 0 ? 'none' : 'sm',
                ];
            }
        } else {
            $footerButtons[] = [
                'type' => 'button',
                'action' => ['type' => 'uri', 'label' => 'เปิดดูบิลค่าส่ง', 'uri' => $fileUrl],
                'style' => 'primary', 'color' => '#0ea5e9', 'height' => 'md',
            ];
        }

        $bubble = [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box', 'layout' => 'horizontal', 'paddingAll' => '16px',
                'backgroundColor' => '#0ea5e9',
                'spacing' => 'md',
                'contents' => [
                    [
                        'type' => 'image',
                        'url' => 'https://skjjapanshipping.com/skjtrack/img/skj-logo-icon.png',
                        'size' => 'xxs',
                        'aspectMode' => 'fit',
                        'flex' => 0,
                    ],
                    [
                        'type' => 'box', 'layout' => 'vertical', 'flex' => 1,
                        'contents' => [
                            ['type' => 'text', 'text' => 'บิลค่าส่งพัสดุในไทย', 'weight' => 'bold', 'size' => 'md', 'color' => '#FFFFFF'],
                            ['type' => 'text', 'text' => 'SKJ JAPAN SHIPPING', 'size' => 'xs', 'color' => '#B3E5FC'],
                        ],
                    ],
                ],
            ],
            'body' => [
                'type' => 'box', 'layout' => 'vertical', 'paddingAll' => '20px', 'spacing' => 'sm',
                'contents' => $bodyContents,
            ],
            'footer' => [
                'type' => 'box', 'layout' => 'vertical', 'paddingAll' => '12px', 'spacing' => 'sm',
                'contents' => $footerButtons,
            ],
        ];

        $altText = 'บิลค่าส่งพัสดุในไทย - ' . strtoupper($customerno);
        if ($totalAmount > 0) {
            $altText .= ' ยอดรวม ' . number_format($totalAmount, 2) . ' บาท';
        }

        return [
            'type' => 'flex',
            'altText' => $altText,
            'contents' => $bubble,
        ];
    }

    /**
     * ดึงรายชื่อลูกค้าที่ออกบิลค่าส่งไทยแล้วแต่ยังไม่โอน (thai_bill_status = 1)
     * เฉพาะรายการที่เป็น "ส่งในไทย" (delivery_type_id IN 2,3)
     */
    public function getUnpaidCustomers(Request $request)
    {
        $etd = $request->input('etd');
        if (!$etd) {
            return response()->json(['error' => 'กรุณาระบุวันปิดตู้'], 400);
        }

        $customers = Customershipping::where('excel_status', '1')
            ->whereDate('etd', $etd)
            ->where('thai_bill_status', 1)
            ->whereIn('delivery_type_id', [2, 3])
            ->select(
                'customerno',
                DB::raw('COUNT(*) as item_count'),
                DB::raw('MAX(thai_bill_amount) as bill_amount'),
                DB::raw('MAX(thai_bill_pdf) as bill_pdf'),
                DB::raw('MAX(thai_billed_at) as billed_at')
            )
            ->groupBy('customerno')
            ->orderBy('customerno')
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $customers,
            'etd' => $etd,
        ]);
    }

    /**
     * ส่งแจ้งเตือนค่าส่งไทยที่ยังไม่โอน ผ่าน LINE
     */
    public function sendReminder(Request $request)
    {
        $request->validate([
            'customer_nos' => 'required|array|min:1',
            'etd'          => 'required|date',
        ]);

        $customerNos = $request->input('customer_nos');
        $etd         = $request->input('etd');
        $message     = $request->input('message', '');
        $etdFormatted = Carbon::parse($etd)->format('d/m/Y');

        $chatApiUrl = 'https://chat.skjjapanshipping.com/api/thai-bill-send';
        $chatApiKey = 'skjchat-invoice-2026';
        $results = ['success' => 0, 'failed' => 0, 'details' => []];

        foreach ($customerNos as $customerno) {
            // ดึงข้อมูลบิลค้างจ่ายของลูกค้า
            $items = Customershipping::where('customerno', $customerno)
                ->where('excel_status', '1')
                ->whereDate('etd', $etd)
                ->where('thai_bill_status', 1)
                ->whereIn('delivery_type_id', [2, 3])
                ->get();

            if ($items->isEmpty()) {
                $results['details'][] = ['customerno' => $customerno, 'status' => 'skip', 'message' => 'ไม่มีรายการค้างจ่าย'];
                continue;
            }

            $billAmount = $items->max('thai_bill_amount') ?: 0;
            $billPdf    = $items->max('thai_bill_pdf') ?: '';
            $itemCount  = $items->count();

            // สร้างข้อความแจ้งเตือน
            $reminderText = "⏰ แจ้งเตือนค่าส่งพัสดุในไทย\n"
                . "รอบปิดตู้: {$etdFormatted}\n"
                . "รหัสลูกค้า: " . strtoupper($customerno) . "\n"
                . "จำนวน: {$itemCount} ชิ้น\n";
            if ($billAmount > 0) {
                $reminderText .= "ยอดค้างชำระ: ฿" . number_format($billAmount, 2) . "\n";
            }
            $reminderText .= "\nกรุณาชำระเงินด้วยนะครับ 🙏";

            if ($message) {
                $reminderText .= "\n\n" . $message;
            }

            // สร้าง Flex Messages (ข้อความ + บิลเดิม)
            $flexMessages = [['type' => 'text', 'text' => $reminderText]];

            // ถ้ามี PDF บิลเดิม ให้สร้าง Flex card แสดงยอด + ปุ่มเปิดบิล + QR
            if ($billAmount > 0) {
                $flexMessages[] = $this->buildThaiShippingFlexMessage(
                    $customerno, $billAmount, $billPdf ?: 'https://skjjapanshipping.com', false, '', []
                );
            }

            // ส่งผ่าน SKJ Chat API
            $sent = false;
            $statusMsg = '';

            try {
                $response = Http::withHeaders([
                    'X-API-Key' => $chatApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($chatApiUrl, [
                    'customerno'   => $customerno,
                    'totalAmount'  => $billAmount > 0 ? round($billAmount, 2) : null,
                    'message'      => $reminderText,
                    'flexMessages' => $flexMessages,
                ]);

                $data = $response->json();
                if ($response->successful() && ($data['success'] ?? false)) {
                    $sent = true;
                    $statusMsg = 'ส่งแจ้งเตือนสำเร็จ';
                } elseif ($response->status() === 404) {
                    // Fallback LINE โดยตรง
                    $statusMsg = $this->fallbackReminderLine($customerno, $reminderText, $billAmount);
                    $sent = !empty($statusMsg);
                    if (!$sent) $statusMsg = 'ไม่พบในระบบแชท + ไม่มี LINE';
                } else {
                    $statusMsg = $data['error'] ?? 'Chat API error';
                }
            } catch (\Exception $e) {
                Log::error('[THAI-BILL-REMIND] Exception', ['customerno' => $customerno, 'error' => $e->getMessage()]);
                $statusMsg = $this->fallbackReminderLine($customerno, $reminderText, $billAmount);
                $sent = !empty($statusMsg);
                if (!$sent) $statusMsg = 'Chat API error + ไม่มี LINE';
            }

            if ($sent) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
            $results['details'][] = ['customerno' => $customerno, 'status' => $sent ? 'success' : 'failed', 'message' => $statusMsg];
        }

        return response()->json([
            'success' => true,
            'message' => "แจ้งเตือนเสร็จสิ้น: สำเร็จ {$results['success']} ราย, ไม่สำเร็จ {$results['failed']} ราย",
            'results' => $results,
        ]);
    }

    /**
     * Fallback: ส่งแจ้งเตือนผ่าน LINE โดยตรง
     */
    protected function fallbackReminderLine(string $customerno, string $message, float $billAmount): string
    {
        $user = User::where('customerno', $customerno)->first();
        if (!$user) return '';

        $authProvider = MyAuthProvider::where('userid', $user->id)->where('provider', 'line')->first();
        if (!$authProvider) return '';

        $lineService = new LineMessagingService();
        $messages = [['type' => 'text', 'text' => $message]];

        if ($billAmount > 0) {
            $paymentPageUrl = 'https://skjjapanshipping.com/skjtrack/pay.php?amount=' . number_format($billAmount, 2, '.', '');
            $qrPaymentUrl = \App\Services\PromptPayQrService::generateQrUrl($billAmount, 'thai');
            $messages[] = [
                'type' => 'flex',
                'altText' => 'แจ้งเตือนค่าส่งไทย ฿' . number_format($billAmount, 2),
                'contents' => [
                    'type' => 'bubble', 'size' => 'kilo',
                    'body' => [
                        'type' => 'box', 'layout' => 'vertical', 'paddingAll' => '16px', 'spacing' => 'md',
                        'contents' => [
                            ['type' => 'text', 'text' => '⏰ แจ้งเตือนค่าส่งไทย', 'weight' => 'bold', 'size' => 'md', 'color' => '#E53935'],
                            ['type' => 'text', 'text' => '฿' . number_format($billAmount, 2), 'size' => 'xxl', 'color' => '#E53935', 'weight' => 'bold', 'align' => 'center'],
                            ['type' => 'image', 'url' => $qrPaymentUrl, 'size' => 'lg', 'aspectMode' => 'fit',
                             'action' => ['type' => 'uri', 'label' => 'ชำระเงิน', 'uri' => $paymentPageUrl]],
                            ['type' => 'button', 'action' => ['type' => 'uri', 'label' => 'กดเพื่อชำระเงิน', 'uri' => $paymentPageUrl],
                             'style' => 'primary', 'color' => '#4CAF50', 'height' => 'sm'],
                        ],
                    ],
                ],
            ];
        }

        $sent = $lineService->pushMessage($authProvider->providerid, $messages);
        return $sent ? 'ส่ง LINE โดยตรงสำเร็จ' : '';
    }

}
