<?php

namespace App\Http\Controllers;

use App\Models\Dailyrate;
use Illuminate\Http\Request;

/**
 * Class DailyrateController
 * @package App\Http\Controllers
 */
class DailyrateController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dailyrates = Dailyrate::orderByDesc('created_at')->paginate(50);

        return view('dailyrate.index', compact('dailyrates'))
            ->with('i', (request()->input('page', 1) - 1) * $dailyrates->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $dailyrate = new Dailyrate();
        return view('dailyrate.create', compact('dailyrate'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Dailyrate::$rules);

        $dailyrate = Dailyrate::create($request->all());

        return redirect()->route('dailyrates.index')
            ->with('success', 'สร้างอัตราแลกเปลี่ยนสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dailyrate = Dailyrate::find($id);

        return view('dailyrate.show', compact('dailyrate'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dailyrate = Dailyrate::find($id);

        return view('dailyrate.edit', compact('dailyrate'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Dailyrate $dailyrate
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Dailyrate $dailyrate)
    {
        request()->validate(Dailyrate::$rules);

        $dailyrate->update($request->all());

        return redirect()->route('dailyrates.index')
            ->with('success', 'อัปเดตอัตราแลกเปลี่ยนสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $dailyrate = Dailyrate::findOrFail($id);
        $dailyrate->delete();

        return redirect()->route('dailyrates.index')
            ->with('success', 'ลบอัตราแลกเปลี่ยนสำเร็จ');
    }
}
