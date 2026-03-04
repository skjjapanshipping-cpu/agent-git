<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

/**
 * Class LocationController
 * @package App\Http\Controllers
 */
class LocationController extends Controller
{
    public function __construct() {
        $this->middleware(['role:admin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($app_id)
    {
        $locations = Location::where('app_id',$app_id)->paginate();
// dd($locations);
        return view('location.index', compact('locations','app_id'))
            ->with('i', (request()->input('page', 1) - 1) * $locations->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($app_id)
    {
        $location = new Location();
        // dd($location);
        return view('location.create', compact('location','app_id'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Location::$rules);


        $location = Location::create(
            array_merge($request->all()
            ,['hashcode'=>encrypt(sprintf('%s.%s',$request->id,$request->app_id))])
        );

        return redirect()->route('locations.index',$request->app_id)
            ->with('success', 'สร้างสถานที่สำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $location = Location::find($id);

        return view('location.show', compact('location'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $location = Location::find($id);

        return view('location.edit', compact('location'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Location $location
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $location)
    {
        request()->validate(Location::$rules);

        $location->update($request->all());

        return redirect()->route('locations.index')
            ->with('success', 'อัปเดตสถานที่สำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'ลบสถานที่สำเร็จ');
    }
}
