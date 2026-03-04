<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customershipping;
use Illuminate\Support\Facades\DB;
use App\Helpers\ChatNotify;

class QrScanController extends Controller
{
    /**
     * Generate เลขกล่องอัตโนมัติ ตามรอบปิดตู้ (ETD)
     * 1 กล่อง = 1 QR = 1 รายการสินค้า
     * Format: BOX-YYYYMMDD-001, BOX-YYYYMMDD-002, ...
     */
    public function generateBoxNumbers(Request $request)
    {
        $request->validate(['etd' => 'required|date']);

        $etd = $request->input('etd');
        $dateStr = date('Ymd', strtotime($etd));
        $prefix = 'BOX-' . $dateStr . '-';

        // ดึงรายการที่ยังไม่มี box_no ในรอบนี้
        $parcels = Customershipping::where('excel_status', '1')
            ->whereDate('etd', $etd)
            ->where(function ($q) {
                $q->whereNull('box_no')->orWhere('box_no', '');
            })
            ->orderBy('customerno')
            ->orderBy('id')
            ->get();

        if ($parcels->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่มีรายการที่ต้อง generate เลขกล่อง (อาจ generate ไปแล้ว)',
            ]);
        }

        // หา running number สูงสุดที่มีอยู่แล้วในรอบนี้
        $lastBox = Customershipping::where('excel_status', '1')
            ->where('box_no', 'like', $prefix . '%')
            ->orderByRaw("CAST(SUBSTRING(box_no, ?) AS UNSIGNED) DESC", [strlen($prefix) + 1])
            ->value('box_no');

        $lastNum = 0;
        if ($lastBox) {
            $lastNum = (int) substr($lastBox, strlen($prefix));
        }

        // Assign box_no ให้ทีละรายการ
        $count = 0;
        foreach ($parcels as $parcel) {
            $lastNum++;
            $parcel->box_no = $prefix . str_pad($lastNum, 3, '0', STR_PAD_LEFT);
            $parcel->save();
            $count++;
        }

        return response()->json([
            'success' => true,
            'message' => "Generate เลขกล่องสำเร็จ {$count} รายการ (รอบ {$etd})",
            'count' => $count,
            'first_box' => $prefix . str_pad($lastNum - $count + 1, 3, '0', STR_PAD_LEFT),
            'last_box' => $prefix . str_pad($lastNum, 3, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * ปริ้น QR Code ทั้งรอบปิดตู้ — 1 QR ต่อ 1 รายการ
     */
    public function printByEtd($etd)
    {
        $parcels = Customershipping::where('excel_status', '1')
            ->whereDate('etd', $etd)
            ->whereNotNull('box_no')
            ->where('box_no', '!=', '')
            ->orderBy('box_no')
            ->get();

        if ($parcels->isEmpty()) {
            return back()->with('error', 'ไม่พบรายการที่มีเลขกล่อง ในรอบ ' . $etd);
        }

        return view('qrscan.print', compact('parcels', 'etd'));
    }

    /**
     * ปริ้น QR Code เฉพาะ 1 กล่อง
     */
    public function printQr($box_no)
    {
        $parcel = Customershipping::where('box_no', $box_no)
            ->where('excel_status', '1')
            ->first();

        if (!$parcel) {
            return back()->with('error', 'ไม่พบกล่องหมายเลข ' . $box_no);
        }

        $parcels = collect([$parcel]);
        $etd = $parcel->etd ? $parcel->etd->format('Y-m-d') : '-';

        return view('qrscan.print', compact('parcels', 'etd'));
    }

    /**
     * หน้า Scanner มือถือ (โกดังไทย) — admin
     */
    public function scanner()
    {
        return view('qrscan.scanner');
    }

    /**
     * หน้า Scanner สำหรับ role scanner (login แยก)
     */
    public function scannerHome()
    {
        return view('scanner.home');
    }

    /**
     * หน้าผลสแกน — แสดงข้อมูลพัสดุ 1 รายการ (1 QR = 1 รายการ)
     */
    public function scanResult($box_no)
    {
        $parcel = $this->findParcelByBarcode($box_no, function ($q) {
            $q->orderBy('etd', 'desc')->orderBy('id', 'desc');
        });

        $statuses = DB::table('shipping_statuses')->get();

        return view('qrscan.result', compact('box_no', 'parcel', 'statuses'));
    }

    /**
     * API: อัพเดตสถานะพัสดุ (1 รายการ จาก box_no)
     */
    public function updateBoxStatus(Request $request)
    {
        $request->validate([
            'box_no' => 'required|string',
        ]);

        $box_no = $request->input('box_no');

        $parcel = $this->findParcelByBarcode($box_no);

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'message' => "ไม่พบพัสดุกล่อง {$box_no}",
            ]);
        }

        $parcel->scanned_at = now();
        $parcel->status = 3; // สินค้าถึงไทยแล้ว
        $parcel->save();

        // Sync สถานะไปที่ customerorder (shipping_status = 3)
        try {
            \App\Models\Customerorder::where('customerno', $parcel->customerno)
                ->where('itemno', $parcel->itemno)
                ->update(['shipping_status' => 3]);
        } catch (\Exception $e) {
            \Log::error('Scan sync customerorder error: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'status_name' => 'สินค้าถึงไทยแล้ว',
            'message' => "สแกนกล่อง {$box_no} สำเร็จ — สินค้าถึงไทยแล้ว",
        ]);
    }

    /**
     * API: ลบสถานะสแกน (clear scanned_at)
     */
    public function clearScan(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
        ]);

        $parcel = Customershipping::find($request->input('id'));

        if (!$parcel) {
            return response()->json(['success' => false, 'message' => 'ไม่พบพัสดุ']);
        }

        $parcel->scanned_at = null;
        $parcel->save();

        return response()->json(['success' => true, 'message' => 'ลบสถานะสแกนเรียบร้อย']);
    }

    /**
     * หน้าพิมพ์สติ๊กเกอร์บาร์โค้ด A4 (18 ดวง/แผ่น)
     */
    public function printLabels()
    {
        $counterFile = storage_path('app/label_counter.json');
        $counters = [];
        if (file_exists($counterFile)) {
            $counters = json_decode(file_get_contents($counterFile), true) ?: [];
        }

        // สร้าง list รอบจาก counters ที่เคยบันทึกไว้
        $rounds = collect($counters)
            ->filter(function ($v, $k) { return $k !== '_saved_at' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $k); })
            ->map(function ($nextStart, $etdKey) {
                $dt = \Carbon\Carbon::parse($etdKey);
                return [
                    'etd' => $etdKey,
                    'etd_display' => $dt->format('d/m/Y'),
                    'prefix' => $dt->format('dm'),
                    'next_start' => (int) $nextStart,
                ];
            })
            ->sortByDesc('etd')
            ->values();

        return view('qrscan.print-labels', compact('rounds'));
    }

    /**
     * บันทึกเลขกล่องล่าสุดที่ปริ้น
     */
    public function saveLabelsCounter(Request $request)
    {
        $nextStart = (int) $request->input('next_start', 1);
        $etd = $request->input('etd', '');
        $counterFile = storage_path('app/label_counter.json');
        $counters = [];
        if (file_exists($counterFile)) {
            $counters = json_decode(file_get_contents($counterFile), true) ?: [];
        }
        if ($etd) {
            $counters[$etd] = $nextStart;
        }
        $counters['_saved_at'] = now()->toDateTimeString();
        file_put_contents($counterFile, json_encode($counters));
        return response()->json(['success' => true, 'next_start' => $nextStart]);
    }

    /**
     * API: ดึงข้อมูลพัสดุจาก box_no (สำหรับ scanner)
     */
    public function getBoxInfo($box_no)
    {
        $parcel = $this->findParcelByBarcode($box_no);

        if (!$parcel) {
            return response()->json(['success' => false, 'message' => 'ไม่พบกล่อง ' . $box_no]);
        }

        return response()->json([
            'success' => true,
            'box_no' => $box_no,
            'parcel' => [
                'id' => $parcel->id,
                'customerno' => $parcel->customerno,
                'track_no' => $parcel->track_no,
                'status' => Customershipping::getShippingStatusNameById($parcel->status)->name,
                'status_id' => $parcel->status,
                'scanned_at' => $parcel->scanned_at ? $parcel->scanned_at->format('d/m/Y H:i') : null,
                'weight' => $parcel->weight,
                'note' => $parcel->note,
                'etd' => $parcel->etd ? $parcel->etd->format('d/m/Y') : '-',
            ],
        ]);
    }

    // ========== ระบบจ่ายของ (Pickup) ==========

    /**
     * Helper: แปลงค่าบาร์โค้ด DDMM-NNN → ['box_no' => N, 'day' => DD, 'month' => MM]
     * ถ้าไม่ตรง pattern จะ return null
     */
    private function parseBarcodeValue($barcode)
    {
        if (preg_match('/^(\d{2})(\d{2})-(\d+)$/', $barcode, $m)) {
            return [
                'box_no' => ltrim($m[3], '0') ?: '0',
                'day' => $m[1],
                'month' => $m[2],
            ];
        }
        return null;
    }

    /**
     * Helper: ค้นหาพัสดุจากบาร์โค้ด DDMM-NNN → หา box_no ในรอบ ETD ที่ตรงกัน (ปีล่าสุด)
     */
    private function findParcelByBarcode($barcode, $extraQuery = null)
    {
        // แปลง DDMM-NNN → หา box_no = N ในรอบ ETD ที่ day/month ตรงกัน (เอาปีล่าสุด)
        $parsed = $this->parseBarcodeValue($barcode);
        if (!$parsed) {
            return null;
        }

        $query = Customershipping::where('box_no', $parsed['box_no'])
            ->where('excel_status', '1')
            ->whereDay('etd', $parsed['day'])
            ->whereMonth('etd', $parsed['month'])
            ->orderBy('etd', 'desc');
        if ($extraQuery) $extraQuery($query);
        return $query->first();
    }

    /**
     * Helper: parse ETD dates from request (comma-separated)
     * Returns array of date strings, or null if none provided
     */
    private function parseEtdDates($etdParam)
    {
        if (!$etdParam) return null;
        return array_filter(array_map('trim', explode(',', $etdParam)));
    }

    /**
     * Helper: apply ETD filter to query (single or multiple dates)
     */
    private function applyEtdFilter($query, $etdDates)
    {
        if (count($etdDates) === 1) {
            $query->whereDate('etd', $etdDates[0]);
        } else {
            $query->where(function ($q) use ($etdDates) {
                foreach ($etdDates as $d) {
                    $q->orWhereDate('etd', $d);
                }
            });
        }
        return $query;
    }

    /**
     * หน้าจ่ายของ (scanner role)
     */
    public function pickupHome()
    {
        return view('scanner.pickup');
    }

    /**
     * API: ดึงรอบปิดตู้ที่มีอยู่ (สำหรับเลือกรอบ)
     */
    public function getAvailableRounds()
    {
        $rounds = Customershipping::where('excel_status', '1')
            ->whereNotNull('box_no')->where('box_no', '!=', '')
            ->select('etd')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN picked_up_at IS NOT NULL THEN 1 ELSE 0 END) as picked_up')
            ->selectRaw('SUM(CASE WHEN scanned_at IS NOT NULL THEN 1 ELSE 0 END) as scanned')
            ->groupBy('etd')
            ->orderBy('etd', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'success' => true,
            'rounds' => $rounds->map(function ($r) {
                return [
                    'etd' => $r->etd ? $r->etd->format('Y-m-d') : null,
                    'etd_display' => $r->etd ? $r->etd->format('d/m/Y') : '-',
                    'total' => $r->total,
                    'picked_up' => (int) $r->picked_up,
                    'scanned' => (int) $r->scanned,
                ];
            }),
        ]);
    }

    /**
     * API: ดึงรายการพัสดุทั้งหมดของลูกค้า (ตามรอบที่เลือก)
     */
    public function getCustomerParcels(Request $request, $customerno)
    {
        $etdDates = $this->parseEtdDates($request->query('etd'));

        if (!$etdDates) {
            return response()->json(['success' => false, 'message' => 'กรุณาเลือกรอบปิดตู้']);
        }

        $query = Customershipping::where('excel_status', '1')
            ->where('customerno', $customerno)
            ->whereNotNull('box_no')->where('box_no', '!=', '');
        $this->applyEtdFilter($query, $etdDates);
        $parcels = $query->orderBy('etd')->orderBy('box_no')->get();

        if ($parcels->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "ไม่พบพัสดุของลูกค้า {$customerno} ในรอบที่เลือก",
            ]);
        }

        $total = $parcels->count();
        $pickedUp = $parcels->whereNotNull('picked_up_at')->count();

        return response()->json([
            'success' => true,
            'customerno' => $customerno,
            'total' => $total,
            'picked_up' => $pickedUp,
            'parcels' => $parcels->map(function ($p) {
                return [
                    'id' => $p->id,
                    'box_no' => $p->box_no,
                    'track_no' => $p->track_no,
                    'weight' => $p->weight,
                    'etd' => $p->etd ? $p->etd->format('d/m/Y') : '-',
                    'picked_up_at' => $p->picked_up_at ? $p->picked_up_at->format('d/m/Y H:i') : null,
                    'scanned_at' => $p->scanned_at ? $p->scanned_at->format('d/m/Y H:i') : null,
                ];
            })->values(),
        ]);
    }

    /**
     * API: ยิงจ่ายของ — บันทึก picked_up_at + ป้องกันจ่ายผิดคน
     */
    public function pickupScan(Request $request)
    {
        $request->validate([
            'box_no' => 'required|string',
            'customerno' => 'required|string',
        ]);

        $boxNo = $request->input('box_no');
        $customerno = $request->input('customerno');
        $etdDates = $this->parseEtdDates($request->input('etd'));

        // ค้นหากล่องในรอบที่เลือก (รองรับบาร์โค้ด DDMM-NNN)
        $parcel = $this->findParcelByBarcode($boxNo, function ($q) use ($etdDates) {
            if ($etdDates) {
                $this->applyEtdFilter($q, $etdDates);
            }
        });

        if (!$parcel) {
            return response()->json([
                'success' => false,
                'type' => 'not_found',
                'message' => "ไม่พบกล่อง {$boxNo} ในรอบที่เลือก",
            ]);
        }

        // ป้องกันจ่ายผิดคน
        if ($parcel->customerno !== $customerno) {
            return response()->json([
                'success' => false,
                'type' => 'wrong_customer',
                'message' => "❌ กล่อง {$boxNo} เป็นของ {$parcel->customerno} ไม่ใช่ {$customerno}!",
                'actual_customer' => $parcel->customerno,
            ]);
        }

        // เช็คซ้ำ
        if ($parcel->picked_up_at) {
            return response()->json([
                'success' => true,
                'type' => 'duplicate',
                'message' => "กล่อง {$boxNo} จ่ายแล้วเมื่อ " . $parcel->picked_up_at->format('H:i'),
                'parcel' => [
                    'box_no' => $parcel->box_no,
                    'track_no' => $parcel->track_no,
                    'weight' => $parcel->weight,
                ],
            ]);
        }

        // บันทึกจ่ายของ + เปลี่ยนสถานะเป็น สำเร็จ (status 4)
        $parcel->picked_up_at = now();
        $parcel->picked_up_by = \Auth::user()->name ?? 'scanner';
        $parcel->status = 4;
        $parcel->save();

        // Sync สถานะไปที่ customerorder (shipping_status = 4)
        try {
            \App\Models\Customerorder::where('customerno', $parcel->customerno)
                ->where('itemno', $parcel->itemno)
                ->update(['shipping_status' => 4]);
        } catch (\Exception $e) {
            \Log::error('Pickup sync customerorder error: ' . $e->getMessage());
        }

        // นับ progress ใหม่ (เฉพาะรอบที่เลือก)
        $progQuery = Customershipping::where('excel_status', '1')
            ->where('customerno', $customerno)
            ->whereNotNull('box_no')->where('box_no', '!=', '');
        if ($etdDates) {
            $this->applyEtdFilter($progQuery, $etdDates);
        }
        $total = (clone $progQuery)->count();
        $pickedUp = (clone $progQuery)->whereNotNull('picked_up_at')->count();

        // แจ้งลูกค้าอัตโนมัติเมื่อจ่ายครบทุกชิ้น
        if ($pickedUp >= $total) {
            ChatNotify::notifyIfPickupComplete($parcel, $etdDates);
        }

        return response()->json([
            'success' => true,
            'type' => 'ok',
            'message' => "✅ จ่ายกล่อง {$boxNo} สำเร็จ",
            'parcel' => [
                'box_no' => $parcel->box_no,
                'track_no' => $parcel->track_no,
                'weight' => $parcel->weight,
            ],
            'progress' => [
                'picked_up' => $pickedUp,
                'total' => $total,
                'complete' => $pickedUp >= $total,
            ],
        ]);
    }

    /**
     * API: รายชื่อลูกค้าทั้งหมดในรอบที่เลือก (สำหรับ autocomplete)
     */
    public function getPickupCustomers(Request $request)
    {
        $etdDates = $this->parseEtdDates($request->query('etd'));

        if (!$etdDates) {
            return response()->json(['success' => false, 'customers' => []]);
        }

        $query = Customershipping::where('excel_status', '1')
            ->whereNotNull('box_no')->where('box_no', '!=', '');
        $this->applyEtdFilter($query, $etdDates);

        $customers = $query->select('customerno')
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN picked_up_at IS NOT NULL THEN 1 ELSE 0 END) as picked_up')
            ->groupBy('customerno')
            ->orderBy('customerno')
            ->get();

        return response()->json([
            'success' => true,
            'customers' => $customers->map(function ($c) {
                return [
                    'customerno' => $c->customerno,
                    'total' => $c->total,
                    'picked_up' => (int) $c->picked_up,
                    'complete' => (int) $c->picked_up >= $c->total,
                ];
            }),
        ]);
    }

    /**
     * หน้าประวัติสแกนพัสดุ (admin)
     */
    public function scanHistory()
    {
        return view('scan-history.index');
    }

    /**
     * API: ดึงข้อมูลประวัติสแกน (admin)
     */
    public function scanHistoryData(Request $request)
    {
        $query = Customershipping::whereNotNull('scanned_at')
            ->where('excel_status', '1');

        if ($request->filled('date')) {
            $query->whereDate('scanned_at', $request->date);
        }
        if ($request->filled('customer')) {
            $query->where('customerno', 'LIKE', '%' . $request->customer . '%');
        }

        $items = $query->orderBy('scanned_at', 'desc')->limit(2000)->get();

        $hasPickup = \Schema::hasColumn('customershippings', 'picked_up_at');

        $total = Customershipping::whereNotNull('scanned_at')->where('excel_status', '1')->count();
        $today = Customershipping::whereNotNull('scanned_at')->where('excel_status', '1')->whereDate('scanned_at', today())->count();
        $latestEtd = Customershipping::where('excel_status', '1')->whereNotNull('box_no')->where('box_no', '!=', '')->max('etd');

        return response()->json([
            'stats' => [
                'total' => $total,
                'today' => $today,
                'latest_etd' => $latestEtd ? \Carbon\Carbon::parse($latestEtd)->format('d/m/Y') : null,
            ],
            'items' => $items->map(function ($item) use ($hasPickup) {
                return [
                    'box_no' => $item->box_no,
                    'customerno' => $item->customerno,
                    'track_no' => $item->track_no,
                    'weight' => $item->weight,
                    'etd' => $item->etd ? $item->etd->format('d/m/Y') : '-',
                    'scanned_at' => $item->scanned_at->format('d/m/Y H:i'),
                    'picked_up' => $hasPickup ? ($item->picked_up_at !== null) : false,
                ];
            }),
        ]);
    }
}
