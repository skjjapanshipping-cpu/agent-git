<?php

namespace App\Http\Controllers;

use App\Exports\CustomershippigHtmlExport;
use App\Exports\CustomershippingExport;
use App\Imports\CustomershippingsImport;
use App\Models\Customerorder;
use App\Models\Customershipping;
use App\Models\DeliveryType;
use App\Models\PayStatus;
use App\Models\Track;
use App\Tambon;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Helpers\ChatNotify;
use Yajra\DataTables\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\LineMessagingService;
use App\MyAuthProvider;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;


/**
 * Class CustomershippingController
 * @package App\Http\Controllers
 */
class CustomershippingController extends Controller
{
    public function __construct() {
        $this->middleware(['role:admin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('customershipping.index');
    }

    /***
     * Backend
     * @param Request $request
     * @return void
     * @throws \Exception
     */
    public function fetchCustomershippings(Request $request)
    {


        if ($request->ajax()) {
            $sqlQuery = '';
            //            $queryAll = Customershipping::latest('etd','ship_date')->where('excel_status','=','1');
            $queryAll = Customershipping::where('excel_status', '=', '1')
                ->orderBy('etd', 'desc');

            //            dd($request->start_date);
            if (!empty($request->start_date)) {
                session(['startdate' => $request->start_date]);
                //                dd($request->start_date);
            } else
                session()->forget('startdate');

            // ค้นหาเลขกล่อง (จาก floating box search) — ค้นเฉพาะเมื่อเลือกรอบปิดตู้แล้ว
            if (!empty($request->box_no) && !empty($request->start_date)) {
                $boxSearchTerm = '%'.$request->box_no.'%';
                $queryAll->whereRaw("box_no like ?", [$boxSearchTerm]);
            }

            if (!empty($request->search) || !empty($request->status) || !empty($request->delivery_type_id) || !empty($request->pay_status) || !empty($request->shipping_method) || !empty($request->start_date) || (!empty($request->start_date) && !empty($request->end_date))) {
                //
                if (strtolower($request->search) == strtolower('แสดง')) {

                    session(['hide' => true]);
                }

                if (strtolower($request->search) == strtolower('ซ่อน')) {

                    session(['hide' => false]);
                }

                if (strtolower($request->search) == strtolower('all')) {

                    $data = $queryAll->take(5000)->get();
                } else {
                    $data = $queryAll->where(function ($query) use ($request) {
                        if (!empty($request->search)) {

                            $query->where(function ($query) use ($request) {
                                $searchTerm = '%'.$request->search.'%';
                                $searchNoHyphens = '%'.str_replace('-', '', $request->search).'%';
                                $query->whereRaw("customerno like ?", [$searchTerm])
                                    ->orWhereRaw("box_no like ?", [$searchTerm])
                                    ->orWhereRaw("DATE_FORMAT(ship_date, '%d/%m/%Y') like ?", [$searchTerm])
                                    ->orWhereRaw("REPLACE(track_no, '-', '') LIKE ?", [$searchNoHyphens]);
                            });
                            //                            session(['search' => $request->search]);
                            //                            dd(session('search'));
                        }
                        //                        else{
                        ////                            session()->forget('search');
                        //                        }
                        if (!empty($request->status))
                            $query->where('status', $request->status);
                        if (!empty($request->delivery_type_id)) {
                            if (strpos($request->delivery_type_id, ',') !== false) {
                                $query->whereIn('delivery_type_id', explode(',', $request->delivery_type_id));
                            } else {
                                $query->where('delivery_type_id', $request->delivery_type_id);
                            }
                        }

                        if (!empty($request->pay_status))
                            $query->where('pay_status', $request->pay_status);

                        if (!empty($request->shipping_method))
                            $query->where('shipping_method', $request->shipping_method);

                        if (!empty($request->recipient_filter)) {
                            if ($request->recipient_filter === '__empty__') {
                                $query->where(function($q) {
                                    $q->whereNull('delivery_fullname')->orWhere('delivery_fullname', '')->orWhereRaw("TRIM(delivery_fullname) = ''");
                                });
                            } else {
                                $query->where('delivery_fullname', $request->recipient_filter);
                            }
                        }

                        if (!empty($request->start_date) && !empty($request->end_date))
                            $query->whereBetween('etd', [$request->start_date, $request->end_date]);
                        else if (!empty($request->start_date))
                            // ใช้ range แทน DATE(etd) เพื่อให้ใช้ index idx_cs_etd ได้ (เร็วขึ้น ~9 เท่า)
                            $query->where('etd', '>=', $request->start_date.' 00:00:00')
                                  ->where('etd', '<=', $request->start_date.' 23:59:59');
                    })->orderByRaw('LENGTH(customerno) ASC, customerno ASC')->orderBy('ship_date', 'desc')->take(1500)->get();


                    $sqlQuery = $queryAll->toSql();
                    //                    dd($sqlQuery);
                }
            } else {
                // โหลดครั้งแรก: ใช้ subquery ดึง 2000 แถวล่าสุดด้วย index (idx_cs_excel_etd) ก่อน
                // แล้วค่อย sort 300 แถว — ผลลัพธ์เหมือน filesort ทั้งตารางแต่เร็วกว่า ~4 เท่า
                $recentIds = Customershipping::where('excel_status', '=', '1')
                    ->orderBy('etd', 'desc')->take(2000)->pluck('id');
                $data = Customershipping::whereIn('id', $recentIds)
                    ->orderBy('etd', 'desc')
                    ->orderByRaw('LENGTH(customerno) ASC, customerno ASC')
                    ->orderBy('ship_date', 'desc')
                    ->take(300)->get();

            }
            $sqlQuery = $queryAll->toSql();

            foreach ($data as $customershipping) {
                $customershipping->status = Customershipping::getShippingStatusNameById($customershipping->status)->name;
            }


            // คำนวณ COD Total โดยใช้ cod_rate ของแต่ละ record (ข้อมูลเก่าใช้ rate เดิม)
            $codTotal = $data->sum(function($item) {
                return $item->cod * ($item->cod_rate ?? 0.25);
            });

            $weightTotal = $data->sum('weight');
            $import_costTotal = $data->sum('import_cost');
            $priceTotal = $import_costTotal + $codTotal;
            $totalRecords = count($data);

            // คำนวณยอดต่อบิล (batch) — แยกตาม invoice_sent_at เพื่อรองรับการออกบิลแยกหลายครั้ง
            // ในรอบปิดตู้/ลูกค้าเดียวกัน. กรณีบิลเก่าที่ยังไม่มี invoice_sent_at (null)
            // จะถูกจัดกลุ่มเดียวกัน (เหมือนพฤติกรรมเดิม — ไม่ regression)
            $invoiceGroupTotals = $data->filter(function($item) {
                return $item->pay_status != 1;
            })->groupBy(function($item) {
                $batchKey = $item->invoice_sent_at
                    ? \Carbon\Carbon::parse($item->invoice_sent_at)->format('YmdHis')
                    : 'legacy';
                return $item->customerno . '|' . ($item->etd ?? '') . '|' . $item->pay_status . '|' . $batchKey;
            })->map(function($group) {
                return round($group->sum(function($s) {
                    return $s->import_cost + ($s->cod * ($s->cod_rate ?? 0.25));
                }), 2);
            });
            $startDate = !empty($request->start_date) ? Carbon::parse($request->start_date)->format('d/m/Y') : '';
            $startDateRaw = $request->start_date;
            
            
            //            dd($data);
            //            dd($sqlQuery);
            return Datatables::of($data)
                //                ->addColumn('cod_total',function($row) use($codTotal){
                //                    return number_format($codTotal, 2, '.', ','); // แสดงผลรวมของค่า COD
                //                })
                ->addColumn('action_del', function ($row) {
                    return route('customershippings.destroy', $row->id);
                })
                ->addColumn('edit_url', function ($row) {
                    return route('customershippings.edit', $row->id);
                })
                ->addColumn('delivery_type_name', function ($row) {
                    return DeliveryType::getNameById($row->delivery_type_id);
                })->addColumn('shipping_method_label', function ($row) {
                    return Customershipping::getShippingMethodLabel($row->shipping_method ?? 1);
                })->addColumn('pay_status', function ($row) {
                    return PayStatus::getNameById($row->pay_status);
                })->addColumn('thai_bill_status', function ($row) {
                    $labels = [0 => '-', 1 => 'รอโอน', 2 => 'โอนแล้ว'];
                    return $labels[$row->thai_bill_status ?? 0] ?? '-';
                })->addColumn('thai_bill_amount_display', function ($row) {
                    return $row->thai_bill_amount ? number_format($row->thai_bill_amount, 0) : '-';
                })->addColumn('invoice_group_total', function ($row) use ($invoiceGroupTotals) {
                    if ($row->pay_status == 1) return '-';
                    $batchKey = $row->invoice_sent_at
                        ? \Carbon\Carbon::parse($row->invoice_sent_at)->format('YmdHis')
                        : 'legacy';
                    $key = $row->customerno . '|' . ($row->etd ?? '') . '|' . $row->pay_status . '|' . $batchKey;
                    $total = $invoiceGroupTotals[$key] ?? 0;
                    return $total > 0 ? number_format($total, 2) : '-';
                })
                ->with([
                    'cod_total' => number_format($codTotal, 2, '.', ','),
                    'weight_total' => number_format($weightTotal, 2, '.', ','),
                    'import_cost_total' => number_format($import_costTotal, 2, '.', ','),
                    'price_total' => number_format($priceTotal, 2, '.', ','),
                    'total_records' => $totalRecords,
                    'start_date' => $startDate,
                    'data_export_link' => url('customershippingsexport2/' . $startDateRaw . (!empty($request->search) ? '?customerno=' . rawurlencode(trim($request->search)) : '')),
                    'shipping_export_link' => url('customershippingsexport/' . $startDateRaw . (!empty($request->search) ? '?customerno=' . rawurlencode(trim($request->search)) : '')),
                    'query' => config('app.debug') ? $sqlQuery : null,
                    'rq' => $request->delivery_type_id
                ]) // แสดงผลรวมของค่า COD])
                ->make(true);
        }
    }



    public function update_StatusByIDs(Request $request)
    {

        $Ids = explode(',', $request->input('track_ids'));

        try {
            $customershippings = Customershipping::whereIn('id', $Ids)->get();

            if ($customershippings->isEmpty()) {
                return redirect()->route('customershippings.index')->with('error', 'ไม่พบรายการที่เลือก');
            }

            // อัปเดต tracks.destination_date เฉพาะ track_no ของรายการที่เลือกเท่านั้น
            // (เดิมกรองด้วย DATE(ship_date) อย่างเดียว → ไปโดน track ของลูกค้าคนอื่นที่ส่งวันเดียวกันทั้งหมด)
            $variants = [];
            foreach ($customershippings as $cs) {
                $raw = (string) $cs->track_no;
                if ($raw === '') continue;
                $variants[] = $raw;
                $variants[] = str_replace('-', '', $raw);
            }
            $variants = array_values(array_unique(array_filter($variants, 'strlen')));
            if (!empty($variants)) {
                Track::whereIn('track_no', $variants)
                    ->whereNull('destination_date')
                    ->update([
                        'destination_date' => Carbon::now()->format('Y-m-d'),
                    ]);
            }
            foreach ($customershippings as $shipping) {
                try {
                    Customerorder::where('customerno', $shipping->customerno)
                        ->where('itemno', $shipping->itemno)
                        ->update(['shipping_status' => 3]);
                } catch (\Exception $e) {
                    Log::error('Error updating customerorder: ' . $e->getMessage());
                }
            }
            $adminName = \Auth::user()->name ?? 'admin';
            foreach ($Ids as $id) {
                Customershipping::where('id', $id)->whereNull('scanned_at')->update([
                    'status' => 3,
                    'scanned_at' => now(),
                    'scanned_by' => $adminName,
                ]);
                Customershipping::where('id', $id)->whereNotNull('scanned_at')->update([
                    'status' => 3,
                ]);
            }

            return redirect()->route('customershippings.index')
                ->with('success', 'อัปเดตสินค้าถึงไทยเรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->route('customershippings.index')
                ->with('error', 'อัปเดตสินค้าถึงไทยไม่สำเร็จ');
        }
    }

    public function update_StatusByIDs2(Request $request)
    {

        $Ids = explode(',', $request->input('track_ids2'));

        try {
            // ดึง customerno + etd ก่อนอัพเดท เพื่อ sync ไป SKJ Chat
            $items = Customershipping::whereIn('id', $Ids)->select('customerno', 'etd')->get();

            Customershipping::whereIn('id', $Ids)->update([
                'pay_status' => 2
            ]);

            // Sync สถานะชำระเงินไป SKJ Chat (invoiceSent → paid)
            $synced = [];
            foreach ($items->groupBy('customerno') as $cn => $group) {
                foreach ($group->pluck('etd')->unique() as $etd) {
                    if (!$etd) continue;
                    $etdFormatted = \Carbon\Carbon::parse($etd)->format('d/m/Y');
                    $key = $cn . '|' . $etdFormatted;
                    if (in_array($key, $synced)) continue;
                    $synced[] = $key;
                    try {
                        $chatBase = rtrim((string) config('services.skjchat.base_url'), '/');
                        \Illuminate\Support\Facades\Http::withHeaders([
                            'X-API-Key' => (string) config('services.skjchat.api_key'),
                            'Content-Type' => 'application/json',
                        ])->timeout(10)->post($chatBase . '/api/invoice-update-status', [
                            'customerno' => $cn,
                            'etd' => $etdFormatted,
                            'status' => 'paid',
                        ]);
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::warning('[PAY-SYNC] Failed to sync to SKJ Chat', ['customerno' => $cn, 'error' => $e->getMessage()]);
                    }
                }
            }

            return redirect()->route('customershippings.index')
                ->with('success', 'อัปเดตสินค้าชำระเงินแล้ว');
        } catch (\Exception $e) {
            return redirect()->route('customershippings.index')
                ->with('error', 'อัปเดตสินค้าชำระเงินไม่สำเร็จ');
        }
    }

    public function update_StatusByIDs3(Request $request)
    {

        $Ids = explode(',', $request->input('track_ids3'));

        try {

            $adminName = \Auth::user()->name ?? 'admin';
            foreach ($Ids as $id) {
                $cs = Customershipping::find($id);
                if (!$cs) continue;

                $updates = ['status' => 4];
                if (!$cs->scanned_at) {
                    $updates['scanned_at'] = now();
                    $updates['scanned_by'] = $adminName;
                }
                if (!$cs->picked_up_at) {
                    $updates['picked_up_at'] = now();
                    $updates['picked_up_by'] = $adminName;
                }
                $cs->update($updates);

                \App\Models\Customerorder::where('customerno', $cs->customerno)
                    ->where('itemno', $cs->itemno)
                    ->update(['shipping_status' => 4]);
            }
            
            // ยังไม่แจ้งลูกค้าจากปุ่มนี้ — ให้แจ้งเฉพาะจาก Pickup Scan เมื่อจ่ายครบเท่านั้น

            return redirect()->route('customershippings.index')
                ->with('success', 'อัปเดตสถานะสำเร็จแล้ว');
        } catch (\Exception $e) {
            return redirect()->route('customershippings.index')
                ->with('error', 'อัปเดตสถานะรับสินค้าไม่สำเร็จ');
        }
    }


    public function updateThaiBillPaid(Request $request)
    {
        $Ids = explode(',', $request->input('track_ids4'));

        try {
            Customershipping::whereIn('id', $Ids)->update([
                'thai_bill_status' => 2
            ]);

            return redirect()->route('customershippings.index')
                ->with('success', 'อัปเดตชำระค่าส่งไทยแล้ว ' . count($Ids) . ' รายการ');
        } catch (\Exception $e) {
            return redirect()->route('customershippings.index')
                ->with('error', 'อัปเดตชำระค่าส่งไทยไม่สำเร็จ');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customershipping = new Customershipping();
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();

        return view('customershipping.create', compact('customershipping', 'provinces', 'amphoes', 'tambons'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Customershipping::$rules);


        $uploadUrl = 'uploads/excel_images';
        $saveImages = [];

        //box_image
        if ($request->hasFile('box_image')) {
            // dd('box_image');
            //            dd($request->all());
            // ถ้ามีการอัปโหลดรูปภาพใหม่
            $image = $request->file('box_image');
            $imageName['box_image'] = time() . '_' . $image->getClientOriginalName();
            $image->move($uploadUrl, $imageName['box_image']);
            $saveImages['box_image'] = $uploadUrl . "/" . $imageName['box_image'];
            //            dd($request->all(),$saveImages);
        }
       
        //product_image
        if ($request->hasFile('product_image')) {
            //            echo  'product_image<br>';
            // dd('product_image');
            //            dd($request->all());
            // ถ้ามีการอัปโหลดรูปภาพใหม่
            $image = $request->file('product_image');
            $imageName['product_image'] = time() . '_' . $image->getClientOriginalName();
            $image->move($uploadUrl, $imageName['product_image']);
            $saveImages['product_image'] = $uploadUrl . "/" . $imageName['product_image'];
        }else if(!empty($request->itemno)){ //ถ้าไม่มีการอัปโหลดรูปภาพ แต่มี itemno จะดึงรูปจาก Order
            $customerorder = Customerorder::where('customerno', $request->customerno)
            ->where('itemno', $request->itemno)
            ->first();

            // ตรวจสอบว่ามี customerorder ก่อนอัพเดท
            if ($customerorder) {
                $saveImages['product_image'] = 'uploads/'.$customerorder->image_link;
                $customerorder->tracking_number = $request->track_no;
                $customerorder->cutoff_date = $request->etd;
                $customerorder->shipping_status = $request->status;
                $customerorder->save();
            }
        }

        $import_cost = 0;
        $weight = !empty(trim($request->weight)) ? trim($request->weight) : 1;
        $unit_price = !empty(trim($request->unit_price)) ? trim($request->unit_price) : 0;

        $isWholePrice = $request->input('iswholeprice', 0); // ถ้าไม่เลือกจะเป็น 0
        $import_cost = $isWholePrice == 1 ? $request->import_cost : $unit_price * $weight; //ถ้าเป็นราคาเหมา ให้แสดงค่านำเข้าเลยไม่ต้องคำนวณ

        // ราคาเหมา: ถ้ามีขนาดครบ คำนวณ import_cost จาก w*l*h*0.01 อัตโนมัติเสมอ
        if ($isWholePrice == 1) {
            $w = (float) $request->input('width');
            $l = (float) $request->input('length');
            $h = (float) $request->input('height');
            if ($w > 0 && $l > 0 && $h > 0) {
                $import_cost = round($w * $l * $h * 0.01, 2);
            }
        }

        $customerorder = Customerorder::where('customerno', $request->customerno)->where('itemno', $request->itemno)->first();
        
        // ตรวจสอบว่ามี customerorder ก่อนอัพเดท
        if ($customerorder) {
            //อัพเดทเลขแทรค ที่ Order และดึงรูปจาก Order มาอัพที่ Shipping
            $customerorder->tracking_number = $request->track_no; //เลขพัสดุ
            $customerorder->cutoff_date = $request->etd; //รอบปิดตู้
            $customerorder->shipping_status = $request->status; // สถานะ ขนส่ง
            $customerorder->save();
        }

        $customershipping = Customershipping::create(array_merge(
            $request->except(['id', 'excel_status', 'pay_status', 'scanned_at', 'scanned_by', 'picked_up_at', 'picked_up_by', 'created_at', 'updated_at']),
            $saveImages,
            [
                'excel_status' => 1, 
                'import_cost' => $import_cost, 
                'iswholeprice' => $isWholePrice,
                'cod_rate' => \App\Models\Dailyrate::getCodRate()
            ]
        ));

        return redirect()->route('customershippings.index')
            ->with('success', 'สร้างรายการสินค้าสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customershipping = Customershipping::findOrFail($id);

        return view('customershipping.show', compact('customershipping'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //        dd(\request('search'));
        $customershipping = Customershipping::findOrFail($id);
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();
        $authUser =  User::whereRaw("lower(customerno) like lower(?)", ['%'.$customershipping->customerno.'%'])->first();

        //dd($authUser);
        return view('customershipping.edit', compact('customershipping', 'provinces', 'amphoes', 'tambons', 'authUser'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param Customershipping $customershipping
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customershipping $customershipping)
    {
        request()->validate(Customershipping::$rules);
        //dd($customershipping);
        /**
         * upload/excel_images/
         */
        $uploadUrl = 'uploads/excel_images';
        $imageName['box_image'] = $customershipping->box_image; // เก็บชื่อรูปภาพเดิมไว้เพื่อใช้ในกรณีที่ไม่มีการเปลี่ยนแปลงรูปภาพ
        $imageName['product_image'] = $customershipping->product_image; // เก็บชื่อรูปภาพเดิมไว้เพื่อใช้ในกรณีที่ไม่มีการเปลี่ยนแปลงรูปภาพ
        $saveImages = [];

        //box_image
        if ($request->hasFile('box_image')) {
            //            dd($request->all());
            // ถ้ามีการอัปโหลดรูปภาพใหม่
            $image = $request->file('box_image');
            $imageName['box_image'] = time() . '_' . $image->getClientOriginalName();
            $image->move($uploadUrl, $imageName['box_image']);
            $saveImages['box_image'] = $uploadUrl . "/" . $imageName['box_image'];
            //            dd($request->all(),$saveImages);
            // ตรวจสอบและลบรูปภาพเก่า (หากมี)
            if (!empty($customershipping->box_image)) {
                $oldImagePath = $customershipping->box_image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        //product_image
        if ($request->hasFile('product_image')) {
            //            echo  'product_image<br>';
            //            dd($request->all());
            // ถ้ามีการอัปโหลดรูปภาพใหม่
            $image = $request->file('product_image');
            $imageName['product_image'] = time() . '_' . $image->getClientOriginalName();
            $image->move($uploadUrl, $imageName['product_image']);
            $saveImages['product_image'] = $uploadUrl . "/" . $imageName['product_image'];
            // ตรวจสอบและลบรูปภาพเก่า (หากมี)
            if (!empty($customershipping->product_image)) {
                $oldImagePath = $customershipping->product_image;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }else if(!empty($request->itemno)){ //ถ้าไม่มีการอัปโหลดรูปภาพ แต่มี itemno จะดึงรูปจาก Order
            $customerorder = Customerorder::where('customerno', $request->customerno)
            ->where('itemno', $request->itemno)
            ->first();

            // ตรวจสอบว่ามี customerorder ก่อนอัพเดท
            if ($customerorder) {
                $saveImages['product_image'] = 'uploads/'.$customerorder->image_link;
                $customerorder->tracking_number = $request->track_no;
                $customerorder->cutoff_date = $request->etd;
                $customerorder->shipping_status = $request->status;
                $customerorder->save();
            }
        }

        // ตรวจสอบการลบรูปภาพ
        if($request->has('delete_product_image')) {
            if(!empty($customershipping->product_image)) {
                $oldImagePath = $customershipping->product_image;
                if(file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $saveImages['product_image'] = null;
        }

        // ตรวจสอบการลบรูปหน้ากล่อง
        if($request->has('delete_box_image')) {
            if(!empty($customershipping->box_image)) {
                $oldImagePath = $customershipping->box_image;
                if(file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
            $saveImages['box_image'] = null;
        }

        $import_cost = 0;
        $weight = !empty(trim($request->weight)) ? trim($request->weight) : 1;
        $unit_price = !empty(trim($request->unit_price)) ? trim($request->unit_price) : 0;

        // ตรวจสอบฟิลด์ iswholeprice
        $isWholePrice = $request->input('iswholeprice', 0); // ถ้าไม่เลือกจะเป็น 0

        $import_cost = $isWholePrice == 1 ? $request->import_cost : $unit_price * $weight; //ถ้าเป็นราคาเหมา ให้แสดงค่านำเข้าเลยไม่ต้องคำนวณ

        // ราคาเหมา: ถ้ามีขนาดครบ คำนวณ import_cost จาก w*l*h*0.01 อัตโนมัติเสมอ
        if ($isWholePrice == 1) {
            $w = (float) $request->input('width');
            $l = (float) $request->input('length');
            $h = (float) $request->input('height');
            if ($w > 0 && $l > 0 && $h > 0) {
                $import_cost = round($w * $l * $h * 0.01, 2);
            }
        }
        //        dd($request->all(),array_merge($request->all()
        //        , $saveImages,['import_cost'=>$import_cost,'iswholeprice' => $isWholePrice,'pay_status'=>$request->pay_status]));
        $customershipping->update(array_merge(
            $request->except(['id', 'excel_status', 'scanned_at', 'scanned_by', 'picked_up_at', 'picked_up_by', 'created_at', 'updated_at']),
            $saveImages,
            ['import_cost' => $import_cost, 'iswholeprice' => $isWholePrice]
        ));



        return redirect()->route('customershippings.index')
            ->with('success', 'อัปเดตรายการสินค้าสำเร็จ')->with('search', $request->customerno);
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $customershipping = Customershipping::findOrFail($id);
        $customershipping->delete();

        return redirect()->route('customershippings.index')
            ->with('success', 'ลบรายการสินค้าสำเร็จ');
    }

    public function confirmImport()
    {
        $customershippings = Customershipping::where('excel_status', 0)->orderBy('ship_date')->orderBy('customerno')->paginate(10000);

        return view('customershipping.confirm', compact('customershippings'))
            ->with('i', (request()->input('page', 1) - 1) * $customershippings->perPage());
    }

    public function update_confirmimport(Request $request)
    {
        $trackIds = explode(',', $request->input('track_ids'));
        Log::info('Track IDs to update:', $trackIds);

        try {
            // ตรวจสอบว่ามี IDs ส่งมาหรือไม่
            if(empty($trackIds)) {
                Log::warning('No track IDs provided');
                return redirect()->route('customershippings.index')
                    ->with('error', 'ไม่พบรายการที่ต้องการอัพเดท');
            }

            $customershippings = Customershipping::whereIn('id', $trackIds)->get();
            Log::info('Found customershippings count: ' . $customershippings->count());

            // sync customerorder ของทุกแถว — กันเคสที่ batch มีหลาย (customerno, etd) ที่ไม่มี itemno
            // (เดิมใช้ flag $orderUdate ทำให้ sync แค่แถวแรกของทั้ง batch)
            $processedNoItemno = [];
            foreach ($customershippings as $shipping) {
                if (!empty($shipping->itemno)) {
                    $customerOrder = Customerorder::where('customerno', $shipping->customerno)
                        ->where('itemno', $shipping->itemno)
                        ->first();
                    // ตรวจสอบว่ามี customerOrder ก่อนอัพเดท
                    if ($customerOrder) {
                        $customerOrder->tracking_number = $shipping->track_no;
                        $customerOrder->cutoff_date = $shipping->etd;
                        $customerOrder->shipping_status = $shipping->status;
                        $customerOrder->save();
                    }
                } else {
                    // ไม่มี itemno → อัปเดตตาม (customerno, etd) ครั้งเดียวต่อคู่ (กันรันซ้ำ)
                    $key = $shipping->customerno . '|' . $shipping->etd;
                    if (!in_array($key, $processedNoItemno, true)) {
                        Customerorder::where('customerno', $shipping->customerno)
                            ->where('cutoff_date', $shipping->etd)
                            ->update([
                                'cutoff_date' => $shipping->etd,
                                'shipping_status' => $shipping->status,
                            ]);
                        $processedNoItemno[] = $key;
                    }
                }
            }

            // สร้างชุด (customerno, etd) ของ batch นี้ ไว้จำกัดการลบ draft ไม่ให้ไปโดนของ admin คนอื่น
            $batchByCustomer = $customershippings->groupBy('customerno');

            // แยก transaction สำหรับการอัพเดท excel_status
            DB::transaction(function() use ($trackIds, $batchByCustomer) {
                // ตรวจสอบก่อนอัพเดท
                $beforeUpdate = Customershipping::whereIn('id', $trackIds)
                    ->pluck('excel_status', 'id')
                    ->toArray();
                Log::info('Status before update:', $beforeUpdate);

                // อัพเดทสถานะ excel_status เป็น 1
                $updated = Customershipping::whereIn('id', $trackIds)
                    ->update(['excel_status' => 1]);
                
                // ตรวจสอบหลังอัพเดท
                $afterUpdate = Customershipping::whereIn('id', $trackIds)
                    ->pluck('excel_status', 'id')
                    ->toArray();
                Log::info('Status after update:', $afterUpdate);
                Log::info('Updated records count: ' . $updated);

                // ลบ draft ที่เหลือ เฉพาะ (customerno, etd) ที่อยู่ใน batch นี้ + สร้างภายใน 2 ชม.
                // (เดิมลบ draft ทั้งหมดทั่วระบบ → ไปโดน draft ของ admin คนอื่นที่กำลัง import พร้อมกัน)
                if ($batchByCustomer->isNotEmpty()) {
                    $deleted = Customershipping::where('excel_status', 0)
                        ->where('created_at', '>=', now()->subHours(2))
                        ->where(function($q) use ($batchByCustomer) {
                            foreach ($batchByCustomer as $cn => $rows) {
                                $etds = $rows->pluck('etd')->filter()->unique()->values()->all();
                                $q->orWhere(function($qq) use ($cn, $etds) {
                                    $qq->where('customerno', $cn);
                                    if (!empty($etds)) $qq->whereIn('etd', $etds);
                                });
                            }
                        })
                        ->delete();
                    Log::info('Deleted records count: ' . $deleted);
                }
            });

            return redirect()->route('customershippings.index')
                ->with('success', 'นำเข้าข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            Log::error('Error in update_confirmimport: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return redirect()->route('customershippings.index')
                ->with('error', 'นำเข้าข้อมูลไม่สำเร็จ: ' . $e->getMessage());
        }
    }

    public function update_confirmimport_bakup_nouse(Request $request)
    {
        //Destination Date
        $trackIds = explode(',', $request->input('track_ids'));

        try {
            // ทำสิ่งที่คุณต้องการกับข้อมูลที่ได้รับ
            // ตัวอย่าง: อัปเดต status ของ tracks ในฐานข้อมูล
            Customershipping::whereIn('id', $trackIds)->update([
                'excel_status' => 1,
            ]);
            Customershipping::where('excel_status', 0)->delete();
            return redirect()->route('customershippings.index')
                ->with('success', 'นำเข้าข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            return redirect()->route('customershippings.index')
                ->with('error', 'นำเข้าข้อมูลไม่สำเร็จ');
        }
    }

    public function del_confirmimport(Request $request)
    {
        try {
            // ลบเฉพาะ draft ที่สร้างภายใน 2 ชม.ล่าสุด
            // เพื่อกัน admin คนหนึ่งกดเคลียร์แล้วไปลบ draft ของ admin คนอื่นที่กำลัง import อยู่
            Customershipping::where('excel_status', 0)
                ->where('created_at', '>=', now()->subHours(2))
                ->delete();
            return redirect()->route('customershippings.index')
                ->with('success', 'เคลียร์ข้อมูลสำเร็จ');
        } catch (\Exception $e) {
            Log::error('del_confirmimport error: ' . $e->getMessage());
            return redirect()->route('customershippings.index')
                ->with('error', 'เคลียร์ข้อมูลไม่สำเร็จ');
        }
    }

    public function importView()
    {
        return view('customershipping.import');
    }


    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls']);

        $file = request()->file('file');
        $reader = new Xlsx();
        $spreadsheet = $reader->load($file);
        $sheet = $spreadsheet->getActiveSheet();
        $drawings = $sheet->getDrawingCollection();
        $imagesCell = [];

        foreach ($drawings as $drawing) {
            $drawing_path = $drawing->getPath();
            $imagesCell[$drawing->getCoordinates()] = $drawing_path;
        }
        //dd($imagesCell);
        $data = [];
        $customershippingsImport = new CustomershippingsImport();
        foreach ($sheet->getRowIterator() as $row) {
            $cells = [
                'A' => 'A' . $row->getRowIndex(),
                'B' => 'B' . $row->getRowIndex(),
                'C' => 'C' . $row->getRowIndex(),
                'D' => 'D' . $row->getRowIndex(),
                'E' => 'E' . $row->getRowIndex(),
                'F' => 'F' . $row->getRowIndex(),
                'G' => 'G' . $row->getRowIndex(),
                'H' => 'H' . $row->getRowIndex(),
                'I' => 'I' . $row->getRowIndex(),
                'J' => 'J' . $row->getRowIndex(),
                'K' => 'K' . $row->getRowIndex(),
                'L' => 'L' . $row->getRowIndex(),
                'M' => 'M' . $row->getRowIndex(),
                'N' => 'N' . $row->getRowIndex(),
            ];

            $data = [
                'ship_date' => $sheet->getCell($cells['A'])->getValue(), //วันที่
                'box_image' => $sheet->getCell($cells['B'])->getHyperlink() ? $sheet->getCell($cells['B'])->getHyperlink()->getUrl() : null, //รูปหน้ากล่อง
                'customerno' => $sheet->getCell($cells['C'])->getValue(), //รหัสลูกค้า
                'track_no' => $sheet->getCell($cells['D'])->getValue(), //เลขพัสดุ
                'cod' => $sheet->getCell($cells['E'])->getValue(), //cod
                'weight' => $sheet->getCell($cells['F'])->getValue(), //น้ำหนัก
                'unit_price' => $sheet->getCell($cells['G'])->getValue(), //หน่วยละ
                'import_cost' => $sheet->getCell($cells['H'])->getValue(), //ค่านำเข้า
                'product_image' => null, //รูปสินค้า (ดึงจาก ITEMS/MyOrders อัตโนมัติ)
                'box_no' => $sheet->getCell($cells['I'])->getValue(), //เลขกล่อง
                'warehouse' => null, //โกดัง (ไม่ใช้ใน Excel ใหม่)
                'etd' => $sheet->getCell($cells['J'])->getValue(), //รอบปิดตู้
                'status' => 2, //สถานะ คงที่ = 2
                'delivery_address' => null, //ที่อยู่จัดส่ง (ดึงจาก profile ลูกค้าอัตโนมัติ)
                'note' => $sheet->getCell($cells['K'])->getValue(), //หมายเหตุ
                'width' => null, //กว้าง
                'length' => null, //ยาว
                'height' => null, //สูง
                'itemno' => $sheet->getCell($cells['L'])->getValue(), //ItemNo
                'image_index' => $cells['B'],
                'note_admin' => $sheet->getCell($cells['M'])->getValue(), //หมายเหตุจากผู้ดูแลระบบ
                'shipping_method' => $sheet->getCell($cells['N'])->getValue(), //ประเภทขนส่ง 1=เรือ, 2=เครื่องบิน
            ];
            // dd($data);

            //            echo "1: ".$data['customerno']."Date:".$data['ship_date']." <br>";
            $customershippingsImport->model($data);
            //            if(isset($imagesCell[$cells['C']])) {
            ////                echo $imagesCell[$cells['C']]."<br>";
            //                $this->uploadImage($imagesCell[$cells['C']],$cells['C']);
            //            }
            //            if(isset($imagesCell[$cells['J']])) {
            ////                echo $imagesCell[$cells['C']]."<br>";
            //                $this->uploadImage($imagesCell[$cells['J']],$cells['J']);
            //            }

        }

        //dd($data);

        $importErrors = $customershippingsImport->getErrors();
        if (!empty($importErrors)) {
            session()->flash('import_errors', $importErrors);
        }

        $importSkipped = $customershippingsImport->getSkipped();
        if (!empty($importSkipped)) {
            session()->flash('import_skipped', $importSkipped);
        }

        return redirect()->route('customershippingsconfirm')
            ->with('success', 'กรุณาตรวจสอบข้อมูลก่อนยืนยัน');
    }

    public function export(Request $request)
    {
        return Excel::download(
            new CustomershippingExport(
                $request->start_date,
                $request->input('customerno')
            ),
            'shipping' . time() . '.xlsx'
        );
    }

    public function export2(Request $request)
    {
        //ไว้ทดสอบ html ก่อน export
        // $params = ['etd'=>$request->start_date,'customerno'=>$request->customerno];
        // $queryAll = Customershipping::where('excel_status','=','1')->whereRaw("DATE(etd)=?",[$request->start_date]);
        
        // $customershippings = $queryAll->where(function ($query) use ($params) {
        //     if (!empty($params['etd']))
        //         $customershippings = $query->whereRaw("DATE(etd)=?",[$params['etd']]);
        //     if (!empty($params['customerno']))
        //         $customershippings = $query->whereRaw("lower(customerno)=?",[strtolower($params['customerno'])]);
        //  })->orderByRaw('etd DESC, customerno ASC, ship_date DESC')->get();



        // $customerStats = [];
    
        // foreach ($customershippings as  $shipping) {
     
        //     // เก็บสถิติตามรหัสลูกค้า
        //     if (!isset($customerStats[$shipping->customerno])) {
        //         $boxCount = Customershipping::where('customerno', $shipping->customerno)
        //         ->where('excel_status', '1')
        //         ->whereRaw("DATE(etd)=?", [$request->start_date])
        //         ->count();
        //         // echo '$boxCount:'.$boxCount." customerno:".$shipping->customerno."<br>";
        //         $shipping->box_count = $boxCount;
        //         $customerStats[$shipping->customerno] = [
        //             'boxes' => $boxCount
        //         ];
                
        //     }else{
        //         // echo '$shippingCustomerNoBoxCount:'.$shippingCustomerNoBoxCount." customerno:".$shipping->customerno."<br>";
        //         $shipping->box_count = $customerStats[$shipping->customerno]['boxes'];
        //     }
        // }

        // return view('customershipping.export',[
        //     'customershippings'=>$customershippings
        // ]);


        return Excel::download(
            new CustomershippigHtmlExport(
                $request->start_date,
                $request->end_date,
                $request->input('customerno'),
                $request->status,
                $request->pay_status
            ),
            'shipping_html' . time() . '.xlsx'
        );
    }


    public function exportLabels($etd_date)
    {
        $shippings = Customershipping::where('excel_status', 1)
            ->whereRaw('DATE(etd) = ?', [$etd_date])
            ->get();

        if ($shippings->isEmpty()) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูลในรอบปิดตู้นี้');
        }

        $etdFormatted = Carbon::parse($etd_date)->format('d/m/Y');

        // Group by customerno
        $grouped = $shippings->groupBy('customerno');
        $labels = [];
        foreach ($grouped as $customerno => $items) {
            $deliveryType = 'รับเอง';
            $first = $items->first();
            if ($first && $first->delivery_type_id == 2) {
                $deliveryType = 'จัดส่ง';
            }
            $labels[] = [
                'qty' => $items->count(),
                'customerno' => $customerno,
                'etd' => $etdFormatted,
                'delivery_type' => $deliveryType,
            ];
        }

        // Sort by customerno
        usort($labels, function($a, $b) {
            return strcmp($a['customerno'], $b['customerno']);
        });

        // --- PhpWord: Generate .docx matching A15 template ---
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('TH SarabunPSK');
        $phpWord->setDefaultFontSize(1);

        // A15 page: 175mm x 212mm, margins: top=0, bottom=0, left=6mm, right=6mm
        // 1mm = 56.6929 twips
        $twip = function($mm) { return (int) round($mm * 56.6929); };

        $sectionStyle = [
            'pageSizeW' => $twip(175),
            'pageSizeH' => $twip(212),
            'marginTop' => $twip(0),
            'marginBottom' => $twip(0),
            'marginLeft' => $twip(6),
            'marginRight' => $twip(6),
        ];

        // Table dimensions from template: 7 rows x 3 cols
        // Label cells: 80mm wide x 50mm tall | Gap: 3mm
        $labelW = $twip(80);
        $labelH = $twip(50);
        $gapW = $twip(3);
        $gapH = $twip(3);

        $tableStyle = [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
        ];

        $labelCellStyle = [
            'width' => $labelW,
            'valign' => 'center',
        ];
        $gapColStyle = [
            'width' => $gapW,
            'valign' => 'center',
        ];

        $chunks = array_chunk($labels, 8);
        foreach ($chunks as $pageIndex => $page) {
            $section = $phpWord->addSection($sectionStyle);
            $table = $section->addTable($tableStyle);

            for ($row = 0; $row < 4; $row++) {
                // Label row
                $tableRow = $table->addRow($labelH, ['exactHeight' => true]);

                // Col 1 - label
                $idx = $row * 2;
                $cell = $tableRow->addCell($labelW, $labelCellStyle);
                if (isset($page[$idx])) {
                    $this->writeLabelContent($cell, $page[$idx]);
                }

                // Col 2 - gap
                $tableRow->addCell($gapW, $gapColStyle);

                // Col 3 - label
                $idx = $row * 2 + 1;
                $cell = $tableRow->addCell($labelW, $labelCellStyle);
                if (isset($page[$idx])) {
                    $this->writeLabelContent($cell, $page[$idx]);
                }

                // Gap row (except after last label row)
                if ($row < 3) {
                    $gapRow = $table->addRow($gapH, ['exactHeight' => true]);
                    $gapRow->addCell($labelW);
                    $gapRow->addCell($gapW);
                    $gapRow->addCell($labelW);
                }
            }
        }

        $filename = 'labels-etd-' . Carbon::parse($etd_date)->format('d-m-Y') . '.docx';
        $tempFile = storage_path('app/' . $filename);
        $phpWord->save($tempFile, 'Word2007');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Export Pile Labels สำหรับ ANW-820 — แยกกองตามผู้รับ (delivery_fullname)
     */
    public function exportPileLabels($etd_date)
    {
        $shippings = Customershipping::where('excel_status', 1)
            ->whereRaw('DATE(etd) = ?', [$etd_date])
            ->where('customerno', 'ANW-820')
            ->whereNotNull('box_no')->where('box_no', '!=', '')
            ->get();

        if ($shippings->isEmpty()) {
            return redirect()->back()->with('error', 'ไม่พบข้อมูล ANW-820 ในรอบปิดตู้นี้');
        }

        $etdFormatted = Carbon::parse($etd_date)->format('d/m/Y');

        $grouped = $shippings->groupBy('delivery_fullname');

        // Sort order — must match scanner (resources/views/scanner/pickup.blade.php):
        //   1. Normal recipients (binary strcmp ascending)
        //   2. SB-prefixed names (รับเอง — known)
        //   3. Empty / unknown names (รับเอง — ไม่ระบุผู้รับ) → very last
        $sortedKeys = $grouped->keys()->sort(function ($a, $b) {
            $aU = ($a === null || trim((string)$a) === '');
            $bU = ($b === null || trim((string)$b) === '');
            if ($aU && !$bU) return 1;
            if (!$aU && $bU) return -1;
            $aSB = str_starts_with((string)$a, 'SB ');
            $bSB = str_starts_with((string)$b, 'SB ');
            if ($aSB && !$bSB) return 1;
            if (!$aSB && $bSB) return -1;
            return strcmp((string)$a, (string)$b);
        })->values();

        $labels = [];
        $pileNum = 0;
        foreach ($sortedKeys as $name) {
            $items = $grouped[$name];
            $pileNum++;
            $isUnknown = ($name === null || trim((string)$name) === '');
            $isSB = !$isUnknown && str_starts_with((string)$name, 'SB ');
            $isSelfPickup = $isSB || $isUnknown;
            $labels[] = [
                'pile_num' => $pileNum,
                'qty' => $items->count(),
                'recipient' => $isUnknown ? '(ไม่ระบุผู้รับ)' : $name,
                'delivery_type' => $isSelfPickup ? 'รับเอง' : 'จัดส่งในไทย',
                'etd' => $etdFormatted,
            ];
        }

        // PhpWord: same A15 layout as exportLabels
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('TH SarabunPSK');
        $phpWord->setDefaultFontSize(1);

        $twip = function ($mm) { return (int) round($mm * 56.6929); };

        $sectionStyle = [
            'pageSizeW' => $twip(175),
            'pageSizeH' => $twip(212),
            'marginTop' => $twip(0),
            'marginBottom' => $twip(0),
            'marginLeft' => $twip(6),
            'marginRight' => $twip(6),
        ];

        $labelW = $twip(80);
        $labelH = $twip(50);
        $gapW = $twip(3);
        $gapH = $twip(3);

        $tableStyle = [
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'cellMargin' => 0,
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
        ];
        $labelCellStyle = ['width' => $labelW, 'valign' => 'center'];
        $gapColStyle = ['width' => $gapW, 'valign' => 'center'];

        $chunks = array_chunk($labels, 8);
        foreach ($chunks as $page) {
            $section = $phpWord->addSection($sectionStyle);
            $table = $section->addTable($tableStyle);

            for ($row = 0; $row < 4; $row++) {
                $tableRow = $table->addRow($labelH, ['exactHeight' => true]);

                $idx = $row * 2;
                $cell = $tableRow->addCell($labelW, $labelCellStyle);
                if (isset($page[$idx])) {
                    $this->writePileLabelContent($cell, $page[$idx]);
                }

                $tableRow->addCell($gapW, $gapColStyle);

                $idx = $row * 2 + 1;
                $cell = $tableRow->addCell($labelW, $labelCellStyle);
                if (isset($page[$idx])) {
                    $this->writePileLabelContent($cell, $page[$idx]);
                }

                if ($row < 3) {
                    $gapRow = $table->addRow($gapH, ['exactHeight' => true]);
                    $gapRow->addCell($labelW);
                    $gapRow->addCell($gapW);
                    $gapRow->addCell($labelW);
                }
            }
        }

        $filename = 'pile-labels-ANW820-' . Carbon::parse($etd_date)->format('d-m-Y') . '.docx';
        $tempFile = storage_path('app/' . $filename);
        $phpWord->save($tempFile, 'Word2007');

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    private function writePileLabelContent($cell, $label)
    {
        $center = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0];

        $cell->addText(
            'กอง ' . $label['pile_num'],
            ['size' => 36, 'bold' => true, 'color' => 'FF0000', 'name' => 'TH SarabunPSK'],
            $center
        );
        $cell->addText(
            'จำนวน ' . $label['qty'] . ' ชิ้น',
            ['size' => 14, 'color' => '0099CC', 'name' => 'TH SarabunPSK'],
            $center
        );
        $cell->addText(
            $label['recipient'],
            ['size' => 16, 'bold' => true, 'color' => '000000', 'name' => 'TH SarabunPSK'],
            $center
        );
        $cell->addText(
            $label['delivery_type'],
            ['size' => 14, 'bold' => true, 'color' => $label['delivery_type'] === 'รับเอง' ? 'DC2626' : '059669', 'name' => 'TH SarabunPSK'],
            $center
        );
    }

    private function writeLabelContent($cell, $label)
    {
        $center = ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0, 'spaceBefore' => 0];

        $cell->addText(
            'จำนวน ' . $label['qty'] . ' ชิ้น',
            ['size' => 14, 'color' => '0099CC', 'name' => 'TH SarabunPSK'],
            $center
        );
        $cell->addText(
            $label['customerno'],
            ['size' => 36, 'bold' => true, 'color' => 'FF0000', 'name' => 'Arial Black'],
            $center
        );
        $cell->addText(
            'รอบปิดตู้: ' . $label['etd'],
            ['size' => 11, 'color' => '555555', 'name' => 'TH SarabunPSK'],
            $center
        );
        $cell->addText(
            'สถานะ: ' . $label['delivery_type'],
            ['size' => 11, 'color' => '555555', 'italic' => true, 'name' => 'TH SarabunPSK'],
            $center
        );
    }

    protected function isImage($value)
    {
        // ตรวจสอบค่าว่าเป็น URL ของรูปภาพหรือไม่
        // โดยอาจใช้เงื่อนไขต่าง ๆ อื่น ๆ ตามลักษณะของข้อมูลที่คุณจะต้องการตรวจสอบ
        return filter_var($value, FILTER_VALIDATE_URL) && getimagesize($value);
    }
    
    public function checkExistingItemnos(Request $request)
    {
        $itemnos = $request->input('itemnos', []);
        $customernos = $request->input('customernos', []);
        
        if (empty($itemnos) || empty($customernos)) {
            return response()->json(['existingItems' => []]);
        }
        
        // เช็ค itemno ที่มีอยู่ใน customershipping แยกตาม customerno
        $existingItems = [];
        
        for ($i = 0; $i < count($itemnos); $i++) {
            $itemno = $itemnos[$i];
            $customerno = $customernos[$i];     
            
            if ($itemno && $customerno) {
                // เช็คว่า itemno + customerno นี้มีอยู่ในระบบแล้วหรือไม่
                $exists = Customershipping::where('itemno', $itemno)
                    ->where('customerno', $customerno)
                    ->where('excel_status', 1)
                    ->exists();
                
                if ($exists) {
                    $existingItems[] = [
                        'itemno' => $itemno,
                        'customerno' => $customerno
                    ];
                }
            }
        }
    
        
        return response()->json(['existingItems' => $existingItems]);
    }

    public function getCustomerDeliveryType(Request $request)
    {
        $customerno = $request->input('customerno');
        
        if ($customerno) {
            $customer = \App\User::where('customerno', $customerno)->first();
            
            if ($customer) {
                return response()->json([
                    'delivery_type_id' => $customer->delivery_type_id,
                    'name' => $customer->name,
                    'addr' => $customer->addr,
                    'province' => $customer->province,
                    'distrinct' => $customer->distrinct,
                    'subdistrinct' => $customer->subdistrinct,
                    'postcode' => $customer->postcode,
                    'mobile' => $customer->mobile
                ]);
            }
        }
        
        return response()->json(['delivery_type_id' => null]);
    }

    /**
     * Send LINE notification to customers for a specific ETD date
     */
    public function sendLineNotification(Request $request)
    {
        $request->validate([
            'etd' => 'required|date',
            'customer_nos' => 'required|array|min:1',
        ]);

        $etdDate = $request->input('etd');
        $customerNos = $request->input('customer_nos');
        $lineService = new LineMessagingService();
        $adminId = Auth::id();
        $results = ['success' => 0, 'failed' => 0, 'no_line' => 0, 'already_sent' => 0, 'details' => []];

        // หาจำนวนสินค้าของลูกค้าที่เลือกในรอบปิดตู้นี้
        $customers = Customershipping::where('excel_status', '1')
            ->whereDate('etd', $etdDate)
            ->whereIn('customerno', $customerNos)
            ->select('customerno', DB::raw('COUNT(*) as item_count'), DB::raw('MAX(shipping_method) as shipping_method'))
            ->groupBy('customerno')
            ->get();

        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบข้อมูลลูกค้าที่เลือกในรอบปิดตู้วันที่ ' . Carbon::parse($etdDate)->format('d/m/Y'),
            ]);
        }

        foreach ($customers as $customer) {
            $customerno = $customer->customerno;
            $itemCount = $customer->item_count;

            // ตรวจสอบว่าเคยส่งแจ้งเตือนสำเร็จแล้วหรือยัง
            $alreadySent = DB::table('line_notifications')
                ->where('customerno', $customerno)
                ->whereDate('etd', $etdDate)
                ->where('status', 'success')
                ->exists();

            if ($alreadySent) {
                $results['already_sent']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'already_sent',
                    'message' => 'เคยแจ้งเตือนแล้ว',
                ];
                continue;
            }

            // หา LINE user ID จาก users → my_auth_providers
            $user = User::where('customerno', $customerno)->first();
            if (!$user) {
                $results['no_line']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'no_user',
                    'message' => 'ไม่พบ user ในระบบ',
                ];
                continue;
            }

            $authProvider = MyAuthProvider::where('userid', $user->id)
                ->where('provider', 'line')
                ->first();

            if (!$authProvider) {
                $results['no_line']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'no_line',
                    'message' => 'ไม่มี LINE account',
                ];
                continue;
            }

            $lineUserId = $authProvider->providerid;
            $etdFormatted = Carbon::parse($etdDate)->format('d/m/Y');
            $viewUrl = 'https://skjjapanshipping.com/skjtrack/shippingview';

            // สร้างข้อความ Flex Message
            $shippingMethod = intval($customer->shipping_method ?? 1);
            $messages = $lineService->buildShippingNotification(
                $customerno,
                $etdFormatted,
                $itemCount,
                $viewUrl,
                $shippingMethod
            );

            // ส่ง push message
            $sent = $lineService->pushMessage($lineUserId, $messages);

            // บันทึก log
            DB::table('line_notifications')->insert([
                'customerno'    => $customerno,
                'etd'           => $etdDate,
                'line_user_id'  => $lineUserId,
                'item_count'    => $itemCount,
                'status'        => $sent ? 'success' : 'failed',
                'error_message' => $sent ? null : 'Push message failed',
                'sent_by'       => $adminId,
                'created_at'    => now(),
                'updated_at'    => now(),
            ]);

            if ($sent) {
                $results['success']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'success',
                    'message' => 'ส่งสำเร็จ',
                ];
            } else {
                $results['failed']++;
                $results['details'][] = [
                    'customerno' => $customerno,
                    'status' => 'failed',
                    'message' => 'ส่งไม่สำเร็จ',
                ];
            }
        }

        $etdFormatted = Carbon::parse($etdDate)->format('d/m/Y');
        return response()->json([
            'success' => true,
            'message' => "แจ้งเตือนรอบปิดตู้ {$etdFormatted} เสร็จสิ้น: สำเร็จ {$results['success']} ราย, ไม่สำเร็จ {$results['failed']} ราย, ไม่มี LINE {$results['no_line']} ราย" . ($results['already_sent'] > 0 ? ", เคยแจ้งแล้ว {$results['already_sent']} ราย" : ''),
            'results' => $results,
        ]);
    }

    /**
     * 📢 บรอดแคสข้อความให้ลูกค้า "ทั้งหมด" ในรอบปิดตู้ที่เลือก
     * ส่งผ่าน SKJ Chat (เหมือนระบบส่งบิล) — ไม่ต้องให้ลูกค้าเชื่อม LINE
     *
     * Body:
     *   etd              required date
     *   title            required string
     *   message          required string
     *   header_color     optional hex (#F59E0B)
     *   customer_nos     optional array — ถ้าระบุ จะบรอดแคสเฉพาะรหัสที่ส่งมา
     */
    public function broadcastEtdMessage(Request $request)
    {
        $request->validate([
            'etd'            => 'required|date',
            'title'          => 'required|string|max:80',
            'message'        => 'required|string|max:700',
            'header_color'   => 'nullable|string|max:9',
            'customer_nos'   => 'nullable|array',
            'customer_nos.*' => 'string|max:50',
        ]);

        $etdDate     = $request->input('etd');
        $title       = trim($request->input('title'));
        $message     = trim($request->input('message'));
        $headerColor = $request->input('header_color') ?: '#F59E0B';
        $filterNos   = collect($request->input('customer_nos', []))
            ->map(fn($c) => trim($c))
            ->filter()
            ->unique()
            ->values();

        $adminId = Auth::id();

        // ดึง customer ที่มีพัสดุในรอบปิดตู้นี้ (group by customerno)
        $query = Customershipping::where('excel_status', '1')
            ->whereDate('etd', $etdDate);

        if ($filterNos->isNotEmpty()) {
            $query->whereIn('customerno', $filterNos);
        }

        $customers = $query
            ->select('customerno', DB::raw('COUNT(*) as item_count'), DB::raw('MAX(shipping_method) as shipping_method'))
            ->groupBy('customerno')
            ->get();

        if ($customers->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'ไม่พบลูกค้าในรอบปิดตู้วันที่ ' . Carbon::parse($etdDate)->format('d/m/Y'),
            ]);
        }

        $etdFormatted = Carbon::parse($etdDate)->format('d/m/Y');

        // เรียก SKJ Chat API — broadcast ทั้ง batch ในครั้งเดียว
        $payload = [
            'etd'         => $etdDate,
            'etdDisplay'  => $etdFormatted,
            'title'       => $title,
            'message'     => $message,
            'headerColor' => $headerColor,
            'customers'   => $customers->map(fn($c) => [
                'customerno'     => $c->customerno,
                'itemCount'      => (int) $c->item_count,
                'shippingMethod' => (int) ($c->shipping_method ?? 1),
            ])->values()->toArray(),
        ];

        $chatBaseUrl = rtrim((string) config('services.skjchat.base_url'), '/');
        $chatApiKey  = (string) config('services.skjchat.tracking_key', config('services.skjchat.api_key', ''));

        if (!$chatBaseUrl || !$chatApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'ระบบ SKJ Chat ยังไม่ได้ตั้งค่า (services.skjchat.base_url / tracking_key)',
            ], 500);
        }

        $results = ['success' => 0, 'failed' => 0, 'no_contact' => 0, 'details' => []];

        try {
            $response = \Illuminate\Support\Facades\Http::timeout(60)
                ->withHeaders(['X-API-Key' => $chatApiKey])
                ->asJson()
                ->post($chatBaseUrl . '/api/etd-broadcast', $payload);

            $data = $response->json();

            if (!$response->successful() || empty($data['success'])) {
                $errMsg = $data['error'] ?? $data['message'] ?? ('HTTP ' . $response->status());
                Log::error('[broadcastEtdMessage] SKJ Chat error', [
                    'status' => $response->status(),
                    'body'   => substr((string) $response->body(), 0, 500),
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'ส่งบรอดแคสไม่สำเร็จ: ' . $errMsg,
                ]);
            }

            foreach (($data['results'] ?? []) as $r) {
                $cn = $r['customerno'] ?? '';
                $st = $r['status'] ?? 'failed';
                $msg = '';
                if ($st === 'sent') {
                    $results['success']++;
                    $platform = $r['platform'] ?? '';
                    $msg = 'ส่งสำเร็จ' . ($platform ? " ({$platform})" : '');
                } elseif ($st === 'no_contact') {
                    $results['no_contact']++;
                    $msg = $r['error'] ?? 'ไม่พบ contact ในแชท';
                } else {
                    $results['failed']++;
                    $msg = $r['error'] ?? 'ส่งไม่สำเร็จ';
                }

                $results['details'][] = [
                    'customerno' => $cn,
                    'status'     => $st === 'sent' ? 'success' : ($st === 'no_contact' ? 'no_contact' : 'failed'),
                    'message'    => $msg,
                ];

                // log
                try {
                    DB::table('line_notifications')->insert([
                        'customerno'    => $cn,
                        'etd'           => $etdDate,
                        'line_user_id'  => $r['platform'] ?? null,
                        'item_count'    => 0,
                        'status'        => $st === 'sent' ? 'success' : 'failed',
                        'error_message' => '[broadcast:' . $st . '] ' . substr($title, 0, 60) . ($msg ? ' - ' . substr($msg, 0, 100) : ''),
                        'sent_by'       => $adminId,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                } catch (\Throwable $e) {
                    Log::warning('[broadcastEtdMessage] log insert failed', ['error' => $e->getMessage()]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('[broadcastEtdMessage] exception', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'ติดต่อ SKJ Chat ไม่ได้: ' . $e->getMessage(),
            ], 500);
        }

        $summary = "บรอดแคสรอบปิดตู้ {$etdFormatted} เสร็จสิ้น: สำเร็จ {$results['success']} ราย"
            . ", ไม่สำเร็จ {$results['failed']} ราย"
            . ", ไม่พบในแชท {$results['no_contact']} ราย";

        return response()->json([
            'success' => true,
            'message' => $summary,
            'results' => $results,
        ]);
    }

    /**
     * Get distinct recipient names for admin filter dropdown
     */
    public function getAdminRecipients(Request $request)
    {
        $query = Customershipping::where('excel_status', 1);

        if (!empty($request->etd)) {
            $query->whereRaw('DATE(etd) = ?', [$request->etd]);
        }

        if (!empty($request->search)) {
            $searchTerm = '%'.$request->search.'%';
            $query->whereRaw("customerno like ?", [$searchTerm]);
        }

        $rows = (clone $query)->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
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
     * สรุปสถานะส่งในไทย ต่อรอบปิดตู้ — แยกตามรหัสลูกค้า
     */
    public function fetchThaiShippingSummary(Request $request)
    {
        $etd = $request->input('etd');
        if (!$etd) {
            return response()->json(['success' => false, 'message' => 'กรุณาเลือกรอบปิดตู้']);
        }

        $items = Customershipping::where('excel_status', '1')
            ->whereDate('etd', $etd)
            ->select('customerno', 'delivery_type_id', 'thai_bill_status', 'status')
            ->get();

        $grouped = $items->groupBy('customerno');
        $customers = [];

        foreach ($grouped as $customerno => $rows) {
            $total = $rows->count();

            // delivery_type_id=1 → รับเอง
            $pickupAll = $rows->where('delivery_type_id', 1);
            $pickupDone = $pickupAll->where('status', 4)->count();   // สำเร็จ = รับแล้ว
            $pickupWait = $pickupAll->count() - $pickupDone;          // ถึงไทย ยังไม่มารับ

            // delivery_type_id!=1 → ต้องส่งในไทย
            $needShip = $rows->where('delivery_type_id', '!=', 1)->count();
            $billed = $rows->filter(function ($r) {
                return $r->delivery_type_id != 1 && $r->thai_bill_status >= 1;
            })->count();

            // สรุปจำนวนที่เสร็จแล้ว = รับเองสำเร็จ + ส่งแล้วมีบิล
            $doneItems = $pickupDone + $billed;
            // จำนวนที่ยังค้าง = รอรับเอง + รอทำส่ง
            $pendingItems = $pickupWait + ($needShip - $billed);

            if ($pendingItems == 0) {
                $status = 'done';       // เสร็จหมด (รับ+ส่งครบ)
            } elseif ($doneItems > 0) {
                $status = 'partial';    // ทำไปบางส่วน
            } else {
                $status = 'pending';    // ยังไม่ดำเนินการเลย
            }

            $customers[] = [
                'customerno' => $customerno,
                'total' => $total,
                'pickup' => $pickupAll->count(),
                'pickup_done' => $pickupDone,
                'pickup_wait' => $pickupWait,
                'need_ship' => $needShip,
                'billed' => $billed,
                'status' => $status,
            ];
        }

        // Sort: pending → partial → done — ภายในกลุ่มเรียงตาม customerno
        usort($customers, function ($a, $b) {
            $order = ['pending' => 0, 'partial' => 1, 'done' => 2];
            $statusCmp = ($order[$a['status']] ?? 3) - ($order[$b['status']] ?? 3);
            if ($statusCmp !== 0) return $statusCmp;
            return strnatcasecmp($a['customerno'], $b['customerno']);
        });

        $totalCustomers = count($customers);
        $doneCount = count(array_filter($customers, fn($c) => $c['status'] === 'done'));
        $partialCount = count(array_filter($customers, fn($c) => $c['status'] === 'partial'));
        $pendingCount = count(array_filter($customers, fn($c) => $c['status'] === 'pending'));

        return response()->json([
            'success' => true,
            'summary' => [
                'total_customers' => $totalCustomers,
                'done' => $doneCount,
                'partial' => $partialCount,
                'pending' => $pendingCount,
            ],
            'customers' => $customers,
        ]);
    }
}
