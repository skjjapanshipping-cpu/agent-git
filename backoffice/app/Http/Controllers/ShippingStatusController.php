<?php

namespace App\Http\Controllers;

use App\Models\ShippingStatus;
use Illuminate\Http\Request;

/**
 * Class ShippingStatusController
 * @package App\Http\Controllers
 */
class ShippingStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $shippingStatuses = ShippingStatus::paginate();

        return view('shipping-status.index', compact('shippingStatuses'))
            ->with('i', (request()->input('page', 1) - 1) * $shippingStatuses->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $shippingStatus = new ShippingStatus();
        return view('shipping-status.create', compact('shippingStatus'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(ShippingStatus::$rules);

        $shippingStatus = ShippingStatus::create($request->all());

        return redirect()->route('shipping-statuses.index')
            ->with('success', 'สร้างสถานะการจัดส่งสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $shippingStatus = ShippingStatus::find($id);

        return view('shipping-status.show', compact('shippingStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $shippingStatus = ShippingStatus::find($id);

        return view('shipping-status.edit', compact('shippingStatus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  ShippingStatus $shippingStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, ShippingStatus $shippingStatus)
    {
        request()->validate(ShippingStatus::$rules);

        $shippingStatus->update($request->all());

        return redirect()->route('shipping-statuses.index')
            ->with('success', 'อัปเดตสถานะการจัดส่งสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $shippingStatus = ShippingStatus::findOrFail($id);
        $shippingStatus->delete();

        return redirect()->route('shipping-statuses.index')
            ->with('success', 'ลบสถานะการจัดส่งสำเร็จ');
    }
}
