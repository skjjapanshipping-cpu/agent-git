<?php

namespace App\Http\Controllers;

use App\Exports\CustomerorderHtmlExport;
use App\Models\Boss;
use App\Models\Category;
use App\Models\Customerorder;
use App\Models\DeliveryType;
use App\Models\PayStatus;
use App\Models\ShippingStatus;
use App\Models\SupplierStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

/**
 * Class CustomerorderController
 * @package App\Http\Controllers
 */
class CustomerorderController extends Controller
{
    public function __construct() {
        $this->middleware(['role:admin']);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return view('customerorder.index');
    }

    public function fetchCustomerorder(Request $request)
    {


        if ($request->ajax()) {
            $sqlQuery='';
            $queryAll = Customerorder::latest('created_at');

            if(!empty($request->start_date)) {
                session(['startdate' => $request->start_date]);
            }
            else
                session()->forget('startdate');

            if (!empty($request->search) || !empty($request->status)|| !empty($request->shipping_status)|| !empty($request->supplier_status_id)|| !empty($request->boss_id)||!empty($request->start_date)|| (!empty($request->start_date) && !empty($request->end_date))) {
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
                                ->orWhereRaw("link like ?", [$searchTerm])
                                    ->orWhereRaw("DATE_FORMAT(order_date, '%d/%m/%Y') like ?", [$searchTerm])
                                    ->orWhereRaw("REPLACE(tracking_number, '-', '') LIKE ?", [$searchNoHyphens]);
                            });
                        }
                        if (!empty($request->status))
                            $query->where('status', $request->status);

                        if (!empty($request->shipping_status))
                            $query->where('shipping_status', $request->shipping_status);

                        if (!empty($request->supplier_status_id))
                            $query->where('supplier_status_id', $request->supplier_status_id);

                        if (!empty($request->boss_id))
                            $query->where('boss_id', $request->boss_id);

                        if (!empty($request->start_date) && !empty($request->end_date)) {
                            // ค้นหาจากวันที่ (DATE) โดยใช้ DATE() function
                            $query->whereRaw('DATE(order_date) >= ?', [$request->start_date])
                                  ->whereRaw('DATE(order_date) <= ?', [$request->end_date]);
                        } else if (!empty($request->start_date)) {
                            // ค้นหาจากวันที่ (DATE) เฉพาะวันเดียว
                            $query->whereRaw('DATE(order_date) = ?', [$request->start_date]);
                        }


                    })->orderByRaw('customerno asc')->take(2000)->get();


                    $sqlQuery = $queryAll->toSql();
                }
            } else {
                $data = $queryAll->orderByRaw('customerno asc')->take(2000)->get();

            }
            $sqlQuery = $queryAll->toSql();

            $sumpayprice =0;
            foreach ($data as $customerorder) {
                if($customerorder->status==1){
                    $sumpayprice+=$customerorder->product_cost_baht??0;
                }

            }

            $payprice =$sumpayprice;
            $totalprice =$data->sum('product_cost_baht');
