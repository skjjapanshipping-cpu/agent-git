<?php

namespace App\Http\Controllers;

use App\Models\Customershipping;
use App\Models\Customerorder;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Carbon\Carbon;
use App\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InvoiceController extends Controller
{
    public function generateInvoice($etd, $customerno, $shipping_ids = null)
    {
        // ถ้ามี shipping_ids ให้ใช้รายการที่เลือก
        if ($shipping_ids && $shipping_ids !== 'null') {
            $ids = explode(',', $shipping_ids);
            $shippings = Customershipping::whereIn('id', $ids)
                ->where('customerno', $customerno)
                ->orderBy('customerno', 'asc')
                ->orderBy('ship_date', 'desc')
                ->get();
        } else {
            // ค้นหา shipping ตาม etd และ customerno (แบบเดิม)
            $shippings = Customershipping::where('etd', $etd)
                ->where('customerno', $customerno)
                // ->where('pay_status', 1)
                ->orderBy('customerno', 'asc')
                ->orderBy('ship_date', 'desc')
                ->get();
        }

        if ($shippings->isEmpty()) {
            return response()->view('errors.404', ['message' => 'กรุณาตรวจสอบรหัสลูกค้าให้ถูกต้อง หรือไม่พบข้อมูลในระบบ'], 404);
        }
        $customer = User::where('customerno', $customerno)->first();
        // dd($customer->toArray());
        // อัพเดทสถานะการชำระเงินเป็น "รอโอน" ถ้ายังไม่ได้ชำระเงิน
        foreach ($shippings as $shipping) {
            if ($shipping->pay_status != 2) { // ถ้าไม่ใช่สถานะ "ชำระเงินแล้ว"
                $shipping->pay_status = 5; // เปลี่ยนเป็น "รอโอน"
                $shipping->save();
            }

        }

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dbhelvethaicax',
            // 'margin_left' => 0,    
            // 'margin_right' => 0,   
            'margin_top' => 5,     
            'margin_bottom' => 10,  
            'margin_header' => 0,  
            'margin_footer' => 5,   
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata' => array_merge($fontData, [
                'sarabun' => [
                    'R' => 'THSarabunNew/THSarabunNew.ttf',
                    'B' => 'THSarabunNew/THSarabunNew Bold.ttf',
                    'I' => 'THSarabunNew/THSarabunNew Italic.ttf',
                    'BI' => 'THSarabunNew/THSarabunNew BoldItalic.ttf'
                ],
                'dbhelvethaicax' => [
                    'R' => 'DBHELVETHAICAX-MED.TTF'
                ],
                'erasbolditc' => [
                    'R' => 'Eras Bold ITC.TTF'
                ]
            ]),
            'default_font_size' => 14,
        ]);

        // ตั้งค่าเลขหน้าก่อนเขียน HTML
        $mpdf->setFooter('{PAGENO} / {nb}');

        // เพิ่ม CSS สำหรับฟอนต์ไทย
        $mpdf->WriteHTML('
            <style>
                body, table, td, th, div, p, h1, h2, h3, h4, h5, h6 {
                    font-family: dbhelvethaicax;
                }
            </style>
        ');

        $html = view('invoices.invoice', [
            'shippings' => $shippings,
            'customer' => $customer,
            'etd' => Carbon::parse($etd)->format('d/m/Y'),
            'etd_Original' => $etd,
            'customerno' => $customerno
        ])->render();
        
        $mpdf->WriteHTML($html);

        $filename = 'invoice-shipping-' . strtoupper($customerno) . '-' . Carbon::parse($etd)->format('d-m-Y') . '.pdf';
        return $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    }

    public function generateOrderInvoice($order_date, $end_order_date, $status,$customerorderids,$customerno)
    {
        // เพิ่มเวลาในการประมวลผล PHP และหน่วยความจำ
        set_time_limit(600); // 10 นาที
        ini_set('memory_limit', '1G'); // เพิ่มหน่วยความจำเป็น 1GB
        ini_set('pcre.backtrack_limit', '10000000'); // เพิ่ม backtrack limit
        ini_set('max_execution_time', '600'); // 10 นาที
        
        // แยก ID ที่ส่งมาเป็น array
        $ids = explode(',', $customerorderids);
        
        // ใช้การดึงข้อมูลแบบปกติแทน chunking เพื่อความสม่ำเสมอ
        $customerorders = Customerorder::query()
            ->whereIn('id', $ids)
            ->orderBy('created_at', 'desc')
            ->get();
        
        // ดึงค่า min และ max order_date จาก customerorders collection
        $minOrderDate = $customerorders->min('order_date');
        $maxOrderDate = $customerorders->max('order_date');

        if ($customerorders->isEmpty()) {
            return response()->view('errors.404', ['message' => 'กรุณาตรวจสอบรหัสลูกค้าให้ถูกต้อง หรือไม่พบข้อมูลในระบบ'], 404);
        }

        $customer = User::where('customerno', $customerno)->first();

        // Batch update status แทนวน save() ทีละแถว (ลด N queries เหลือ 1)
        Customerorder::whereIn('id', $ids)
            ->where('status', '!=', 2)
            ->update(['status' => 5]);

        // Pre-cache image existence + displayLink
        $uploadPath = '/var/www/vhosts/skjjapanshipping.com/httpdocs/skjtrack/uploads/';
        foreach ($customerorders as $order) {
            $order->status = ($order->status == 2) ? 2 : 5; // sync in-memory
            $order->displayLink = $this->getNameFromDomain($order->link);
            // Pre-check image exists (cache result on model)
            $order->imageExists = !empty($order->image_link) && file_exists($uploadPath . $order->image_link);
            $order->imagePath = $order->imageExists ? ($uploadPath . $order->image_link) : null;
        }

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];

        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dbhelvethaicax',
            'margin_top' => 5,     
            'margin_bottom' => 10,  
            'margin_header' => 0,  
            'margin_footer' => 5,   
            'img_dpi' => 72,  // ลดจาก 96 เพื่อเร่งความเร็ว (รูป 35x35 ไม่ต้องละเอียดมาก)
            'tempDir' => storage_path('app/mpdf_temp'),  // cache directory
            'backtrack_limit' => 10000000, // เพิ่ม backtrack limit
            'max_execution_time' => 600, // เพิ่มเวลาการประมวลผล 10 นาที
            'memory_limit' => '1G', // เพิ่มหน่วยความจำเป็น 1GB (เดิม 512M)
            'cacheTables' => true, // เปิดใช้งาน cache สำหรับตาราง
            'enableImports' => false, // ปิดการนำเข้า
            'ignore_invalid_utf8' => true, // เพิ่มความทนทานกับ UTF-8 ที่ไม่ถูกต้อง
            'allow_html_optional_endtags' => true, // อนุญาต HTML tags ที่ไม่ปิด
            'use_kwt' => false, // ปิด Keep-With-Table
            'autoLangToFont' => true, // อัตโนมัติเลือกฟอนต์ตามภาษา
            'allow_output_buffering' => true, // อนุญาต output buffering
            'debug' => false, // ปิด debug เพื่อประสิทธิภาพ
            'showImageErrors' => false, // ปิดการแสดง error รูปภาพ
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts'),
            ]),
            'fontdata' => array_merge($fontData, [
                'sarabun' => [
                    'R' => 'THSarabunNew/THSarabunNew.ttf',
                    'B' => 'THSarabunNew/THSarabunNew Bold.ttf',
                    'I' => 'THSarabunNew/THSarabunNew Italic.ttf',
                    'BI' => 'THSarabunNew/THSarabunNew BoldItalic.ttf'
                ],
                'dbhelvethaicax' => [
                    'R' => 'DBHELVETHAICAX-MED.TTF'
                ],
                'erasbolditc' => [
                    'R' => 'Eras Bold ITC.TTF'
                ]
            ]),
            'default_font_size' => 14,
        ]);

        // ตั้งค่าเลขหน้าก่อนเขียน HTML
        $mpdf->setFooter('{PAGENO} / {nb}');

        // เพิ่ม CSS สำหรับฟอนต์ไทย
        $mpdf->WriteHTML('
            <style>
                body, table, td, th, div, p, h1, h2, h3, h4, h5, h6 {
                    font-family: dbhelvethaicax;
                }
                .border-top {
                    border-top: 2px solid black !important;
                }
                .border-bottom {
                    border-bottom: 2px solid black !important;
                }
            </style>
        ');

        $html = view('invoices.order-invoice', [
            'orders' => $customerorders,
            'customer' => $customer,
            'order_date' => Carbon::parse($minOrderDate)->format('d/m/Y'),
            'order_date_Original' => $minOrderDate,
            'end_order_date' => Carbon::parse($maxOrderDate)->format('d/m/Y'),
            'end_order_date_Original' => $maxOrderDate,
            'customerno' => $customerno,
        ])->render();
        
        $mpdf->WriteHTML($html);

        $filename = 'invoice-order-' . strtoupper($customerno) . '-' . Carbon::parse($order_date)->format('d-m-Y') . '.pdf';
        return $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    }


    /**
     * Save invoice PDF to public path (for chat sending)
     * Returns the public URL of the saved PDF
     */
    public function saveInvoicePdf($etd, $customerno, $shippingIds = null)
    {
        if (!empty($shippingIds)) {
            $shippings = Customershipping::whereIn('id', $shippingIds)
                ->orderBy('ship_date', 'desc')
                ->get();
        } else {
            $shippings = Customershipping::where('etd', $etd)
                ->where('customerno', $customerno)
                ->where('excel_status', '1')
                ->orderBy('ship_date', 'desc')
                ->get();
        }

        Log::info("[saveInvoicePdf] shippingIds=" . json_encode($shippingIds) . ", found=" . $shippings->count() . ", ids_found=" . json_encode($shippings->pluck('id')->toArray()));

        if ($shippings->isEmpty()) {
            return null;
        }

        $customer = User::where('customerno', $customerno)->first();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dbhelvethaicax',
            'margin_top' => 5,
            'margin_bottom' => 10,
            'margin_header' => 0,
            'margin_footer' => 5,
            'fontDir' => array_merge($fontDirs, [public_path('fonts')]),
            'fontdata' => array_merge($fontData, [
                'sarabun' => [
                    'R' => 'THSarabunNew/THSarabunNew.ttf',
                    'B' => 'THSarabunNew/THSarabunNew Bold.ttf',
                    'I' => 'THSarabunNew/THSarabunNew Italic.ttf',
                    'BI' => 'THSarabunNew/THSarabunNew BoldItalic.ttf'
                ],
                'dbhelvethaicax' => ['R' => 'DBHELVETHAICAX-MED.TTF'],
                'erasbolditc' => ['R' => 'Eras Bold ITC.TTF']
            ]),
            'default_font_size' => 14,
        ]);

        $mpdf->setFooter('{PAGENO} / {nb}');
        $mpdf->WriteHTML('<style>body,table,td,th,div,p,h1,h2,h3,h4,h5,h6{font-family:dbhelvethaicax;}</style>');

        $html = view('invoices.invoice', [
            'shippings' => $shippings,
            'customer' => $customer,
            'etd' => Carbon::parse($etd)->format('d/m/Y'),
            'etd_Original' => $etd,
            'customerno' => $customerno
        ])->render();

        $mpdf->WriteHTML($html);

        // Save to httpdocs/skjtrack/invoices/ (web-accessible path)
        $invoiceDir = base_path('../httpdocs/skjtrack/invoices');
        if (!is_dir($invoiceDir)) {
            mkdir($invoiceDir, 0777, true);
        }

        $filename = 'invoice-' . strtoupper($customerno) . '-' . Carbon::parse($etd)->format('d-m-Y') . '.pdf';
        $filepath = $invoiceDir . '/' . $filename;
        $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);
        @chmod($filepath, 0666);

        return '/skjtrack/invoices/' . $filename;
    }

    /**
     * Send invoice to customers via SKJ Chat
     */
    public function sendInvoiceChat(Request $request)
    {
        // Mode: check_only — just check chat connection status
        if ($request->input('check_only')) {
            $customerNos = $request->input('customer_nos', []);
            if (empty($customerNos)) {
                return response()->json(['success' => false, 'error' => 'No customers']);
            }
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-API-Key' => 'skjchat-invoice-2026',
                    'Content-Type' => 'application/json',
                ])->timeout(15)->post('https://chat.skjjapanshipping.com/api/invoice-check', [
                    'customer_nos' => $customerNos,
                ]);
                return response()->json($response->json(), $response->status());
            } catch (\Exception $e) {
                Log::error('checkChatConnection error: ' . $e->getMessage());
                return response()->json(['error' => $e->getMessage()], 500);
            }
        }

        $request->validate([
            'etd' => 'required|date',
            'customer_nos' => 'required|array|min:1',
            'message_template' => 'nullable|string',
            'qr_image_url' => 'nullable|string',
            'messenger_fee' => 'nullable|numeric|min:0',
        ]);

        $etdDate = $request->input('etd');
        $customerNos = $request->input('customer_nos');
        $shippingIdsMap = $request->input('shipping_ids_map', []);
        $messageTemplate = $request->input('message_template', '');
        $qrImageUrl = $request->input('qr_image_url', '');
        $messengerFee = (float) $request->input('messenger_fee', 0);

        $chatApiUrl = 'https://chat.skjjapanshipping.com/api/invoice-send';
        $chatApiKey = 'skjchat-invoice-2026';

        $results = ['success' => 0, 'failed' => 0, 'not_found' => 0, 'details' => []];

        foreach ($customerNos as $sendIndex => $customerno) {
            // Delay 1s between each customer to avoid LINE 429 rate limit
            if ($sendIndex > 0) {
                usleep(1000000);
            }
            // ใช้ shipping_ids ที่เลือกจาก frontend (ถ้ามี)
            $ids = isset($shippingIdsMap[$customerno]) ? $shippingIdsMap[$customerno] : [];
            if (!empty($ids)) {
                $shippings = Customershipping::whereIn('id', $ids)
                    ->where('customerno', $customerno)
                    ->where('excel_status', '1')
                    ->get();
            } else {
                $shippings = Customershipping::where('etd', $etdDate)
                    ->where('customerno', $customerno)
                    ->where('excel_status', '1')
                    ->get();
            }

            if ($shippings->isEmpty()) {
                $results['failed']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'no_data',
                    'message' => 'ไม่พบข้อมูลในรอบปิดตู้นี้',
                ];
                continue;
            }

            $itemCount = $shippings->count();
            $totalAmount = $shippings->sum(function ($s) {
                $codRate = $s->cod_rate ?? 0.25;
                return $s->import_cost + ($s->cod * $codRate);
            });

            // Save PDF to public path (ใช้เฉพาะรายการที่เลือก)
            $pdfUrl = null;
            try {
                $shippingIds = !empty($ids) ? $ids : null;
                Log::info("[INVOICE-PDF] customerno={$customerno}, ids_from_map=" . json_encode($ids) . ", shippingIds=" . json_encode($shippingIds) . ", shippings_count={$itemCount}");
                $pdfPath = $this->saveInvoicePdf($etdDate, $customerno, $shippingIds);
                $pdfUrl = $pdfPath ? ('https://skjjapanshipping.com' . $pdfPath . '?t=' . time()) : null;
            } catch (\Exception $e) {
                Log::error('saveInvoicePdf error for ' . $customerno . ': ' . $e->getMessage());
            }

            $etdFormatted = Carbon::parse($etdDate)->format('d/m/Y');

            // คำนวณยอดรวมทั้งหมด (ค่านำเข้า + ค่าแมสเซ็นเจอร์)
            $grandTotal = round($totalAmount + $messengerFee, 2);

            // สร้าง PromptPay QR แบบ dynamic (ฝังยอดเงินรวมค่าแมส)
            $dynamicQrUrl = \App\Services\PromptPayQrService::generateQrUrl($grandTotal, 'inv');

            // สร้าง Flex Message card สำหรับ LINE
            $flexMessages = $this->buildInvoiceFlexMessages(
                $customerno, $etdFormatted, $itemCount, round($totalAmount, 2),
                $pdfUrl, null, $messageTemplate ?: null, $messengerFee
            );

            // Call SKJ Chat API
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-API-Key' => $chatApiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(30)->post($chatApiUrl, [
                    'customerno' => $customerno,
                    'etd' => $etdFormatted,
                    'itemCount' => $itemCount,
                    'totalAmount' => $grandTotal,
                    'messengerFee' => $messengerFee,
                    'messageTemplate' => $messageTemplate ?: null,
                    'pdfUrl' => $pdfUrl,
                    'qrImageUrl' => $dynamicQrUrl,
                    'shippingIds' => $shippings->pluck('id')->values()->toArray(),
                    'flexMessages' => $flexMessages,
                ]);

                $data = $response->json();

                if ($response->successful() && ($data['success'] ?? false)) {
                    // อัพเดทสถานะเป็น "รอโอน" (pay_status=5) สำหรับรายการที่ยังไม่ได้ชำระ
                    foreach ($shippings as $shipping) {
                        if ($shipping->pay_status != 2) {
                            $shipping->pay_status = 5;
                            $shipping->save();
                        }
                    }

                    // เช็คว่ามี step ที่ fail ไหม (partial success)
                    $warning = $data['warning'] ?? null;
                    $failedSteps = $data['failedSteps'] ?? [];
                    if ($warning || !empty($failedSteps)) {
                        $failDetail = '';
                        foreach ($failedSteps as $fs) {
                            $stepName = $fs['step'] ?? '';
                            $stepErr = $fs['error'] ?? '';
                            if (str_contains($stepErr, '24') || str_contains($stepErr, 'window')) {
                                $failDetail .= ' [' . $stepName . ': FB เกิน 24 ชม.]';
                            } else {
                                $failDetail .= ' [' . $stepName . ': ' . mb_substr($stepErr, 0, 60) . ']';
                            }
                        }
                        $results['success']++;
                        $results['details'][] = [
                            'customerno' => $customerno,
                            'status' => 'partial',
                            'message' => '⚠️ ส่งได้บางส่วน → ' . ($data['contactName'] ?? '') . ' (' . ($data['platform'] ?? '') . ')' . $failDetail,
                        ];
                    } else {
                        $results['success']++;
                        $results['details'][] = [
                            'customerno' => $customerno,
                            'status' => 'success',
                            'message' => 'ส่งบิลสำเร็จ → ' . ($data['contactName'] ?? '') . ' (' . ($data['platform'] ?? '') . ')',
                        ];
                    }
                } elseif ($response->status() === 404) {
                    $results['not_found']++;
                    $results['details'][] = [
                        'customerno' => $customerno,
                        'status' => 'not_found',
                        'message' => $data['message'] ?? 'ไม่พบในระบบแชท',
                    ];
                } elseif ($response->successful() && !($data['success'] ?? true)) {
                    // Chat API returned 200 but success=false (all steps failed)
                    $errorMsg = $data['error'] ?? 'ส่งไม่สำเร็จ';
                    if (str_contains($errorMsg, '24') || str_contains($errorMsg, 'window')) {
                        $errorMsg = 'FB เกิน 24 ชม. ส่งข้อความไม่ได้';
                    }
                    $results['failed']++;
                    $results['details'][] = [
                        'customerno' => $customerno,
                        'status' => 'failed',
                        'message' => $errorMsg . ' → ' . ($data['contactName'] ?? '') . ' (' . ($data['platform'] ?? '') . ')',
                    ];
                } else {
                    $results['failed']++;
                    $results['details'][] = [
                        'customerno' => $customerno,
                        'status' => 'failed',
                        'message' => $data['error'] ?? 'ส่งไม่สำเร็จ',
                    ];
                }
            } catch (Exception $e) {
                Log::error('sendInvoiceChat error for ' . $customerno . ': ' . $e->getMessage());
                $results['failed']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'failed',
                    'message' => 'เชื่อมต่อ Chat API ไม่ได้: ' . $e->getMessage(),
                ];
            }
        }

        $totalSent = $results['success'];
        $totalFailed = $results['failed'];
        $totalNotFound = $results['not_found'];

        return response()->json([
            'success' => true,
            'message' => "ส่งบิลสำเร็จ {$totalSent} ราย, ไม่พบในแชท {$totalNotFound} ราย, ล้มเหลว {$totalFailed} ราย",
            'results' => $results,
        ]);
    }

    protected function getNameFromDomain($urlData) {
        $domainName = '';
        try {
            $host = parse_url($urlData, PHP_URL_HOST);
            if (!$host) {
                // ถ้า parse ไม่ได้ ให้ return url เดิม
                return $urlData;
            }
    
            // ตัด www. ออก
            $host = preg_replace('/^www\./', '', $host);
    
            $parts = explode('.', $host);
            $numParts = count($parts);
    
            // รายการ TLD/SLD ที่พบบ่อย
            $commonTLDs = ['jp', 'co', 'com', 'net', 'org', 'gov', 'edu', 'th'];
            $commonSLDs = ['co', 'ac', 'ne', 'or', 'com', 'net', 'org', 'edu', 'th'];
    
            if ($numParts > 2) {
                $tld = $parts[$numParts - 1];
                $sld = $parts[$numParts - 2];
                $secondLastPart = $parts[$numParts - 3];
    
                if (in_array($tld, $commonTLDs)) {
                    if (in_array($sld, $commonSLDs)) {
                        // เช่น example.co.jp, example.ac.th
                        $domainName = $secondLastPart;
                    } else {
                        // เช่น example.jp, example.com
                        $domainName = $sld;
                    }
                } else {
                    $domainName = $sld;
                }
            } elseif ($numParts === 2) {
                $domainName = $parts[0];
            } else {
                $domainName = $host;
            }
    
            // ตัวอักษรแรกเป็นตัวใหญ่
            $domainName = ucfirst($domainName);
    
        } catch (Exception $e) {
            $domainName = $urlData;
        }
        return $domainName;
    }

    /**
     * Check chat connection status for customer numbers
     */
    public function checkChatConnection(Request $request)
    {
        $request->validate([
            'customer_nos' => 'required|array|min:1',
        ]);

        $chatApiKey = 'skjchat-invoice-2026';
        $chatApiUrl = 'https://chat.skjjapanshipping.com/api/invoice-check';

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-Key' => $chatApiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post($chatApiUrl, [
                'customer_nos' => $request->input('customer_nos'),
            ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            Log::error('checkChatConnection error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send payment reminder to customers via SKJ Chat API
     */
    public function remindPayment(Request $request)
    {
        $request->validate([
            'customer_nos' => 'required|array|min:1',
        ]);

        $chatApiKey = 'skjchat-invoice-2026';
        $chatApiUrl = 'https://chat.skjjapanshipping.com/api/invoice-remind';
        $customerNos = $request->input('customer_nos');
        $results = [];

        foreach ($customerNos as $index => $customerno) {
            // Delay 500ms between each request to avoid LINE 429 rate limit
            if ($index > 0) {
                usleep(500000);
            }
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(15)->withHeaders([
                    'X-API-Key' => $chatApiKey,
                ])->post($chatApiUrl, [
                    'customerno' => $customerno,
                ]);

                $data = $response->json();
                if ($response->successful() && !empty($data['success'])) {
                    $results[] = [
                        'customerno' => $customerno,
                        'status' => 'success',
                        'message' => $data['message'] ?? 'ส่งเตือนเรียบร้อย',
                    ];
                } else {
                    $results[] = [
                        'customerno' => $customerno,
                        'status' => 'failed',
                        'message' => $data['message'] ?? ($data['error'] ?? 'ไม่สามารถส่งเตือนได้'),
                    ];
                }
            } catch (\Exception $e) {
                Log::error("remindPayment error for {$customerno}: " . $e->getMessage());
                $results[] = [
                    'customerno' => $customerno,
                    'status' => 'failed',
                    'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage(),
                ];
            }
        }

        $successCount = collect($results)->where('status', 'success')->count();
        return response()->json([
            'message' => "ส่งเตือนสำเร็จ {$successCount}/" . count($customerNos) . " ราย",
            'results' => $results,
        ]);
    }

    /**
     * สร้าง Flex Message card สำหรับค่านำเข้า (LINE)
     */
    protected function buildInvoiceFlexMessages(
        string $customerno, string $etdFormatted, int $itemCount, float $totalAmount,
        ?string $pdfUrl, ?string $qrImageUrl, ?string $messageTemplate, float $messengerFee = 0
    ): array {
        // คำนวณยอดรวมทั้งหมด (ค่านำเข้า + ค่าแมสเซ็นเจอร์)
        $grandTotal = round($totalAmount + $messengerFee, 2);

        // สร้าง PromptPay QR แบบ dynamic (ฝังยอดเงินรวมค่าแมส) — ไม่ใช้ qrImageUrl จาก frontend เพราะต้องการฝังยอดเงินทุกครั้ง
        $qrPaymentUrl = \App\Services\PromptPayQrService::generateQrUrl($grandTotal, 'inv');

        $bodyContents = [
            ['type' => 'text', 'text' => 'รหัสลูกค้า', 'size' => 'xs', 'color' => '#AAAAAA'],
            ['type' => 'text', 'text' => strtoupper($customerno), 'size' => 'lg', 'color' => '#333333', 'weight' => 'bold'],
            ['type' => 'separator', 'margin' => 'lg'],
            [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm',
                'contents' => [
                    [
                        'type' => 'box', 'layout' => 'horizontal',
                        'contents' => [
                            ['type' => 'text', 'text' => 'รอบปิดตู้', 'size' => 'sm', 'color' => '#AAAAAA', 'flex' => 0],
                            ['type' => 'text', 'text' => $etdFormatted, 'size' => 'sm', 'color' => '#333333', 'weight' => 'bold', 'align' => 'end'],
                        ],
                    ],
                    [
                        'type' => 'box', 'layout' => 'horizontal',
                        'contents' => [
                            ['type' => 'text', 'text' => 'จำนวน', 'size' => 'sm', 'color' => '#AAAAAA', 'flex' => 0],
                            ['type' => 'text', 'text' => $itemCount . ' ชิ้น', 'size' => 'sm', 'color' => '#333333', 'weight' => 'bold', 'align' => 'end'],
                        ],
                    ],
                ],
            ],
            ['type' => 'separator', 'margin' => 'lg'],
            [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'lg', 'spacing' => 'sm',
                'contents' => [
                    ['type' => 'text', 'text' => 'ค่านำเข้า', 'size' => 'sm', 'color' => '#555555'],
                    [
                        'type' => 'box', 'layout' => 'horizontal',
                        'contents' => [
                            ['type' => 'text', 'text' => '฿' . number_format($totalAmount, 2), 'size' => $messengerFee > 0 ? 'lg' : 'xxl', 'color' => '#E53935', 'weight' => 'bold', 'align' => 'center', 'margin' => 'md'],
                        ],
                    ],
                ],
            ],
        ];

        // ถ้ามีค่าแมสเซ็นเจอร์ แสดงแยกบรรทัดและสรุปยอดรวมทั้งหมด
        if ($messengerFee > 0) {
            $bodyContents[] = [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'sm', 'spacing' => 'sm',
                'contents' => [
                    ['type' => 'text', 'text' => 'ค่าแมสเซ็นเจอร์', 'size' => 'sm', 'color' => '#555555'],
                    [
                        'type' => 'box', 'layout' => 'horizontal',
                        'contents' => [
                            ['type' => 'text', 'text' => '฿' . number_format($messengerFee, 2), 'size' => 'lg', 'color' => '#E53935', 'weight' => 'bold', 'align' => 'center', 'margin' => 'md'],
                        ],
                    ],
                ],
            ];
            $bodyContents[] = ['type' => 'separator', 'margin' => 'md'];
            $bodyContents[] = [
                'type' => 'box', 'layout' => 'vertical', 'margin' => 'md', 'spacing' => 'sm',
                'contents' => [
                    ['type' => 'text', 'text' => 'ยอดรวมทั้งหมด', 'size' => 'sm', 'color' => '#555555', 'weight' => 'bold'],
                    ['type' => 'text', 'text' => '฿' . number_format($grandTotal, 2), 'size' => 'xxl', 'color' => '#E53935', 'weight' => 'bold', 'align' => 'center', 'margin' => 'md'],
                ],
            ];
        }

        $bodyContents[] = ['type' => 'separator', 'margin' => 'lg'];

        // QR Code + ปุ่มกดชำระเงิน (ใช้ยอดรวมทั้งหมดรวมค่าแมส)
        $paymentPageUrl = 'https://skjjapanshipping.com/skjtrack/pay.php?amount=' . number_format($grandTotal, 2, '.', '');
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
        $bodyContents[] = [
            'type' => 'box', 'layout' => 'vertical', 'margin' => 'md',
            'contents' => [
                ['type' => 'text', 'text' => 'กดปุ่มด้านล่างเพื่อดูรายละเอียดบิล', 'size' => 'xs', 'color' => '#AAAAAA', 'wrap' => true, 'align' => 'center'],
            ],
        ];

        $bubble = [
            'type' => 'bubble',
            'size' => 'mega',
            'header' => [
                'type' => 'box', 'layout' => 'horizontal', 'paddingAll' => '16px',
                'backgroundColor' => '#E53935',
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
                            ['type' => 'text', 'text' => 'ใบแจ้งค่านำเข้า', 'weight' => 'bold', 'size' => 'md', 'color' => '#FFFFFF'],
                            ['type' => 'text', 'text' => 'SKJ JAPAN SHIPPING', 'size' => 'xs', 'color' => '#FFE0E0'],
                        ],
                    ],
                ],
            ],
            'body' => [
                'type' => 'box', 'layout' => 'vertical', 'paddingAll' => '20px', 'spacing' => 'sm',
                'contents' => $bodyContents,
            ],
        ];

        // Footer with PDF button
        if ($pdfUrl) {
            $bubble['footer'] = [
                'type' => 'box', 'layout' => 'vertical', 'paddingAll' => '12px',
                'contents' => [
                    [
                        'type' => 'button',
                        'action' => ['type' => 'uri', 'label' => 'เปิดดูใบแจ้งหนี้', 'uri' => $pdfUrl],
                        'style' => 'primary', 'color' => '#E53935', 'height' => 'md',
                    ],
                ],
            ];
        }

        $messages = [];

        // ถ้ามี messageTemplate ส่ง text ก่อน
        if ($messageTemplate) {
            $formattedTotal = number_format($totalAmount, 2);
            $formattedMessengerFee = number_format($messengerFee, 2);
            $formattedGrandTotal = number_format($grandTotal, 2);
            $text = str_replace(
                ['{{จำนวน}}', '{{รวม}}', '{{ค่าแมส}}', '{{ยอดรวมทั้งหมด}}'],
                [$itemCount, $formattedTotal, $formattedMessengerFee, $formattedGrandTotal],
                $messageTemplate
            );
            $messages[] = ['type' => 'text', 'text' => $text];
        }

        $altText = 'ใบแจ้งค่านำเข้า - ' . strtoupper($customerno) . ' รอบปิดตู้ ' . $etdFormatted . ' ยอดรวม ฿' . number_format($grandTotal, 2);
        $messages[] = [
            'type' => 'flex',
            'altText' => $altText,
            'contents' => $bubble,
        ];

        return $messages;
    }
} 