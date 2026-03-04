<?php

namespace App\Http\Controllers;

use App\Models\Customerorder;
use App\Models\PayStatus;
use App\Models\ShippingStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Yajra\DataTables\DataTables;

class CustomerviewController extends Controller
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
        return view('customerview.index');
    }

    public function fetchCustomerorder(Request $request)
    {
        if ($request->ajax()) {
            $authUser = Auth::user();

            $queryAll = Customerorder::where('customerno', $authUser->customerno)
                ->latest('created_at');

            if (!empty($request->search['value'])) {
                $searchValue = $request->search['value'];
                $searchValueNoHyphens = str_replace('-', '', $searchValue);

                $queryAll->where(function ($query) use ($searchValue, $searchValueNoHyphens) {
                    $query->whereRaw("link LIKE ?", ['%' . $searchValue . '%'])
                        ->orWhereRaw("note LIKE ?", ['%' . $searchValue . '%'])
                        ->orWhereRaw("REPLACE(tracking_number, '-', '') LIKE ?", ['%' . $searchValueNoHyphens . '%']);
                });
            }

            return DataTables::of($queryAll)
                ->addColumn('row_number', function ($row) {
                    return '';
                })
                ->addColumn('image', function ($row) {
                    return '<img src="' . asset(config('app.upload_url') . '/' . $row->image_link) . '" class="img-thumbnail" width="50" height="50" onclick="showImage(\'' . asset(config('app.upload_url') . '/' . $row->image_link) . '\')" style="cursor: pointer;" />';
                })
                ->addColumn('status_name', function ($row) {
                    return PayStatus::getNameById($row->status);
                })
                ->addColumn('order_date_formatted', function ($row) {
                    return $row->order_date ? \Carbon\Carbon::parse($row->order_date)->format('d/m/Y') : '';
                })
                ->addColumn('link_display', function ($row) {
                    return '<div class="link-cell" title="' . $row->link . '">
                                <button class="btn btn-sm btn-outline-secondary copy-link d-none" data-clipboard-text="' . $row->link . '">คัดลอก</button>
                                <a href="' . $row->link . '" target="_blank">
                                    <span class="domain-name" data-url="' . $row->link . '"></span>
                                </a>
                            </div>';
                })
                ->addColumn('quantity_formatted', function ($row) {
                    return number_format($row->quantity, 0, '.', ',') .
                        '<input type="hidden" class="quantity" value="' . $row->quantity . '" />';
                })
                ->addColumn('product_cost_yen_formatted', function ($row) {
                    return number_format($row->product_cost_yen, 2, '.', ',') .
                        '<input type="hidden" class="product_cost_yen" value="' . $row->product_cost_yen . '" />';
                })
                ->addColumn('rateprice_formatted', function ($row) {
                    return number_format($row->rateprice, 3, '.', ',') .
                        '<input type="hidden" class="rateprice" value="' . $row->rateprice . '" />';
                })
                ->addColumn('product_cost_baht_formatted', function ($row) {
                    return number_format($row->product_cost_baht, 2, '.', ',') .
                        '<input type="hidden" class="product_cost_baht" value="' . $row->product_cost_baht . '" />';
                })
                ->addColumn('tracking_number_display', function ($row) {
                    return $row->tracking_number ?? 'รอดำเนินการ';
                })
                ->addColumn('cutoff_date_formatted', function ($row) {
                    return $row->cutoff_date ? \Carbon\Carbon::parse($row->cutoff_date)->format('d/m/Y') : 'รอดำเนินการ';
                })
                ->addColumn('shipping_status_name', function ($row) {
                    return ShippingStatus::getNameById($row->shipping_status);
                })
                ->filterColumn('customerno', function ($query, $keyword) {
                    // Do nothing - disable column filtering
                })
                ->filterColumn('note', function ($query, $keyword) {
                    // Do nothing - disable column filtering
                })
                ->rawColumns(['image', 'link_display', 'quantity_formatted', 'product_cost_yen_formatted', 'rateprice_formatted', 'product_cost_baht_formatted', 'shipping_status_name'])
                ->make(true);
        }
    }
}
