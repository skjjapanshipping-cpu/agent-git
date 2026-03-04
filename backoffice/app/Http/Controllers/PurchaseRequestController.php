<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\Boss;
use App\Models\Customer;
use App\Models\Dailyrate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;

class PurchaseRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware(['role:admin']);
    }

    /**
     * Display listing
     */
    public function index()
    {
        return view('purchase-request.index');
    }

    /**
     * DataTables AJAX
     */
    public function fetch(Request $request)
    {
        if ($request->ajax()) {
            $query = PurchaseRequest::latest('created_at');

            if (!empty($request->search)) {
                $search = '%' . $request->search . '%';
                $query->where(function ($q) use ($search) {
                    $q->where('request_no', 'like', $search)
                      ->orWhere('customerno', 'like', $search)
                      ->orWhere('product_title', 'like', $search)
                      ->orWhere('product_url', 'like', $search)
                      ->orWhere('purchase_ref', 'like', $search);
                });
            }

            if ($request->status !== null && $request->status !== '') {
                $query->where('status', $request->status);
            }

            if (!empty($request->start_date)) {
                $query->whereDate('created_at', '>=', $request->start_date);
            }
            if (!empty($request->end_date)) {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            return DataTables::of($query)
                ->addColumn('action_edit', function ($row) {
                    return route('purchase-requests.edit', $row->id);
                })
                ->addColumn('action_del', function ($row) {
                    return route('purchase-requests.destroy', $row->id);
                })
                ->addColumn('status_label', function ($row) {
                    return $row->status_label;
                })
                ->addColumn('status_color', function ($row) {
                    return $row->status_color;
                })
                ->addColumn('boss_name', function ($row) {
                    return $row->boss ? $row->boss->name : '-';
                })
                ->rawColumns(['action_edit', 'action_del'])
                ->make(true);
        }
    }

    /**
     * Show create form
     */
    public function create()
    {
        $bosses = Boss::all();
        $requestNo = PurchaseRequest::generateRequestNo();
        return view('purchase-request.create', compact('bosses', 'requestNo'));
    }

    /**
     * Store new purchase request
     */
    public function store(Request $request)
    {
        $request->validate([
            'customerno' => 'required|string',
            'product_url' => 'required|url',
            'quantity' => 'required|integer|min:1',
        ]);

        $data = $request->all();
        $data['request_no'] = PurchaseRequest::generateRequestNo();
        $data['site'] = PurchaseRequest::detectSite($request->product_url);
        $data['admin_id'] = Auth::id();

        if (empty($data['status'])) {
            $data['status'] = PurchaseRequest::STATUS_PENDING;
        }

        PurchaseRequest::create($data);

        return redirect()->route('purchase-requests.index')
            ->with('success', 'สร้างคำขอสั่งซื้อเรียบร้อยแล้ว');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);
        $bosses = Boss::all();
        $statuses = PurchaseRequest::$statusLabels;
        return view('purchase-request.edit', compact('purchaseRequest', 'bosses', 'statuses'));
    }

    /**
     * Update purchase request
     */
    public function update(Request $request, $id)
    {
        $purchaseRequest = PurchaseRequest::findOrFail($id);

        $request->validate([
            'customerno' => 'required|string',
            'product_url' => 'required|url',
            'quantity' => 'required|integer|min:1',
        ]);

        $data = $request->all();
        $data['site'] = PurchaseRequest::detectSite($request->product_url);

        // If status changed to purchased and no rate set, use current rate
        if ($request->status == PurchaseRequest::STATUS_PURCHASED && empty($data['rate'])) {
            $data['rate'] = Dailyrate::curRatePerBath();
        }

        $purchaseRequest->update($data);

        return redirect()->route('purchase-requests.index')
            ->with('success', 'อัปเดตคำขอสั่งซื้อเรียบร้อยแล้ว');
    }

    /**
     * Delete purchase request
     */
    public function destroy($id)
    {
        PurchaseRequest::findOrFail($id)->delete();

        return redirect()->route('purchase-requests.index')
            ->with('success', 'ลบคำขอสั่งซื้อเรียบร้อยแล้ว');
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'status' => 'required|integer|between:0,6',
        ]);

        PurchaseRequest::whereIn('id', $request->ids)
            ->update(['status' => $request->status]);

        return response()->json(['success' => true]);
    }
}
