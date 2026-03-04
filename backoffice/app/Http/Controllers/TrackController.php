<?php

namespace App\Http\Controllers;

use App\Imports\TrackImport;
use App\Models\Track;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\DataTables;

/**
 * Class TrackController
 * @package App\Http\Controllers
 */
class TrackController extends Controller
{
    public function __construct() {
        $this->middleware(['role:admin']);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

//        $tracks = Track::select('*', DB::raw("replace(track_no,'-','') as tracknodash"))
//            ->where('status', 1)
//            ->orderByDesc('source_date')
//            ->paginate(4000);

        return view('track.index');
    }

    public function fetchTrack(Request $request)
    {


        if ($request->ajax()) {
            $sqlQuery='';
            $queryAll =  Track::select('*', DB::raw("replace(track_no,'-','') as tracknodash"))
                ->where('status', 1)
                ->orderByDesc('source_date');


            if(!empty($request->start_date)) {
                session(['startdate' => $request->start_date]);
            }
            else
                session()->forget('startdate');

            if (!empty($request->search) || !empty($request->status)|| !empty($request->shipping_status)||!empty($request->start_date)|| (!empty($request->start_date) && !empty($request->end_date))) {
//


                if (strtolower($request->search) == strtolower('all')) {

                    $data = $queryAll->get();
                } else {
                    $data = $queryAll->where(function ($query) use ($request) {
                        if (!empty($request->search)) {

                            $query->where(function ($query) use ($request) {
                                $searchTerm = '%'.$request->search.'%';
                                $searchNoHyphens = '%'.str_replace('-', '', $request->search).'%';
                                $query->whereRaw("customer_name like ?", [$searchTerm])
                                    ->orWhereRaw("note like ?", [$searchTerm])
                                    ->orWhereRaw("DATE_FORMAT(source_date, '%d/%m/%Y') like ?", [$searchTerm])
                                    ->orWhereRaw("DATE_FORMAT(destination_date, '%d/%m/%Y') like ?", [$searchTerm])
                                    ->orWhereRaw("REPLACE(track_no, '-', '') LIKE ?", [$searchNoHyphens]);
                            });
//                            session(['search' => $request->search]);
//                            dd(session('search'));
                        }
//                        else{
////                            session()->forget('search');
//                        }

                        if (!empty($request->start_date) && !empty($request->end_date))
                            $query->whereBetween('ship_date', [$request->start_date, $request->end_date]);
                        else if (!empty($request->start_date))
                            $query->whereRaw("DATE(ship_date) BETWEEN ? AND ?", [$request->start_date, $request->start_date]);


                    })->orderByRaw('customer_name asc')->take(1000)->get();


                    $sqlQuery = $queryAll->toSql();
//                    dd($sqlQuery);
                }
            } else {
                $data = $queryAll->orderByRaw('customer_name asc')->take(1000)->get(); // โหลดเพียง 20 รายการเมื่อครั้งแรก

            }
            $sqlQuery = $queryAll->toSql();




//dd($sqlQuery);


            return Datatables::of($data)
                ->addColumn('action_del', function($row) {
                    return route('tracks.destroy', $row->id);
                })
                ->addColumn('edit_url', function($row) {
                    return route('tracks.edit', $row->id);
                })
                ->with([
                    'query'=>$sqlQuery

                ])
                ->make(true);


        }
    }

    public function confirmImport()
    {
        $tracks = Track::where('status',0)->orderByDesc('source_date')->paginate(10000);

        return view('track.confirm', compact('tracks'))
            ->with('i', (request()->input('page', 1) - 1) * $tracks->perPage());
    }

    public function importView(){
        return view('track.import');
    }
    public function import()
    {
        Excel::import(new TrackImport,request()->file('file'));

        return redirect()->route('tracksconfirm')
            ->with('success', 'กรุณาตรวจสอบข้อมูลก่อนยืนยัน');
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $track = new Track();
        return view('track.create', compact('track'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(Track::$rules);

        $track = Track::create($request->all());

        return redirect()->route('tracks.index')
            ->with('success', 'สร้างรายการสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $track = Track::find($id);

        return view('track.show', compact('track'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $track = Track::find($id);

        return view('track.edit', compact('track'));
    }

    public function update_confirmimport(Request $request){
//Destination Date
        $trackIds = explode(',', $request->input('track_ids'));

        try{
            // ทำสิ่งที่คุณต้องการกับข้อมูลที่ได้รับ
            // ตัวอย่าง: อัปเดต status ของ tracks ในฐานข้อมูล
            Track::whereIn('id', $trackIds)->update([
                'status' => 1,
            ]);
            Track::where('status', 0)->delete();
            return redirect()->route('tracks.index')
                ->with('success', 'นำเข้าข้อมูลสำเร็จ');

        } catch (\Exception $e) {
            return redirect()->route('tracks.index')
                ->with('error', 'นำเข้าข้อมูลไม่สำเร็จ');
        }
    }

    public function del_confirmimport(Request $request){


        try{
            // ทำสิ่งที่คุณต้องการกับข้อมูลที่ได้รับ
            // ตัวอย่าง: อัปเดต status ของ tracks ในฐานข้อมูล
            Track::where('status', 0)->delete();
            return redirect()->route('tracks.index')
                ->with('success', 'เคลียร์ข้อมูลสำเร็จ');

        } catch (\Exception $e) {
            return redirect()->route('tracks.index')
                ->with('error', 'เคลียร์ข้อมูลไม่สำเร็จ');
        }
    }
    public function update_StatusByIDs(Request $request){
//Destination Date
        $trackIds = explode(',', $request->input('track_ids'));
        $selectedDate = $request->input('date');
        try{
            // ทำสิ่งที่คุณต้องการกับข้อมูลที่ได้รับ
            // ตัวอย่าง: อัปเดต status ของ tracks ในฐานข้อมูล
            Track::whereIn('id', $trackIds)->update([
                'destination_date' => $selectedDate,
            ]);
            return redirect()->route('tracks.index')
                ->with('success', 'อัปเดตสินค้าถึงไทยเรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return redirect()->route('tracks.index')
                ->with('success', 'อัปเดตสินค้าถึงไทยไม่สำเร็จ');
        }
    }

    public function update_StatusByIDs2(Request $request){
//ETD
        $trackIds = explode(',', $request->input('track_ids2'));
        $selectedDate = $request->input('date2');
//        dd($request->all());
        try{
            // ทำสิ่งที่คุณต้องการกับข้อมูลที่ได้รับ
            // ตัวอย่าง: อัปเดต status ของ tracks ในฐานข้อมูล
            Track::whereIn('id', $trackIds)->update([
                'ship_date' => $selectedDate,
            ]);
            return redirect()->route('tracks.index')
                ->with('success', 'อัปเดตETD เรียบร้อยแล้ว');

        } catch (\Exception $e) {
            return redirect()->route('tracks.index')
                ->with('success', 'อัปเดตสETD ไม่สำเร็จ');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Track $track
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Track $track)
    {
        request()->validate(Track::$rules);

        $track->update($request->all());

        return redirect()->route('tracks.index')
            ->with('success', 'อัปเดตรายการสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $track = Track::find($id)->delete();

        return redirect()->route('tracks.index')
            ->with('success', 'ลบรายการสำเร็จ');
    }
}
