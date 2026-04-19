<?php

namespace App\Http\Controllers;

use App\Models\Customerorder;
use App\Models\Customershipping;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        if(auth()->user()->hasRole('admin')) {
            $pendingPayments = $this->getPendingPayments();
            return view('home', compact('pendingPayments'));
        } else {
            return redirect()->route('shipment-analytics');
        }
    }

    private function getPendingPayments()
    {
        $rows = DB::table('customershippings')
            ->select(
                'customerno',
                'etd',
                DB::raw("COUNT(*) as item_count"),
                DB::raw("SUM(CASE WHEN pay_status = 5 THEN COALESCE(import_cost, 0) + (COALESCE(cod, 0) * COALESCE(cod_rate, 0.25)) ELSE 0 END) as pending_import"),
                DB::raw("SUM(CASE WHEN thai_bill_status = 1 THEN COALESCE(thai_bill_amount, 0) ELSE 0 END) as pending_thai"),
                DB::raw("MAX(CASE WHEN pay_status = 5 THEN 1 ELSE 0 END) as has_import_pending"),
                DB::raw("MAX(CASE WHEN thai_bill_status = 1 THEN 1 ELSE 0 END) as has_thai_pending")
            )
            ->where('excel_status', '1')
            ->where(function ($q) {
                $q->where('pay_status', 5)
                  ->orWhere('thai_bill_status', 1);
            })
            ->groupBy('customerno', 'etd')
            ->orderByDesc('etd')
            ->orderBy('customerno')
            ->get();

        return $rows;
    }
}
