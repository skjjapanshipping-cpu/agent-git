<?php

namespace App\Http\Controllers;

use App\Models\DeliveryType;
use Illuminate\Http\Request;

/**
 * Class DeliveryTypeController
 * @package App\Http\Controllers
 */
class DeliveryTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $deliveryTypes = DeliveryType::paginate();

        return view('delivery-type.index', compact('deliveryTypes'))
            ->with('i', (request()->input('page', 1) - 1) * $deliveryTypes->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $deliveryType = new DeliveryType();
        return view('delivery-type.create', compact('deliveryType'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(DeliveryType::$rules);

        $deliveryType = DeliveryType::create($request->all());

        return redirect()->route('delivery-types.index')
            ->with('success', 'สร้างประเภทการจัดส่งสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $deliveryType = DeliveryType::find($id);

        return view('delivery-type.show', compact('deliveryType'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $deliveryType = DeliveryType::find($id);

        return view('delivery-type.edit', compact('deliveryType'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  DeliveryType $deliveryType
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DeliveryType $deliveryType)
    {
        request()->validate(DeliveryType::$rules);

        $deliveryType->update($request->all());

        return redirect()->route('delivery-types.index')
            ->with('success', 'อัปเดตประเภทการจัดส่งสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $deliveryType = DeliveryType::findOrFail($id);
        $deliveryType->delete();

        return redirect()->route('delivery-types.index')
            ->with('success', 'ลบประเภทการจัดส่งสำเร็จ');
    }
}