//           dd($sqlQuery,$data->toArray());
            // สร้าง export link
            $exportParams = [];
            if (!empty($request->start_date)) $exportParams['start_date'] = $request->start_date;
            if (!empty($request->end_date)) $exportParams['end_date'] = $request->end_date;
            if (!empty($request->search)) $exportParams['customerno'] = $request->search;
            if (!empty($request->status)) $exportParams['status'] = $request->status;
            if (!empty($request->shipping_status)) $exportParams['shipping_status'] = $request->shipping_status;
            
            $exportLink = route('customerorderexport2') . (!empty($exportParams) ? '?' . http_build_query($exportParams) : '');

            return Datatables::of($data)
                ->addColumn('action_del', function($row) {
                    return route('customerorders.destroy', $row->id);
                })
                ->addColumn('edit_url', function($row) {
                    return route('customerorders.edit', $row->id);
                })
                ->addColumn('shipping_status', function($row) {
                    return ShippingStatus::getNameById($row->shipping_status);
                })->addColumn('status', function($row) {

                    return PayStatus::getNameById($row->status);
                })->addColumn('supplier_status', function($row) {
                    return SupplierStatus::getNameById($row->supplier_status_id);
                })->addColumn('note_admin', function($row) {
                    return $row->note_admin ?? '-';
                })->addColumn('boss', function($row) {
                    return Boss::getNameById($row->boss_id);
                })->addColumn('category', function($row) {
                    return Category::getNameById($row->category);
                })
                ->with(['payprice' => number_format($payprice, 2, '.', ',')
                    ,'totalprice'=>number_format($totalprice, 2, '.', ',')
                    ,'query'=>$sqlQuery
                    ,'last_search'=>$request->search??''
                    ,'data_export_link'=>$exportLink

                ])// แสดงผลรวมของค่า COD])
                ->make(true);


        }
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customerorder = new Customerorder();
        return view('customerorder.create', compact('customerorder'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validation สำหรับสร้างข้อมูลใหม่
        $rules = Customerorder::$rules;
        // ถ้ามีรูปจาก URL หรือไม่ได้อัพโหลดไฟล์ → เอา image_link ออกจาก validation เลย
        if ($request->filled('fetched_image_url') || !$request->hasFile('image_link')) {
            unset($rules['image_link']);
        } else {
            $rules['image_link'] = ['nullable', 'mimes:jpeg,bmp,png,PNG,JPG,jpg,JPEG', 'max:9000'];
        }
        request()->validate($rules);


            $imageName = null;
            if ($request->hasFile('image_link')) {
                $uploadUrl = config('app.upload_url');
                $image = $request->file('image_link');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move($uploadUrl, $imageName);
            } elseif ($request->filled('fetched_image_url')) {
                // Download image from fetched URL
                try {
                    $imageUrl = $request->input('fetched_image_url');
                    $uploadUrl = config('app.upload_url');
                    $ext = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                    $ext = preg_replace('/[^a-zA-Z]/', '', substr($ext, 0, 4)) ?: 'jpg';
                    $imageName = time() . '_fetched.' . $ext;

                    $client = new \GuzzleHttp\Client(['timeout' => 15, 'verify' => false]);
                    $response = $client->get($imageUrl);
                    file_put_contents($uploadUrl . '/' . $imageName, $response->getBody());
                } catch (\Exception $e) {
                    Log::error('ดาวน์โหลดรูปจาก URL ไม่สำเร็จ: ' . $e->getMessage(), ['url' => $imageUrl ?? '']);
                    $imageName = null;
                }
            }

            $customerorder = Customerorder::create(array_merge($request->all(), ['image_link' => $imageName
                ,'customerno'=> str_replace(' ','',$request->customerno)]));

            return redirect()->route('customerorders.index')
                ->with('success', 'อัพเดทข้อมูลการสั่งซื้อสำเร็จ')
                ->with('search', str_replace(' ', '', $request->customerno));

    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customerorder = Customerorder::find($id);

        return view('customerorder.show', compact('customerorder'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customerorder = Customerorder::find($id);



        return view('customerorder.edit', compact('customerorder'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Customerorder $customerorder
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customerorder $customerorder)
    {
        // Validation สำหรับแก้ไข - บังคับให้อัพโหลดรูปภาพเฉพาะตอนที่ไม่มีรูปภาพเดิม
        $rules = Customerorder::$rules;
        if (empty($customerorder->image_link)) {
            $rules['image_link'] = ['required', 'mimes:jpeg,bmp,png,PNG,JPG,jpg,JPEG', 'max:9000'];
        } else {
            $rules['image_link'] = ['sometimes', 'mimes:jpeg,bmp,png,PNG,JPG,jpg,JPEG', 'max:9000'];
        }
        request()->validate($rules);

        $imageName = $customerorder->image_link; // เก็บชื่อรูปภาพเดิมไว้เพื่อใช้ในกรณีที่ไม่มีการเปลี่ยนแปลงรูปภาพ

        if ($request->hasFile('image_link')) {
            $uploadUrl = config('app.upload_url');

            // ถ้ามีการอัปโหลดรูปภาพใหม่
            $image = $request->file('image_link');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $image->move($uploadUrl, $imageName);

            // ตรวจสอบและลบรูปภาพเก่า (หากมี)
            if (!empty($customerorder->image_link)) {
                $oldImagePath = $uploadUrl.'/' . $customerorder->image_link;
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }
        }

        // อัปเดตข้อมูล Customerorder
        $customerorder->update(array_merge($request->all(), ['image_link' => $imageName
            ,'customerno'=> str_replace(' ','',$request->customerno)]));

        return redirect()->route('customerorders.index')
            ->with('success', 'อัพเดทข้อมูลการสั่งซื้อสำเร็จ')->with('search',$request->customerno);
    }


    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $customerorder = Customerorder::find($id)->delete();

        return redirect()->route('customerorders.index')
            ->with('success', 'ลบรายการสั่งซื้อสำเร็จ');
    }

    public function update_StatusByIDs(Request $request){
//Destination Date
        $Ids = explode(',', $request->input('track_ids'));

        try{

            Customerorder::whereIn('id', $Ids)->update([
                'shipping_status' => 3
            ]);//shipping_status
            return redirect()->route('customerorders.index')
                ->with('success', 'อัปเดตสินค้าถึงไทยเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return redirect()->route('customerorders.index')
                ->with('error', 'อัปเดตสินค้าถึงไทยไม่สำเร็จ');
        }
    }


    //status-pay
    public function update_StatusByIDs2(Request $request){
//Destination Date
        $Ids = explode(',', $request->input('track_ids2'));

        try{

            Customerorder::whereIn('id', $Ids)->update([
                'status' => 2
            ]);//shipping_status
            return redirect()->route('customerorders.index')
                ->with('success', 'อัปเดตสินค้าชำระเงินแล้ว');

        } catch (\Exception $e) {
            return redirect()->route('customerorders.index')
                ->with('error', 'อัปเดตสินค้าชำระเงินไม่สำเร็จ');
        }
    }

    //supplier-status-pay
    public function update_SupplierStatusByIDs(Request $request){
        $Ids = explode(',', $request->input('track_ids3'));

        try{
            Customerorder::whereIn('id', $Ids)->update([
                'supplier_status_id' => 3 // จ่ายแล้ว
            ]);
            return redirect()->route('customerorders.index')
                ->with('success', 'อัปเดตสถานะ Supplier ชำระเงินแล้ว');

        } catch (\Exception $e) {
            return redirect()->route('customerorders.index')
                ->with('error', 'อัปเดตสถานะ Supplier ชำระเงินไม่สำเร็จ');
        }
    }

    public function getNewItemno(Request $request){
        $customerno =$request->input('customerno');
        return Customerorder::newItemno($customerno);
    }
    
    public function getAvailableItemno(Request $request){
        $customerno = $request->input('customerno');
        $skipCount = (int) $request->input('skip_count', 0); // จำนวนครั้งที่ข้าม
        
        // หาหมายเลขที่ใช้อยู่ทั้งหมด
        $usedItemnos = Customerorder::where('customerno', $customerno)
            ->pluck('itemno')
            ->map(function($itemno) {
                return (int) $itemno;
            })
            ->sort()
            ->values()
            ->toArray();
        
        // หาหมายเลขที่ว่างอยู่ (จากเลขมากสุดไปเลขน้อย)
        $availableItemno = null;
        $foundCount = 0; // นับจำนวนเลขว่างที่พบ
        
        if (!empty($usedItemnos)) {
            $maxUsed = max($usedItemnos);
            
            // หาจากเลขมากสุดลงไป (ไม่รวมเลขล่าสุดที่ยังไม่เคยใช้)
            for ($i = $maxUsed - 1; $i >= 1; $i--) {
                if (!in_array($i, $usedItemnos)) {
                    if ($foundCount === $skipCount) {
                        $availableItemno = str_pad($i, 4, '0', STR_PAD_LEFT);
                        break;
                    }
                    $foundCount++;
                }
            }
        }
        
        // ถ้าไม่มีเลขว่าง ให้ใช้เลขล่าสุด + 1 (เฉพาะเมื่อ skipCount = 0)
        if (!$availableItemno && $skipCount === 0) {
            $nextItemno = !empty($usedItemnos) ? max($usedItemnos) + 1 : 1;
            $availableItemno = str_pad($nextItemno, 4, '0', STR_PAD_LEFT);
        }
        
        // ส่งข้อมูลหมายเลขที่ใช้อยู่ทั้งหมดกลับไปด้วย
        return response()->json([
            'availableItemno' => $availableItemno,
            'usedItemnos' => $usedItemnos,
            'foundCount' => $foundCount
        ]);
    }
    
    public function checkItemnoExists(Request $request){
        $customerno = $request->input('customerno');
        $itemno = $request->input('itemno');
        $excludeId = $request->input('exclude_id');
        
        // ใช้ CAST เปรียบเทียบเป็นตัวเลข เพื่อให้ "0505" ตรงกับ "505" หรือ 505
        $query = Customerorder::where('customerno', $customerno)
            ->whereRaw('CAST(itemno AS UNSIGNED) = ?', [(int) $itemno]);
        
        // ถ้ามี exclude_id (การแก้ไข) ให้ยกเว้น ID ตัวเอง
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }
        
        $exists = $query->exists();
        
        return response()->json($exists);
    }
    
    public function checkCustomerorderExists(Request $request)
    {
        $itemnos = $request->input('itemnos', []);
        $customernos = $request->input('customernos', []);
        
        if (empty($itemnos) || empty($customernos)) {
            return response()->json(['missingItemnos' => []]);
        }
        
        // เช็ค itemno ที่ไม่มีใน customerorder แยกตาม customerno
        $missingItemnos = [];
        
        for ($i = 0; $i < count($itemnos); $i++) {
            $itemno = $itemnos[$i];
            $customerno = $customernos[$i];     
            
            if ($itemno && $customerno) {
                $exists = Customerorder::where('itemno', $itemno)
                    ->where('customerno', $customerno)
                    ->exists();
                
                if (!$exists) {
                    $missingItemnos[] = [
                        'itemno' => $itemno,
                        'customerno' => $customerno
                    ];
                }
            }
        }
    
        return response()->json(['missingItemnos' => $missingItemnos]);
    }

    public function export2(Request $request)
    {
        // อัพเดทสถานะ supplier_status_id จาก 1 (รอตรวจสอบ) เป็น 2 (รอโอน) สำหรับรายการที่เลือก
        if (!empty($request->customerorder_ids)) {
            $ids = is_array($request->customerorder_ids) 
                ? $request->customerorder_ids 
                : explode(',', $request->customerorder_ids);
            
                // dd($ids);
            // อัพเดทเฉพาะรายการที่มี supplier_status_id = 1 (รอตรวจสอบ) เป็น 2 (รอโอน)
            Customerorder::whereIn('id', $ids)
                ->where('supplier_status_id', 1)
                ->update(['supplier_status_id' => 2]);

            // dd(Customerorder::whereIn('id', $ids)->get()->toArray());
        }
        
        $includeImage = $request->has('include_image') && $request->include_image == '1';
        
        return Excel::download(
            new CustomerorderHtmlExport(
                $request->start_date ?? '',
                $request->end_date ?? '',
                $request->customerno ?? '',
                $request->status ?? '',
                $request->shipping_status ?? '',
                $request->customerorder_ids ?? null,
                $includeImage
            ),
            'Buyer_data_' . date('d-m-Y') . '.xlsx'
        );
    }
}
