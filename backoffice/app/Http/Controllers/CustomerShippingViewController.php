<?php

namespace App\Http\Controllers;

use App\Exports\CustomershippigviewHtmlExport;
use App\Models\Customershipping;
use App\Models\Customerorder;
use App\Models\DeliveryType;
use App\Models\ShippingStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;


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
        return view('customershippingview.index');
    }

    public function clearSession()
    {
        session()->forget('startdate');

        return redirect()->route('shippingview.index');
    }

    public function update_StatusByIDs(Request $request){
//Destination Date
        $Ids = explode(',', $request->input('track_ids'));
        $authUser = Auth::user();
//        dd($authUser);
        try{

            Customershipping::whereIn('id', $Ids)->update([
                'delivery_type_id' => 2,
                'delivery_fullname'=>$authUser->name
                ,'delivery_mobile'=>$authUser->mobile
                , 'delivery_address'=>$authUser->addr
                , 'delivery_subdistrict'=>$authUser->subdistrinct
                , 'delivery_district'=>$authUser->distrinct
                , 'delivery_province'=>$authUser->province
                , 'delivery_postcode'=>$authUser->postcode

            ]);//delivery_type_id
            return redirect()->route('shippingview.index')
                ->with('success', 'บันทึกข้อมูลที่อยู่จัดส่งสินค้าสำเร็จ');

        } catch (\Exception $e) {
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

        return view('customershippingview.edit', compact('customershipping','authUser'));
    }


    public function update(Request $request, Customershipping $customershipping)
    {
        $customershipping->update(array_merge($request->all()
            , ['delivery_mobile'=>str_replace([' ', '-'], '', $request->delivery_mobile)]
        ));

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

                        if (!empty($request->start_date) && !empty($request->end_date))
                            $query->whereRaw("DATE(etd) BETWEEN ? AND ?", [$request->start_date, $request->end_date]);
                        else if (!empty($request->start_date))
                            $query->whereRaw("DATE(etd) BETWEEN ? AND ?", [$request->start_date, $request->start_date]);

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
                ->with(['cod_total' => number_format($codTotal, 2, '.', ',')
                    ,'weight_total'=>number_format($weightTotal, 2, '.', ',')
                    ,'import_cost_total'=>number_format($import_costTotal, 2, '.', ',')
                    ,'price_total'=>number_format($priceTotal, 2, '.', ',')
                    ,'total_records'=>$totalRecords
                    ,'start_date'=>$startDate
                    ,'data_export_link'=>url('customershippingsviewexport2',['customerno'=>!empty($request->customerno)?$request->customerno:'','start_date'=>$startDateRaw])

                    ,'query'=>$sqlQuery
                ])// แสดงผลรวมของค่า COD])
                ->make(true);


        }
    }

    public function export2(Request $request)
    {
//        return view('customershipping.export',[
//            'customershippings'=>Customershipping::all()
//        ]);
        return Excel::download(new CustomershippigviewHtmlExport($request->start_date,$request->customerno), 'shipping_data_' . time() . '.xlsx');
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
            $month = Carbon::now()->subMonths($i);
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
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
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
            $query->whereRaw('DATE(etd) = ?', [$request->etd]);
        }

        $recipients = $query->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('recipient_name')
            ->orderByRaw('cnt DESC')
            ->get()
            ->map(function($item) {
                $name = $item->recipient_name;
                if ($name === '__empty__') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
                }
                return ['name' => $name, 'label' => $name, 'count' => $item->cnt, 'value' => $name];
            });

        return response()->json(['recipients' => $recipients]);
    }

    public static function getEtd3Month($customerno)
    {
        $etdDates = Customershipping::selectRaw('DISTINCT DATE(etd) as etd, 
            CASE 
                WHEN COUNT(CASE WHEN status = 2 THEN 1 END) > 0 AND COUNT(CASE WHEN status = 3 THEN 1 END) = 0 THEN 2
                WHEN COUNT(CASE WHEN status = 3 THEN 1 END) > 0 AND COUNT(CASE WHEN status = 2 THEN 1 END) = 0 THEN 3
                WHEN COUNT(CASE WHEN status = 2 THEN 1 END) > 0 AND COUNT(CASE WHEN status = 3 THEN 1 END) > 0 THEN 2
                ELSE MAX(status)
            END as status')
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
                    $statusIndicator = '🔴 '; // สีแดง - อยู่ระหว่างขนส่ง
                } elseif ($item->status == 3 || $item->status == 4) {
                    $statusIndicator = '🟢 '; // สีเขียว - สินค้าถึงไทยแล้ว หรือ สำเร็จ
                }
                // ไม่แสดง icon สำหรับสถานะอื่นๆ
                
                return [$valueDate => $statusIndicator . $formattedDate];
            });

        return $etdDates;
    }
}
