<?php

namespace App\Http\Controllers;

use App\Exports\CustomershippigviewHtmlExport;
use App\Models\Customershipping;
use App\Models\Customerorder;
use App\Models\DeliveryType;
use App\Models\ExtraShippingCharge;
use App\Models\ShippingStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;
use App\Services\PromptPayQrService;


class CustomerShippingViewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if ($user->hasRole('admin') && empty($user->customerno)) {
            return redirect('/home');
        }

        // ห้าม cache หน้านี้ (กัน browser/bfcache ค้างเวอร์ชันเก่า → รอบปิดตู้ล่าสุดไม่ขึ้น)
        return response()
            ->view('customershippingview.index')
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function clearSession()
    {
        session()->forget('startdate');

        return redirect()->route('shippingview.index');
    }

    public function update_StatusByIDs(Request $request){
        $authUser = Auth::user();
        $rawIds = explode(',', (string) $request->input('track_ids'));
        $Ids = array_filter(array_map('intval', $rawIds));

        if (empty($Ids)) {
            return redirect()->route('shippingview.index')
                ->with('error', 'ไม่พบรายการที่เลือก');
        }

        try {
            // เฉพาะรายการของ customer คนนี้เท่านั้น (ป้องกัน IDOR)
            $ownIds = Customershipping::whereIn('id', $Ids)
                ->where('customerno', $authUser->customerno)
                ->where('excel_status', 1)
                ->pluck('id')
                ->toArray();

            if (empty($ownIds)) {
                return redirect()->route('shippingview.index')
                    ->with('error', 'ไม่พบรายการที่เลือก');
            }

            Customershipping::whereIn('id', $ownIds)->update([
                'delivery_type_id'    => 2,
                'delivery_fullname'   => $authUser->name,
                'delivery_mobile'     => $authUser->mobile,
                'delivery_address'    => $authUser->addr,
                'delivery_subdistrict'=> $authUser->subdistrinct,
                'delivery_district'   => $authUser->distrinct,
                'delivery_province'   => $authUser->province,
                'delivery_postcode'   => $authUser->postcode,
            ]);

            return redirect()->route('shippingview.index')
                ->with('success', 'บันทึกข้อมูลที่อยู่จัดส่งสินค้าสำเร็จ');
        } catch (\Exception $e) {
            \Log::error('update_StatusByIDs error: ' . $e->getMessage());
            return redirect()->route('shippingview.index')
                ->with('error', 'บันทึกข้อมูลที่อยู่จัดส่งสินค้า ไม่สำเร็จ');
        }
    }

    public function edit($id)
    {
        $authUser = Auth::user();
        if ($authUser->hasRole('admin') && empty($authUser->customerno)) {
            return redirect('/home');
        }
        $customershipping = Customershipping::find($id);

        // ป้องกัน IDOR: ลูกค้าต้องเป็นเจ้าของรายการ (admin ดูได้ทั้งหมด)
        if (!$customershipping) {
            abort(404);
        }
        // เปรียบเทียบ customerno แบบ case-insensitive (DB เก็บ ANW-xxxx ส่วน users เก็บ anw-xxxx)
        if (!$authUser->hasRole('admin') && strcasecmp((string) $customershipping->customerno, (string) $authUser->customerno) !== 0) {
            abort(403);
        }

        return view('customershippingview.edit', compact('customershipping','authUser'));
    }


    public function update(Request $request, Customershipping $customershipping)
    {
        $authUser = Auth::user();

        // ป้องกัน IDOR (case-insensitive)
        if (!$authUser->hasRole('admin') && strcasecmp((string) $customershipping->customerno, (string) $authUser->customerno) !== 0) {
            abort(403);
        }

        // Allowlist: อนุญาตเฉพาะฟิลด์ที่เกี่ยวกับที่อยู่จัดส่ง — ป้องกัน mass assignment
        $allowed = $request->only([
            'delivery_type_id',
            'delivery_fullname',
            'delivery_mobile',
            'delivery_address',
            'delivery_subdistrict',
            'delivery_district',
            'delivery_province',
            'delivery_postcode',
            'note',
        ]);

        if (isset($allowed['delivery_mobile'])) {
            $allowed['delivery_mobile'] = str_replace([' ', '-'], '', (string) $allowed['delivery_mobile']);
        }

        $customershipping->update($allowed);

        return redirect()->route('shippingview.index')
            ->with('success', 'บันทึกข้อมูลที่อยู่จัดส่งสินค้าสำเร็จ');
    }

    /***
     * frontend
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function fetchCustomershippingsview(Request $request)
    {


        if ($request->ajax()) {

            $authUser = Auth::user();

            $sqlQuery='';
            $queryAll = Customershipping::latest('ship_date')->where('excel_status','=','1')
                ->where('customerno',$authUser->customerno);

            // Filter by recipient name (ผู้รับ)
            if (!empty($request->recipient_filter)) {
                if ($request->recipient_filter === '__empty__') {
                    $queryAll->where(function($q) {
                        $q->whereNull('delivery_fullname')->orWhere('delivery_fullname', '');
                    });
                } else {
                    $queryAll->where('delivery_fullname', $request->recipient_filter);
                }
            }
//            dd($request->start_date);
            if(!empty($request->start_date)) {
                session(['startdate' => $request->start_date]);
//                dd($request->start_date);
//                dd($request->start_date);
            }
            else
                session()->forget('startdate');

            if (!empty($request->search) || !empty($request->status) || !empty($request->start_date)|| (!empty($request->start_date) && !empty($request->end_date))) {
//
                if (strtolower($request->search) == strtolower('all')) {

                    $data = $queryAll->get();
                } else {
                    $data = $queryAll->where(function ($query) use ($request) {
                        if (!empty($request->search)) {
                            $searchTerm = '%'.$request->search.'%';
                            $searchNoHyphens = '%'.str_replace('-', '', $request->search).'%';
                            $query->whereRaw("customerno like ?", [$searchTerm])
                                ->orWhereRaw("box_no like ?", [$searchTerm])
                                ->orWhereRaw("DATE_FORMAT(ship_date, '%d/%m/%Y') like ?", [$searchTerm])
                                ->orWhereRaw("REPLACE(track_no, '-', '') LIKE ?", [$searchNoHyphens]);

                        }
                        if (!empty($request->status))
                            $query->where('status', '=', $request->status);

                        // ใช้ช่วงเวลาแบบ SARGable (etd >= 00:00 และ <= 23:59) เพื่อให้ใช้ index idx_cs_customerno_etd ได้
                        // (เดิมใช้ DATE(etd) ทำให้ index ใช้ไม่ได้ → ช้ากับลูกค้าที่มีรายการเยอะ)
                        if (!empty($request->start_date) && !empty($request->end_date))
                            $query->where('etd', '>=', $request->start_date.' 00:00:00')
                                  ->where('etd', '<=', $request->end_date.' 23:59:59');
                        else if (!empty($request->start_date))
                            $query->where('etd', '>=', $request->start_date.' 00:00:00')
                                  ->where('etd', '<=', $request->start_date.' 23:59:59');

                    })->take(1000)->get();


                    $sqlQuery = $queryAll->toSql();
//                    dd($sqlQuery);
                }
            } else {
                $data = $queryAll->orderByRaw('customerno asc')->take(1000)->get(); // โหลดเพียง 20 รายการเมื่อครั้งแรก

            }
            $sqlQuery = $queryAll->toSql();

            foreach ($data as $customershipping) {
                $statusInfo = Customershipping::getShippingStatusNameById($customershipping->status);
                $customershipping->status_id = $customershipping->status; // เก็บ status_id เดิมไว้
                $customershipping->status = $statusInfo->name; // แปลงเป็น name
            }

            // คำนวณ COD Total โดยใช้ cod_rate ของแต่ละ record (ข้อมูลเก่าใช้ rate เดิม)
            $codTotal = $data->sum(function($item) {
                return $item->cod * ($item->cod_rate ?? 0.25);
            });
            $weightTotal = $data->sum('weight');
            $import_costTotal = $data->sum('import_cost');
            $priceTotal = $import_costTotal+$codTotal;
            $totalRecords= count($data);
            $startDate= !empty($request->start_date)?Carbon::parse($request->start_date)->format('d/m/Y'):'';
            $startDateRaw = $request->start_date;

            // ตรวจว่ารอบนี้เป็น "ทางเครื่องบิน" หรือไม่ (METHOD_AIR=2) เพื่อสลับ label เป็น "รอบเครื่องบิน"
            $airCount = $data->filter(function($r){ return (int) $r->shipping_method === Customershipping::METHOD_AIR; })->count();
            $seaCount = $totalRecords - $airCount;
            $roundIsAir = $airCount > 0 && $airCount >= $seaCount;

            // สร้าง summary บิลค่าส่งไทย (group by thai_tracking_no — 1 ref = 1 shipment)
            // และสร้าง map สำหรับ dedup ค่าส่ง/ref ในตาราง (กล่องหลัก = box_no น้อยที่สุดในกลุ่ม)
            $thaiShipments = [];
            $thaiShippingTotal = 0.0;
            $thaiBoxCount = 0;
            $thaiShipmentMap = []; // tracking_no => ['main_box'=>X, 'boxes'=>[...]]
            // แสดง "ค่าส่งในไทย" เฉพาะกล่องที่ "สินค้าถึงไทยแล้ว" (status >= 3) ขึ้นไปเท่านั้น
            // กันเคสที่ admin จอง Shippop ล่วงหน้าตั้งแต่ยัง "อยู่ระหว่างขนส่ง" (status 2) → ลูกค้าเห็นบิลทั้งที่ยังไม่ถึงไทย
            // หมายเหตุ: ด้านบน $r->status ถูกแปลงเป็นชื่อแล้ว เลข status เดิมอยู่ที่ $r->status_id
            $rowsWithRef = $data->filter(function($r){
                $statusId = (int) ($r->status_id ?? $r->status);
                return !empty($r->thai_tracking_no) && $statusId >= 3;
            });
            $grouped = $rowsWithRef->groupBy('thai_tracking_no');
            foreach ($grouped as $refNo => $rows) {
                $first = $rows->first();
                $boxes = $rows->pluck('box_no')->filter()->unique()->values()->all();
                // เรียง box_no จากน้อย→มาก (cast เป็น int เพื่อให้ "066" < "100")
                usort($boxes, function($a, $b){ return ((int)$a) <=> ((int)$b); });
                $mainBox = $boxes[0] ?? null;
                $price = (float) ($first->thai_shipping_price ?? 0);
                $thaiShippingTotal += $price;
                $thaiBoxCount += count($boxes);
                // ชื่อผู้รับ: ดึงจาก delivery_fullname ที่ไม่ว่างเปล่าใน group (1 shipment = 1 ผู้รับ)
                $recipientName = $rows->pluck('delivery_fullname')
                    ->filter(function($n){ return !empty(trim((string) $n)); })
                    ->first();
                $thaiShipments[] = [
                    'refNo'          => $refNo,
                    'courier'        => $first->thai_courier,
                    'recipient_name' => $recipientName,
                    'price'          => $price,
                    'boxes'          => $boxes,
                    'main_box'       => $mainBox,
                    'billed_at'      => $first->thai_billed_at ? Carbon::parse($first->thai_billed_at)->format('d/m/Y') : null,
                ];
                $thaiShipmentMap[(string) $refNo] = [
                    'main_box' => $mainBox,
                    'boxes'    => $boxes,
                    'courier'  => $first->thai_courier,
                    'price'    => $price,
                ];
            }
            // ดึง "ค่าบริการเพิ่มเติม" (Repack, ค่าธรรมเนียม ฯลฯ) ที่ผูกกับ customer + etd ปัจจุบัน
            $extraCharges = [];
            $extraTotal = 0.0;
            $etdFilter = $request->start_date ?? null;
            if (!empty($authUser->customerno)) {
                $extraQuery = ExtraShippingCharge::where('customerno', $authUser->customerno);
                if ($etdFilter) {
                    $extraQuery->where('etd_date', $etdFilter);
                }
                $extraRows = $extraQuery->orderBy('sequence_no', 'asc')->orderBy('created_at', 'asc')->orderBy('id', 'asc')->get();
                foreach ($extraRows as $ec) {
                    $price = (float) ($ec->price ?? 0);
                    $extraTotal += $price;
                    $extraCharges[] = [
                        'id'             => $ec->id,
                        'refNo'          => $ec->ref_no,
                        'courier'        => $ec->courier,
                        'recipient_name' => $ec->recipient_name,
                        'description'    => $ec->description,
                        'price'          => $price,
                        'etd_date'       => $ec->etd_date ? Carbon::parse($ec->etd_date)->format('d/m/Y') : null,
                    ];
                }
            }

            $thaiShippingSummary = [
                'shipment_count' => count($thaiShipments),
                'box_count'      => $thaiBoxCount,
                'total_price'    => round($thaiShippingTotal, 2),
                'shipments'      => $thaiShipments,
                'extra_charges'  => $extraCharges,
                'extra_count'    => count($extraCharges),
                'extra_total'    => round($extraTotal, 2),
                'grand_total'    => round($thaiShippingTotal + $extraTotal, 2),
            ];
//            dd($data);
            return Datatables::of($data)

                ->addColumn('delivery_type_name', function($row) {
                    return DeliveryType::getNameById($row->delivery_type_id);
                })
                ->addColumn('shipping_method_label', function($row) {
                    return Customershipping::getShippingMethodLabel($row->shipping_method ?? 1);
                })
                ->addColumn('edit_url', function($row) {
                    return route('customershippingview.edit', $row->id);
                })
                ->addColumn('thai_is_main', function($row) use ($thaiShipmentMap) {
                    if (empty($row->thai_tracking_no)) return false;
                    $info = $thaiShipmentMap[(string) $row->thai_tracking_no] ?? null;
                    if (!$info) return true; // มี ref แต่หา map ไม่เจอ → fail-safe ให้แสดงเต็ม
                    return (string) $row->box_no === (string) $info['main_box'];
                })
                ->addColumn('thai_shipment_main_box', function($row) use ($thaiShipmentMap) {
                    if (empty($row->thai_tracking_no)) return null;
                    return $thaiShipmentMap[(string) $row->thai_tracking_no]['main_box'] ?? null;
                })
                ->addColumn('thai_shipment_boxes', function($row) use ($thaiShipmentMap) {
                    if (empty($row->thai_tracking_no)) return [];
                    return $thaiShipmentMap[(string) $row->thai_tracking_no]['boxes'] ?? [];
                })
                ->with(['cod_total' => number_format($codTotal, 2, '.', ',')
                    ,'weight_total'=>number_format($weightTotal, 2, '.', ',')
                    ,'import_cost_total'=>number_format($import_costTotal, 2, '.', ',')
                    ,'price_total'=>number_format($priceTotal, 2, '.', ',')
                    ,'total_records'=>$totalRecords
                    ,'start_date'=>$startDate
                    ,'data_export_link'=>url('customershippingsviewexport2',['customerno'=>!empty($request->customerno)?$request->customerno:'','start_date'=>$startDateRaw]) . (!empty($request->recipient_filter) ? '?recipient_filter=' . urlencode($request->recipient_filter) : '')
                    ,'thai_shipping_summary'=>$thaiShippingSummary
                    ,'round_is_air'=>$roundIsAir

                    ,'query'=>config('app.debug') ? $sqlQuery : null
                ])// แสดงผลรวมของค่า COD])
                ->make(true);


        }
    }

    public function export2(Request $request)
    {
//        return view('customershipping.export',[
//            'customershippings'=>Customershipping::all()
//        ]);
        return Excel::download(new CustomershippigviewHtmlExport($request->start_date,$request->customerno,$request->recipient_filter), 'shipping_data_' . time() . '.xlsx');
    }

    public function downloadBoxImages($customerno, $start_date)
    {
        set_time_limit(300);

        $user = Auth::user();
        if (!$user->hasRole('admin') && strcasecmp((string) $user->customerno, (string) $customerno) !== 0) {
            abort(403);
        }

        $etdDate = Carbon::parse($start_date);
        $etdLabel = $etdDate->format('d.m.Y');

        $items = Customershipping::where('excel_status', '1')
            ->where('customerno', $customerno)
            ->whereRaw("DATE(etd) = ?", [$start_date])
            ->whereNotNull('box_image')
            ->where('box_image', '!=', '')
            ->where('box_image', '!=', '-')
            ->select('box_no', 'box_image')
            ->distinct('box_no')
            ->get()
            ->unique('box_no');

        if ($items->isEmpty()) {
            return back()->with('error', 'ไม่พบรูปหน้ากล่องในรอบปิดตู้นี้');
        }

        // Collect all download jobs: [ [url, fileName], ... ]
        $jobs = [];
        foreach ($items as $item) {
            $raw = trim($item->box_image);
            $urls = [];
            if (str_starts_with($raw, '[')) {
                try { $urls = json_decode($raw, true) ?: []; } catch (\Exception $e) { $urls = [$raw]; }
            } elseif (str_contains($raw, ',')) {
                $urls = array_filter(array_map('trim', explode(',', $raw)));
            } else {
                $urls = [$raw];
            }

            foreach ($urls as $idx => $url) {
                $url = trim($url);
                if (empty($url) || $url === '-') continue;
                $suffix = count($urls) > 1 ? '_' . ($idx + 1) : '';
                $fileName = "Box.{$item->box_no} รอบปิดตู้ {$etdLabel}{$suffix}.jpg";
                $jobs[] = [$url, $fileName];
            }
        }

        if (empty($jobs)) {
            return back()->with('error', 'ไม่พบรูปหน้ากล่องในรอบปิดตู้นี้');
        }

        $tempFile = tempnam(sys_get_temp_dir(), 'boximg_') . '.zip';
        $zip = new \ZipArchive();
        if ($zip->open($tempFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Cannot create ZIP');
        }

        // Download in parallel batches of 10 using curl_multi
        $batchSize = 10;
        $added = 0;

        foreach (array_chunk($jobs, $batchSize) as $batch) {
            $mh = curl_multi_init();
            $handles = [];

            foreach ($batch as $i => [$url, $fileName]) {
                // กรอง URL: ต้องเป็น http/https ที่ resolve เป็น public host เท่านั้น (กัน SSRF)
                $parsed = parse_url($url);
                if (!$parsed || !in_array(strtolower($parsed['scheme'] ?? ''), ['http','https'], true)) {
                    continue;
                }
                $host = strtolower($parsed['host'] ?? '');
                if ($host === '' || in_array($host, ['localhost','127.0.0.1','0.0.0.0','::1'], true) || str_starts_with($host, '169.254.')) {
                    continue;
                }

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_MAXREDIRS      => 5,
                    CURLOPT_TIMEOUT        => 20,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_SSL_VERIFYHOST => 2,
                    CURLOPT_USERAGENT      => 'Mozilla/5.0',
                    CURLOPT_PROTOCOLS      => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                    CURLOPT_REDIR_PROTOCOLS=> CURLPROTO_HTTP | CURLPROTO_HTTPS,
                ]);
                curl_multi_add_handle($mh, $ch);
                $handles[$i] = ['ch' => $ch, 'fileName' => $fileName];
            }

            $running = null;
            do {
                curl_multi_exec($mh, $running);
                curl_multi_select($mh, 1);
            } while ($running > 0);

            foreach ($handles as $h) {
                $httpCode = curl_getinfo($h['ch'], CURLINFO_HTTP_CODE);
                $imgData = curl_multi_getcontent($h['ch']);
                curl_multi_remove_handle($mh, $h['ch']);
                curl_close($h['ch']);

                if ($httpCode === 200 && $imgData && strlen($imgData) > 100) {
                    $zip->addFromString($h['fileName'], $imgData);
                    $added++;
                }
            }

            curl_multi_close($mh);
        }

        $zip->close();

        if ($added === 0) {
            @unlink($tempFile);
            return back()->with('error', 'ไม่สามารถดาวน์โหลดรูปหน้ากล่องได้');
        }

        $zipName = strtoupper($customerno) . " รอบปิดตู้ {$etdLabel}.zip";
        return response()->download($tempFile, $zipName)->deleteFileAfterSend(true);
    }

    public function analytics()
    {
        $user = Auth::user();

        // Admin ไม่มี customerno → redirect กลับ admin dashboard
        if ($user->hasRole('admin') && empty($user->customerno)) {
            return redirect('/home');
        }

        $customerno = $user->customerno;
        $threeMonthsAgo = Carbon::now()->subMonths(2)->startOfMonth()->format('Y-m-d');

        // ข้อมูลพัสดุ 3 เดือนย้อนหลัง + 1 เดือนข้างหน้า (ใช้ etd ให้ตรงกับ My Shipping)
        $oneMonthAhead = Carbon::now()->addMonth()->endOfMonth()->format('Y-m-d');
        $shippings = Customershipping::where('customerno', $customerno)
            ->where('excel_status', 1)
            ->where('etd', '>=', $threeMonthsAgo)
            ->where('etd', '<=', $oneMonthAhead)
            ->get();

        // Summary cards
        $totalShipments = $shippings->count();
        $statusCounts = $shippings->groupBy('status')->map->count();

        // จำนวนพัสดุรายเดือน (2 เดือนย้อนหลัง + เดือนนี้ + 1 เดือนข้างหน้า) — ใช้ etd
        $monthlyData = [];
        for ($i = 2; $i >= -1; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $count = Customershipping::where('customerno', $customerno)
                ->where('excel_status', 1)
                ->whereYear('etd', $month->year)
                ->whereMonth('etd', $month->month)
                ->count();
            $monthlyData[] = [
                'label' => $month->locale('th')->translatedFormat('M Y'),
                'count' => $count,
            ];
        }

        // ความเคลื่อนไหวล่าสุด (10 รายการ)
        $recentShipments = Customershipping::where('customerno', $customerno)
            ->where('excel_status', 1)
            ->latest('updated_at')
            ->take(10)
            ->get();

        // รายการสินค้าล่าสุด (จำนวนเท่ากับ ETD timeline)
        $etdTimelineCount = Customershipping::where('customerno', $customerno)
            ->where('excel_status', 1)
            ->where('etd', '>=', $threeMonthsAgo)
            ->selectRaw('COUNT(DISTINCT DATE(etd)) as cnt')
            ->value('cnt');
        $orderLimit = max($etdTimelineCount, 10);
        $recentOrders = Customerorder::where('customerno', $customerno)
            ->latest('created_at')
            ->take($orderLimit)
            ->get();

        // Shipping statuses for name lookup
        $shippingStatuses = DB::table('shipping_statuses')->pluck('name', 'id')->toArray();

        // Pay statuses for orders
        $payStatuses = DB::table('pay_statuses')->pluck('name', 'id')->toArray();

        // ETD dates with status (reuse getEtd3Month logic, extended to show count)
        $etdTimeline = Customershipping::selectRaw('DATE(etd) as etd_date, COUNT(*) as total,
            SUM(CASE WHEN status = 1 THEN 1 ELSE 0 END) as cnt_pending,
            SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) as cnt_shipping,
            SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) as cnt_arrived,
            SUM(CASE WHEN status = 4 THEN 1 ELSE 0 END) as cnt_completed,
            MAX(COALESCE(shipping_method, 1)) as shipping_method,
            CASE 
                WHEN SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) > 0 THEN 2
                WHEN SUM(CASE WHEN status = 3 THEN 1 ELSE 0 END) > 0 AND SUM(CASE WHEN status = 2 THEN 1 ELSE 0 END) = 0 THEN 3
                ELSE MAX(status)
            END as main_status')
            ->where('customerno', $customerno)
            ->where('excel_status', 1)
            ->where('etd', '>=', $threeMonthsAgo)
            ->groupBy('etd_date')
            ->orderBy('etd_date', 'desc')
            ->get();

        return view('customershippingview.analytics', compact(
            'totalShipments', 'statusCounts', 'monthlyData',
            'recentShipments', 'recentOrders', 'shippingStatuses', 'payStatuses', 'etdTimeline'
        ));
    }

    /**
     * Batch update recipient/delivery info for multiple items at once
     */
    public function batchUpdateRecipient(Request $request)
    {
        $authUser = Auth::user();

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'delivery_type_id' => 'required|integer|in:1,2,3',
        ]);

        $ids = $request->input('ids');
        $deliveryTypeId = $request->input('delivery_type_id');

        // Security: only update items belonging to this customer
        $validIds = Customershipping::whereIn('id', $ids)
            ->where('customerno', $authUser->customerno)
            ->where('excel_status', 1)
            ->pluck('id')
            ->toArray();

        if (empty($validIds)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรายการที่เลือก'], 404);
        }

        try {
            $updateData = ['delivery_type_id' => $deliveryTypeId];

            if ($deliveryTypeId == 1) {
                // รับเอง - set pickup person name if provided, clear address info
                $updateData['delivery_fullname'] = $request->input('delivery_fullname', null);
                $updateData['delivery_mobile'] = null;
                $updateData['delivery_address'] = null;
                $updateData['delivery_subdistrict'] = null;
                $updateData['delivery_district'] = null;
                $updateData['delivery_province'] = null;
                $updateData['delivery_postcode'] = null;
            } elseif ($deliveryTypeId == 2) {
                // ที่อยู่ปัจจุบัน - use auth user's address
                $updateData['delivery_fullname'] = $authUser->name;
                $updateData['delivery_mobile'] = $authUser->mobile;
                $updateData['delivery_address'] = $authUser->addr;
                $updateData['delivery_subdistrict'] = $authUser->subdistrinct;
                $updateData['delivery_district'] = $authUser->distrinct;
                $updateData['delivery_province'] = $authUser->province;
                $updateData['delivery_postcode'] = $authUser->postcode;
            } else {
                // เพิ่มที่อยู่เอง - use provided data
                $request->validate([
                    'delivery_fullname' => 'required|string|max:255',
                    'delivery_mobile' => 'required|string|max:50',
                    'delivery_address' => 'required|string|max:255',
                    'delivery_subdistrict' => 'required|string|max:255',
                    'delivery_district' => 'required|string|max:255',
                    'delivery_province' => 'required|string|max:255',
                    'delivery_postcode' => 'required|string|max:10',
                ]);

                $updateData['delivery_fullname'] = $request->input('delivery_fullname');
                $updateData['delivery_mobile'] = str_replace([' ', '-'], '', $request->input('delivery_mobile'));
                $updateData['delivery_address'] = $request->input('delivery_address');
                $updateData['delivery_subdistrict'] = $request->input('delivery_subdistrict');
                $updateData['delivery_district'] = $request->input('delivery_district');
                $updateData['delivery_province'] = $request->input('delivery_province');
                $updateData['delivery_postcode'] = $request->input('delivery_postcode');
            }

            // Add note if provided
            if (!empty($request->input('note'))) {
                $updateData['note'] = $request->input('note');
            }

            Customershipping::whereIn('id', $validIds)->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทผู้รับสำเร็จ ' . count($validIds) . ' รายการ',
                'updated_count' => count($validIds),
            ]);

        } catch (\Exception $e) {
            \Log::error('batchUpdateRecipient error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด กรุณาลองใหม่อีกครั้ง'], 500);
        }
    }

    /**
     * Get distinct recipient names for a customer + ETD (for filter dropdown)
     */
    public function getRecipients(Request $request)
    {
        $authUser = Auth::user();
        $query = Customershipping::where('excel_status', 1)
            ->where('customerno', $authUser->customerno);

        if (!empty($request->etd)) {
            // SARGable range แทน DATE(etd)=? เพื่อให้ใช้ index idx_cs_customerno_etd
            $query->where('etd', '>=', $request->etd.' 00:00:00')
                  ->where('etd', '<=', $request->etd.' 23:59:59');
        }

        $rows = $query->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
            ->selectRaw('MAX(delivery_type_id) as dtype')
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('recipient_name')
            ->orderByRaw('cnt DESC')
            ->get();

        $normal = [];
        $pickup = [];
        foreach ($rows as $item) {
            $name = $item->recipient_name;
            $isPickup = ($item->dtype == 1);
            if ($name === '__empty__') {
                $entry = ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
            } else {
                $label = $isPickup ? '(รับเอง) ' . $name : $name;
                $entry = ['name' => $name, 'label' => $label, 'count' => $item->cnt, 'value' => $name];
            }
            if ($isPickup) {
                $pickup[] = $entry;
            } else {
                $normal[] = $entry;
            }
        }

        usort($normal, function($a, $b) { return strcmp($a['name'], $b['name']); });
        usort($pickup, function($a, $b) { return strcmp($a['name'], $b['name']); });

        return response()->json(['recipients' => array_merge($normal, $pickup)]);
    }

    /**
     * Generate PromptPay QR code for invoice payment
     */
    public function generateInvoiceQr(Request $request)
    {
        $amount = floatval($request->amount);
        if ($amount <= 0) {
            return response()->json(['success' => false, 'message' => 'ยอดเงินไม่ถูกต้อง'], 400);
        }

        $qrUrl = PromptPayQrService::generateQrUrl($amount, 'invoice');

        return response()->json([
            'success' => true,
            'qr_url' => $qrUrl,
            'amount' => $amount,
            'formatted_amount' => number_format($amount, 2, '.', ','),
        ]);
    }

    public static function getEtd3Month($customerno)
    {
        $etdDates = Customershipping::selectRaw('DISTINCT DATE(etd) as etd,
            CASE
                WHEN COUNT(CASE WHEN status = 2 THEN 1 END) > 0 AND COUNT(CASE WHEN status = 3 THEN 1 END) = 0 THEN 2
                WHEN COUNT(CASE WHEN status = 3 THEN 1 END) > 0 AND COUNT(CASE WHEN status = 2 THEN 1 END) = 0 THEN 3
                WHEN COUNT(CASE WHEN status = 2 THEN 1 END) > 0 AND COUNT(CASE WHEN status = 3 THEN 1 END) > 0 THEN 2
                ELSE MAX(status)
            END as status,
            SUM(CASE WHEN shipping_method = 2 THEN 1 ELSE 0 END) as air_count,
            SUM(CASE WHEN shipping_method = 1 OR shipping_method IS NULL THEN 1 ELSE 0 END) as sea_count')
            ->where('customerno', $customerno)
            //เพิ่มเป็น 6 เดือน
            ->where('etd', '>=', Carbon::now()->subMonths(6)->format('Y-m-d'))
            ->groupBy('etd')
            ->orderBy('etd', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                $formattedDate = Carbon::parse($item->etd)->format('d/m/Y'); // วันที่ที่จะแสดงใน dropdown
                $valueDate = Carbon::parse($item->etd)->format('Y-m-d'); // ค่า value ใน format Y-m-d

                // กำหนดสัญลักษณ์สีตามสถานะ
                $statusIndicator = '';
                if ($item->status == 2) {
                    $statusIndicator = '🔴'; // สีแดง - อยู่ระหว่างขนส่ง
                } elseif ($item->status == 3 || $item->status == 4) {
                    $statusIndicator = '🟢'; // สีเขียว - สินค้าถึงไทยแล้ว หรือ สำเร็จ
                }

                // ไอคอนประเภทขนส่ง: เครื่องบิน (✈️) ถ้ามีของทางอากาศมากกว่า, ไม่งั้นเรือ (🚢)
                $airCount = (int) ($item->air_count ?? 0);
                $seaCount = (int) ($item->sea_count ?? 0);
                $methodIcon = $airCount > $seaCount ? '✈️' : '🚢';

                // รูปแบบ: [status] [icon] วันที่   เช่น  🔴 🚢 11/05/2026
                $parts = array_filter([$statusIndicator, $methodIcon, $formattedDate]);
                return [$valueDate => implode(' ', $parts)];
            });

        return $etdDates;
    }
}
