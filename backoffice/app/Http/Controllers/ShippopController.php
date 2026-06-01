<?php

namespace App\Http\Controllers;

use App\Models\Customershipping;
use App\Models\ExtraShippingCharge;
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
            'invoice_files.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $customerNos = $request->input('customer_nos');
        $message     = $request->input('message', '');
        $etd         = $request->input('etd', '');

        if (count($customerNos) > 1) {
            return back()->with('error', 'กรุณาส่งบิลค่าส่งไทยทีละ 1 รหัสลูกค้า');
        }

        // รับ customer_map (JSON) จาก frontend — เก็บ shipping IDs ที่ Admin เลือกต่อลูกค้า
        $customerMap = [];
        if ($request->has('customer_map')) {
            $customerMap = json_decode($request->input('customer_map'), true) ?: [];
        }

        // รับ parsed_items (JSON) — รายการ shipment ต่อ Box ที่ admin review/แก้แล้ว
        // schema: [{ refNo, courier, destination, recipientName, totalPrice, boxes: [int] }, ...]
        // - มี box  → update Customershipping per-box ตามเดิม
        // - ไม่มี box → save เป็น ExtraShippingCharge (ค่าบริการเพิ่มเติม เช่น Repack, ค่าธรรมเนียม)
        $parsedItems = [];
        if ($request->has('parsed_items')) {
            $raw = $request->input('parsed_items');
            $decoded = is_array($raw) ? $raw : (json_decode((string) $raw, true) ?: []);
            foreach ($decoded as $row) {
                if (!is_array($row)) continue;
                $boxes = $row['boxes'] ?? [];
                if (is_string($boxes)) {
                    $boxes = preg_split('/[\s,+]+/u', $boxes);
                }
                $boxes = array_values(array_filter(array_map('intval', (array) $boxes), function($x){ return $x > 0; }));
                $refNo     = trim((string) ($row['refNo'] ?? ''));
                $recipient = trim((string) ($row['recipientName'] ?? ($row['recipient'] ?? '')));
                $price     = (float) ($row['totalPrice'] ?? 0);
                // เก็บแถวที่มีข้อมูลพอจะตาม trace ได้: ref หรือ box หรือ (recipient + price)
                if ($refNo === '' && empty($boxes) && ($recipient === '' || $price <= 0)) continue;
                $parsedItems[] = [
                    'refNo'         => $refNo,
                    'courier'       => trim((string) ($row['courier'] ?? '')),
                    'destination'   => trim((string) ($row['destination'] ?? '')),
                    'recipientName' => $recipient,
                    'totalPrice'    => $price,
                    'boxes'         => $boxes,
                ];
            }
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

        $hasPdfFiles = collect($uploadedFiles)->contains('isPdf', true);
        $parseWarning = null;
        if ($hasPdfFiles && $totalAmount == 0) {
            $parseWarning = '⚠️ ไม่สามารถอ่านยอดเงินจาก PDF ได้ — ยอดจะเป็น 0 บาท กรุณาตรวจสอบ';
        }

        // 3) ส่งผ่าน SKJ Chat API + บันทึก DB ให้ลูกค้าแต่ละราย
        $chatBase   = rtrim((string) config('services.skjchat.base_url'), '/');
        $chatApiUrl = $chatBase . '/api/thai-bill-send';
        $chatApiKey = (string) config('services.skjchat.api_key');
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

            // ลองส่งผ่าน SKJ Chat API ก่อน (retry สูงสุด 3 ครั้งกรณี timeout/network)
            $maxAttempts = 3;
            $lastException = null;
            for ($attempt = 1; $attempt <= $maxAttempts; $attempt++) {
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
                        $lastException = null;
                        break; // success — exit retry loop
                    } elseif ($response->status() === 404) {
                        $statusMsg = $this->fallbackSendLine($customerno, $message, $totalAmount, $fileUrl, $hasPdf, $originalFilename);
                        $sent = !empty($statusMsg);
                        if (!$sent) $statusMsg = 'ไม่พบในระบบแชท + ไม่มี LINE account';
                        $lastException = null;
                        break; // 404 not retryable — fallback handled
                    } elseif ($response->successful() && empty($data['success'])) {
                        // API responded 200 แต่ success:false — มักเป็น LINE-level failure (e.g. timeout, invalid msg)
                        $results_summary = '';
                        if (!empty($data['results']) && is_array($data['results'])) {
                            $failed = array_filter($data['results'], fn($r) => ($r['status'] ?? '') !== 'sent');
                            $errs = array_map(fn($r) => ($r['step'] ?? '?') . ':' . ($r['error'] ?? '?'), $failed);
                            $results_summary = ' [' . implode(' | ', $errs) . ']';
                        }
                        Log::warning('[THAI-BILL] Chat API success:false', [
                            'customerno' => $customerno,
                            'attempt'    => $attempt,
                            'response'   => $data,
                        ]);
                        $statusMsg = ($data['error'] ?? 'ส่งไม่สำเร็จ') . $results_summary;
                        if ($attempt < $maxAttempts) {
                            sleep(2); // wait 2s before retry
                            continue;
                        }
                    } else {
                        Log::warning('[THAI-BILL] Chat API non-2xx', [
                            'customerno' => $customerno,
                            'status'     => $response->status(),
                            'body'       => mb_substr((string) $response->body(), 0, 500),
                        ]);
                        $statusMsg = $data['error'] ?? ('Chat API HTTP ' . $response->status());
                        if ($attempt < $maxAttempts && in_array($response->status(), [500, 502, 503, 504])) {
                            sleep(2);
                            continue;
                        }
                    }
                    break;
                } catch (\Exception $e) {
                    $lastException = $e;
                    Log::warning('[THAI-BILL] Chat API exception', [
                        'customerno' => $customerno,
                        'attempt'    => $attempt,
                        'error'      => $e->getMessage(),
                    ]);
                    if ($attempt < $maxAttempts) {
                        sleep(2);
                        continue;
                    }
                }
            }

            // ถ้ายังไม่สำเร็จหลังจาก retry และมี exception — fallback ไปยัง LINE โดยตรง
            if (!$sent && $lastException) {
                Log::error('[THAI-BILL] Chat API failed after retries, falling back to direct LINE', [
                    'customerno' => $customerno,
                    'error'      => $lastException->getMessage(),
                ]);
                $statusMsg = $this->fallbackSendLine($customerno, $message, $totalAmount, $fileUrl, $hasPdf, $originalFilename);
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

                // อัพเดทข้อมูล per-box (เลขอ้างอิง/ราคา/courier) ตามที่ admin review
                // หา etd_date สำหรับ extra charges (ใช้ etd จาก request → fallback จาก first record)
                $now = Carbon::now();
                $etdForExtra = $etd ?: null;
                if (!$etdForExtra && !empty($selectedIds)) {
                    $firstRow = Customershipping::whereIn('id', $selectedIds)->first();
                    if ($firstRow && $firstRow->etd) {
                        $etdForExtra = Carbon::parse($firstRow->etd)->format('Y-m-d');
                    }
                }
                $adminUserId = Auth::id();

                // === Replace batch strategy ===
                // ลบ extra เดิมของ (customerno + etd) ทั้งหมด → insert ใหม่ทั้ง batch พร้อม sequence_no
                // เหตุผล: admin มัก re-upload PDF เพื่อแก้ราคา/รายการ → ต้องการให้ผลตรงเป๊ะ
                //          และกรณี multi-PDF ที่มีรายการซ้ำกัน (เช่น "1 กล่องเบอร์ C 15.00" 2 รอบ) ต้อง save แยก 100%
                $extraDeleteQuery = ExtraShippingCharge::where('customerno', $customerno);
                if ($etdForExtra) {
                    $extraDeleteQuery->whereDate('etd_date', $etdForExtra);
                } else {
                    $extraDeleteQuery->whereNull('etd_date');
                }
                $extraDeleteQuery->delete();
                $extraSeq = 0;

                foreach ($parsedItems as $pItem) {
                    // === Case 1: ไม่มี box → บันทึกเป็น "ค่าบริการเพิ่มเติม" (Repack, ค่าธรรมเนียม ฯลฯ) ===
                    if (empty($pItem['boxes'])) {
                        if ($pItem['totalPrice'] <= 0 && $pItem['refNo'] === '' && $pItem['recipientName'] === '') continue;
                        $extraSeq++;
                        ExtraShippingCharge::create([
                            'customerno'     => $customerno,
                            'etd_date'       => $etdForExtra,
                            'ref_no'         => $pItem['refNo'] !== '' ? $pItem['refNo'] : null,
                            'courier'        => $pItem['courier'] !== '' ? $pItem['courier'] : null,
                            'recipient_name' => $pItem['recipientName'] !== '' ? $pItem['recipientName'] : null,
                            'price'          => round($pItem['totalPrice'], 2),
                            'description'    => $pItem['destination'] !== '' ? $pItem['destination'] : null,
                            'sequence_no'    => $extraSeq,
                            'created_by'     => $adminUserId,
                        ]);
                        continue;
                    }

                    // === Case 2: มี box → update Customershipping per-box ตามเดิม ===
                    $perBox = [
                        'thai_tracking_no'    => $pItem['refNo'] !== '' ? $pItem['refNo'] : null,
                        'thai_courier'        => $pItem['courier'] !== '' ? $pItem['courier'] : null,
                        'thai_shipping_price' => $pItem['totalPrice'] > 0 ? round($pItem['totalPrice'], 2) : null,
                        'shippop_booked_at'   => $now,
                    ];
                    $perBox = array_filter($perBox, function($v){ return $v !== null; });
                    if (empty($perBox)) continue;

                    // รองรับ box_no ทั้ง int และ string (กรณีมี zero-pad/prefix) — match แบบยืดหยุ่น
                    $boxValues = [];
                    foreach ($pItem['boxes'] as $b) {
                        $b = (int) $b;
                        $boxValues[] = (string) $b;
                        $boxValues[] = str_pad((string) $b, 3, '0', STR_PAD_LEFT);
                    }
                    $boxValues = array_values(array_unique($boxValues));

                    $affected = Customershipping::where('customerno', $customerno)
                        ->where(function($q) use ($boxValues) {
                            $q->whereIn('box_no', $boxValues)
                              ->orWhereRaw('CAST(box_no AS UNSIGNED) IN (' . implode(',', array_fill(0, count($boxValues), '?')) . ')',
                                  array_map('intval', $boxValues));
                        })
                        ->where('excel_status', '1')
                        ->update($perBox);

                    // === Case 3: มี box แต่ "ไม่ match" กล่องของลูกค้ารายนี้เลย (เช่น Box.715 ที่ไม่ใช่ของลูกค้า) ===
                    // เดิม update ไม่โดนแถวไหน → ราคาตกหล่น ทำให้ยอดรวมไม่ตรงบิล
                    // แก้: บันทึกเป็น "ค่าบริการเพิ่มเติม" แทน (คงเลข Box ไว้ใน description เพื่อ trace)
                    // หมายเหตุ: Thai bill ส่งทีละ 1 ลูกค้าเท่านั้น (guard ด้านบน) → ไม่มี cross-customer
                    if ($affected === 0) {
                        $boxLabel = 'Box.' . implode('+', $pItem['boxes']);
                        $extraDesc = $pItem['destination'] !== ''
                            ? $boxLabel . ' (' . $pItem['destination'] . ')'
                            : $boxLabel;
                        $extraSeq++;
                        ExtraShippingCharge::create([
                            'customerno'     => $customerno,
                            'etd_date'       => $etdForExtra,
                            'ref_no'         => $pItem['refNo'] !== '' ? $pItem['refNo'] : null,
                            'courier'        => $pItem['courier'] !== '' ? $pItem['courier'] : null,
                            'recipient_name' => $pItem['recipientName'] !== '' ? $pItem['recipientName'] : null,
                            'price'          => round($pItem['totalPrice'], 2),
                            'description'    => $extraDesc,
                            'sequence_no'    => $extraSeq,
                            'created_by'     => $adminUserId,
                        ]);
                    }
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

        $allFailed = $results['success'] === 0 && $results['failed'] > 0;

        return response()->json([
            'success' => !$allFailed,
            'message' => "ส่งบิลค่าส่งไทยเสร็จสิ้น: สำเร็จ {$results['success']} ราย, ไม่สำเร็จ {$results['failed']} ราย",
            'results' => $results,
            'total_amount' => $totalAmount,
            'parse_warning' => $parseWarning,
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

        $chatBase   = rtrim((string) config('services.skjchat.base_url'), '/');
        $chatApiUrl = $chatBase . '/api/thai-bill-send';
        $chatApiKey = (string) config('services.skjchat.api_key');
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

    /**
     * Parse-Preview: รับ PDF/รูปจาก admin → คืนรายการ shipment (เลขอ้างอิง/Box/ราคา/courier)
     * ใช้สำหรับ Admin Review UI ก่อนกดส่งจริง
     */
    public function parsePreview(Request $request)
    {
        $request->validate([
            'invoice_files'   => 'required|array|min:1',
            'invoice_files.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $items = [];
        $totalAmount = 0;
        $warnings = [];
        $fileSummaries = [];

        foreach ($request->file('invoice_files') as $file) {
            $origName = $file->getClientOriginalName();
            $ext = strtolower($file->getClientOriginalExtension());
            $isPdf = $ext === 'pdf';

            if (!$isPdf) {
                $warnings[] = 'ไฟล์ ' . $origName . ' เป็นรูปภาพ — ระบบไม่สามารถ auto-parse ได้ กรุณากรอกข้อมูลด้วยตนเอง';
                $fileSummaries[] = ['file' => $origName, 'isPdf' => false, 'itemCount' => 0, 'sum' => 0];
                continue;
            }

            try {
                $parser = new \Smalot\PdfParser\Parser();
                $pdf = $parser->parseFile($file->getRealPath());
                $text = $pdf->getText();

                $parsed = $this->extractShippopItems($text);
                $fileSum = 0;
                foreach ($parsed as $p) {
                    $p['sourceFile'] = $origName;
                    $items[] = $p;
                    $fileSum += (float) ($p['totalPrice'] ?? 0);
                }
                $totalAmount += $fileSum;
                $fileSummaries[] = ['file' => $origName, 'isPdf' => true, 'itemCount' => count($parsed), 'sum' => $fileSum];

                if (count($parsed) === 0) {
                    $warnings[] = 'ไฟล์ ' . $origName . ' ไม่พบรายการ shipment ที่ parse ได้ — โปรดตรวจรูปแบบ PDF';
                }
            } catch (\Exception $e) {
                Log::warning('[THAI-BILL-PREVIEW] PDF parse failed', ['file' => $origName, 'error' => $e->getMessage()]);
                $warnings[] = 'ไฟล์ ' . $origName . ' parse ไม่สำเร็จ: ' . $e->getMessage();
                $fileSummaries[] = ['file' => $origName, 'isPdf' => true, 'itemCount' => 0, 'sum' => 0, 'error' => $e->getMessage()];
            }
        }

        return response()->json([
            'success'      => true,
            'items'        => $items,
            'totalAmount'  => round($totalAmount, 2),
            'fileSummaries'=> $fileSummaries,
            'warnings'     => $warnings,
        ]);
    }

    /**
     * Extract Shippop items จาก raw text (ของ PDF บิล Shippop)
     *
     * รูปแบบหนึ่ง item:
     *   1.) 1 DHL Express เมืองมหาสารคาม 44000
     *   เลขอ้างอิง 7227056829598426
     *   - ขนาดพัสดุ ( 23.00x32.00x3.00 ซม. )
     *   - น้ำหนัก 50 กรัม                       25.00
     *   - Additional Fuel                        3.00
     *   ผู้รับ: กอง 1 - Chanidapa Saengsit (Box.66 รวม 1 กล่อง)+E
     */
    protected function extractShippopItems(string $text): array
    {
        $items = [];
        $lines = preg_split('/\r\n|\r|\n/u', $text);
        if (!$lines) return $items;

        // โครงสร้าง PDF Shippop:
        //   - ขนาดพัสดุ ( ... )              ← marker เริ่ม item ใหม่
        //   - น้ำหนัก X กรัม      PRICE
        //   - Additional Fuel    PRICE
        //   X.) Y COURIER DESTINATION       ← header (อยู่ "หลัง" details ใน PDF text)
        //   เลขอ้างอิง XXXX
        //   ผู้รับ: NAME (Box.NNN รวม X กล่อง)
        //   - ขนาดพัสดุ ...                  ← next item
        //
        // Smalot/PdfParser บางทีแทรกช่องว่างใน คำไทย (เช่น "พั สดุ", "น้ำหนั ก") — ต้อง tolerant

        $sizeStartRe = '/^\s*-\s*ขนาด\s*พั?\s*สดุ/u';
        $itemHeaderRe = '/^\s*(\d+)\s*\.\s*\)\s+\d+\s+(.+?)$/u';

        $buckets = [];
        $current = null;
        foreach ($lines as $line) {
            $trim = trim($line);
            if (preg_match($sizeStartRe, $line)) {
                if ($current !== null) $buckets[] = $current;
                $current = ['lines' => [$trim]];
            } elseif ($current !== null) {
                $current['lines'][] = $trim;
            }
        }
        if ($current !== null) $buckets[] = $current;

        foreach ($buckets as $bucket) {
            // หา item header line ("X.) Y ...") ภายใน bucket
            $idx = null; $header = '';
            foreach ($bucket['lines'] as $ln) {
                if (preg_match($itemHeaderRe, $ln, $m)) {
                    $idx = (int) $m[1];
                    $header = trim($m[2]);
                    break;
                }
            }
            if ($idx === null) continue; // ไม่มี header → skip (อาจเป็น dummy block หรือบิลแถวก่อน X.)1)
            $bucket['idx'] = $idx;
            $bucket['header'] = $header;

            $item = $this->parseShippopBucket($bucket);
            if ($item !== null) {
                $items[] = $item;
            }
        }

        // === Pass 2: รายการพิเศษ "ค่ากล่อง" (เช่น "12.) 1 กล่องเบอร์ C   15.00") ===
        // ไม่มี - ขนาดพัสดุ จึงไม่ถูกจับใน Pass 1 → ต้อง scan แยก เป็น extra charge (ไม่ผูก box ลูกค้า)
        // วิธี: regex header กว้างๆ แล้ว normalize whitespace ก่อนเช็ค "กล่องเบอร์" (กัน Smalot ใส่ space แทรกในคำไทย)
        $existingIdxs = array_map(function($it){ return $it['idx'] ?? null; }, $items);
        // header: X.) Y <ข้อความ> [ราคา]?  (Y = qty/index ภายในรายการ)
        $itemLineRe = '/^\s*(\d+)\s*\.\s*\)\s*(\d+)\s+(.+?)(?:\s+(\d+(?:,\d{3})*\.\d{2}))?\s*$/u';
        $priceOnlyRe = '/^\s*(\d+(?:,\d{3})*\.\d{2})\s*$/u';
        $lineCount = count($lines);
        for ($i = 0; $i < $lineCount; $i++) {
            $trim = trim($lines[$i]);
            if (!preg_match($itemLineRe, $trim, $m)) continue;
            $idx = (int) $m[1];
            if (in_array($idx, $existingIdxs, true)) continue; // ถูก parse ใน Pass 1 แล้ว
            $qty = (int) $m[2];
            $descRaw = trim($m[3]);
            // Normalize: ลบ whitespace ทั้งหมดเพื่อเทียบคำไทย/อังกฤษ
            $descCompact = preg_replace('/\s+/u', '', $descRaw);
            $isBoxNo  = mb_stripos($descCompact, 'กล่องเบอร์') !== false;       // "กล่องเบอร์ C"
            $isBigBox = mb_stripos($descCompact, 'BigBox') !== false;            // "กล่อง Big Box"
            if (!$isBoxNo && !$isBigBox) continue; // ไม่ใช่รายการค่ากล่อง

            if ($isBigBox) {
                // ค่ากล่อง Big Box (ภาษาอังกฤษ ไม่มีเบอร์) → "1 กล่อง Big Box"
                $desc = trim($qty . ' กล่อง Big Box');
            } else {
                // หา label เบอร์กล่อง (token หลัง "กล่องเบอร์" ใน raw)
                $boxLabel = '';
                if (preg_match('/ก\s*ล่?\s*อ\s*ง\s*เ\s*บ\s*อ\s*ร์\s*(\S+)/u', $descRaw, $lm)) {
                    $boxLabel = trim($lm[1]);
                }
                $desc = trim($qty . ' กล่องเบอร์ ' . $boxLabel);
            }

            $price = isset($m[4]) && $m[4] !== '' ? (float) str_replace(',', '', $m[4]) : 0.0;
            // ราคาอาจอยู่บรรทัดถัดไป → look ahead 1-3 บรรทัด
            if ($price == 0.0) {
                for ($j = $i + 1; $j < min($i + 4, $lineCount); $j++) {
                    $next = trim($lines[$j]);
                    if ($next === '') continue;
                    if (preg_match($priceOnlyRe, $next, $pm)) {
                        $price = (float) str_replace(',', '', $pm[1]);
                        break;
                    }
                    break;
                }
            }

            $items[] = [
                'idx'           => $idx,
                'refNo'         => '',
                'courier'       => '',
                'destination'   => $desc, // ใช้เป็น description ตอน save extra
                'totalPrice'    => round($price, 2),
                'boxes'         => [],
                'recipient'     => '',
                'recipientName' => $desc, // โชว์ในช่อง "ผู้รับ" บน admin preview
                'pileNo'        => null,
                'rawText'       => $trim,
            ];
            $existingIdxs[] = $idx;
        }

        // เรียงตาม idx ให้ admin เห็นเรียงตามบิล
        usort($items, function($a, $b){
            return ($a['idx'] ?? 0) <=> ($b['idx'] ?? 0);
        });

        return $items;
    }

    /**
     * Parse 1 bucket (item) → schema { idx, refNo, courier, destination, totalPrice, boxes, recipient, rawText }
     */
    protected function parseShippopBucket(array $bucket): ?array
    {
        $rawText = implode("\n", $bucket['lines']);

        // Header: "DHL Express เมืองมหาสารคาม 44000" → courier="DHL Express", destination="เมืองมหาสารคาม 44000"
        $header = $bucket['header'];
        $courier = '';
        $destination = '';
        // จับ courier เป็น คำ ASCII ติดกันด้วย space (greedy ทุก word ที่เป็น ASCII)
        if (preg_match('/^([A-Za-z][A-Za-z0-9&\.\-]*(?:\s+[A-Za-z][A-Za-z0-9&\.\-]*)*)\s+(.+)$/u', $header, $hm)) {
            $courier = trim($hm[1]);
            $destination = trim($hm[2]);
        } else {
            $destination = $header;
        }

        $refNo = '';
        if (preg_match('/เลขอ้างอิง\s*[:：]?\s*([0-9A-Z\-]{8,30})/u', $rawText, $rm)) {
            $refNo = trim($rm[1]);
        }

        $boxes = [];
        // ต้องมี "." หลัง Box (รูปแบบจริง = "Box.96", "Box.834+613") เพื่อไม่ให้ไป match
        // คำว่า "Box" ใน "Big Box 110.00" (ค่ากล่อง Big Box ที่ไม่มีจุด) → กันเลขราคาหลุดมาเป็นเลขกล่อง
        if (preg_match_all('/(?<![A-Za-z])Box\.\s*(\d+)(?:\s*\+\s*\d+)*/u', $rawText, $bxAll)) {
            foreach ($bxAll[0] as $boxBlock) {
                if (preg_match_all('/(\d+)/u', $boxBlock, $nums)) {
                    foreach ($nums[1] as $n) {
                        $boxes[] = (int) $n;
                    }
                }
            }
            $boxes = array_values(array_unique($boxes));
        }

        // ผู้รับ: "กอง 1 - Chanidapa Saengsit (Box.66 รวม 1 กล่อง)"
        //   → pileNo=1, recipientName="Chanidapa Saengsit", recipient="กอง 1 - Chanidapa Saengsit"
        // หมายเหตุ: ผู้รับอาจถูก wrap หลายบรรทัด — รวมก่อนแล้วค่อย match
        // Early-stop เมื่อเจอ: (Box / รายการถัดไป "X.)" / ยอดรวม / ยอดสุทธิ / Additional Fuel Total / EOL
        $recipient = '';
        $recipientName = '';
        $pileNo = null;
        if (preg_match('/ผู้รับ\s*[:：]\s*([\s\S]+?)(?:\(\s*Box|\s\d+\s*\.\s*\)|ยอด\s*รวม|ยอด\s*สุท\s*ธิ|Additional\s*Fuel\s*Total|รับเงิน|ทอนเงิน|$)/u', $rawText, $recM)) {
            $recipient = preg_replace('/\s+/u', ' ', trim($recM[1]));
            if (preg_match('/^กอง\s*(\d+)\s*[-—–]\s*(.+)$/u', $recipient, $rp)) {
                $pileNo = (int) $rp[1];
                $recipientName = trim($rp[2]);
            } else {
                $recipientName = $recipient;
            }
        }

        // คำนวณราคา: หา keyword แล้วเอา ราคา (X.XX) ที่อยู่ใกล้ที่สุดหลัง keyword
        // ตัดส่วน parentheses ออกก่อน (เช่น ขนาดพัสดุ "( 20.00x35.00x4.00 ซม. )") ป้องกัน 20.00/35.00 ถูกนับ
        $priceText = preg_replace('/\([^)]*\)/u', ' ', $rawText);
        // ตัด "Box.NNN+NNN" ทุกตัวด้วย (กันชนเลขใน Box)
        $priceText = preg_replace('/Box\.?\s*\d+(?:\s*\+\s*\d+)*/u', ' ', $priceText);

        $totalPrice = 0.0;
        // pattern: keyword → ตัวเลขแบบทศนิยม 2 ตำแหน่งตัวแรกที่อยู่หลัง keyword
        // ใช้ `[\s\S]{0,80}?` แบบ lazy เพื่อข้ามคำ/บรรทัดคั่นได้แต่ไม่ไกลเกินไป
        // ใช้ ค่า `(\d+(?:,\d{3})*\.\d{2})` เพื่อจับเลขแบบ 25.00, 140.00, 8,000.00
        // Smalot/PdfParser บางทีแทรกช่องว่างในคำไทย — ใช้ `\s*` ระหว่างพยัญชนะ
        $patterns = [
            '/น้ำ\s*หนั\s*ก[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/u',
            '/Additional\s*Fuel[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/iu',
            '/\bCOD\b[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/iu',
            '/ประ\s*กั\s*น[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/u',
            '/\bInsurance\b[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/iu',
            '/Remote\s*Area[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/iu',
            // ค่าบริการพื้นที่พิเศษ (remote area ภาษาไทย) — Smalot แทรก space ในคำ เช่น "พื นที พิเศษ"
            '/พิ\s*เ\s*ศ\s*ษ[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/u',
            '/ภา\s*ษี[\s\S]{0,80}?(\d+(?:,\d{3})*\.\d{2})/u',
        ];
        foreach ($patterns as $rx) {
            if (preg_match_all($rx, $priceText, $pm)) {
                foreach ($pm[1] as $price) {
                    $totalPrice += (float) str_replace(',', '', $price);
                }
            }
        }

        if ($refNo === '' && empty($boxes) && $totalPrice == 0.0) {
            return null;
        }

        return [
            'idx'           => $bucket['idx'] ?? null,
            'refNo'         => $refNo,
            'courier'       => $courier,
            'destination'   => $destination,
            'totalPrice'    => round($totalPrice, 2),
            'boxes'         => $boxes,
            'recipient'     => $recipient,
            'recipientName' => $recipientName,
            'pileNo'        => $pileNo,
            'rawText'     => $rawText,
        ];
    }

}
