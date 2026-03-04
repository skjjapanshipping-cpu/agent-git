<?php

namespace App\Http\Controllers;

use App\Models\App;
use Illuminate\Http\Request;

/**
 * Class AppController
 * @package App\Http\Controllers
 */
class AppController extends Controller
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
        $apps = App::paginate();

        return view('app.index', compact('apps'))
            ->with('i', (request()->input('page', 1) - 1) * $apps->perPage());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $app = new App();
        return view('app.create', compact('app'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        request()->validate(App::$rules);

        $app = App::create($request->all());

        return redirect()->route('apps.index')
            ->with('success', 'สร้างแอปสำเร็จ');
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $app = App::find($id);

        return view('app.show', compact('app'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $app = App::find($id);

        return view('app.edit', compact('app'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  App $app
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, App $app)
    {
        request()->validate(App::$rules);

        $app->update($request->all());

        return redirect()->route('apps.index')
            ->with('success', 'อัปเดตแอปสำเร็จ');
    }

    /**
     * @param int $id
     * @return \Illuminate\Http\RedirectResponse
     * @throws \Exception
     */
    public function destroy($id)
    {
        $app = App::findOrFail($id);
        $app->delete();

        return redirect()->route('apps.index')
            ->with('success', 'ลบแอปสำเร็จ');
    }
}
