<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\MyAuthProvider;
use App\Tambon;
use App\User;
use Illuminate\Http\Request;

/**
 * Class CustomerController
 * @package App\Http\Controllers
 */
class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $customers = User::whereHas('roles')->paginate();
        $customers=  User::whereHas('roles', function($query) {
            $query->where('name','user');
        })->get();
        $lineUserIds = MyAuthProvider::where('provider', 'line')->pluck('userid')->toArray();
        return view('customer.index', compact('customers', 'lineUserIds'))
            ->with('i', 0);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $customer = new User();
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();
        $deliveryTypes = \App\Models\DeliveryType::all();
        return view('customer.create', compact('customer','provinces','amphoes','tambons','deliveryTypes'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Customer::$rules);

        $customer = User::create($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'สร้างลูกค้าสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $customer = User::find($id);

        return view('customer.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $customer = User::find($id);
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();
        $deliveryTypes = \App\Models\DeliveryType::all();
        return view('customer.edit', compact('customer','provinces','amphoes','tambons','deliveryTypes'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Customer $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $customer)
    {
//        request()->validate(User::$rules);

        $customer->update($request->all());

        return redirect()->route('customers.index')
            ->with('success', 'อัปเดตลูกค้าสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $customer = User::findOrFail($id);
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', 'ลบลูกค้าสำเร็จ');
    }
}
