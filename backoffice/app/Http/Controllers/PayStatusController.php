<?php

namespace App\Http\Controllers;

use App\Models\PayStatus;
use Illuminate\Http\Request;

/**
 * Class PayStatusController
 * @package App\Http\Controllers
 */
class PayStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $payStatuses = PayStatus::paginate();

        return view('pay-status.index', compact('payStatuses'))
            ->with('i', (request()->input('page', 1) - 1) * $payStatuses->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $payStatus = new PayStatus();
        return view('pay-status.create', compact('payStatus'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(PayStatus::$rules);

        $payStatus = PayStatus::create($request->all());

        return redirect()->route('pay-statuses.index')
            ->with('success', 'สร้างสถานะการชำระสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $payStatus = PayStatus::find($id);

        return view('pay-status.show', compact('payStatus'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $payStatus = PayStatus::find($id);

        return view('pay-status.edit', compact('payStatus'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  PayStatus $payStatus
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, PayStatus $payStatus)
    {
        request()->validate(PayStatus::$rules);

        $payStatus->update($request->all());

        return redirect()->route('pay-statuses.index')
            ->with('success', 'อัปเดตสถานะการชำระสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $payStatus = PayStatus::findOrFail($id);
        $payStatus->delete();

        return redirect()->route('pay-statuses.index')
            ->with('success', 'ลบสถานะการชำระสำเร็จ');
    }
}
