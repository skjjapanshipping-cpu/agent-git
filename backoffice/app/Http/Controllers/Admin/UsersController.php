<?php

namespace App\Http\Controllers\Admin;

use App\User;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use App\UserLoginLog;

class UsersController extends Controller
{
     /**
    *
    * allow admin only
    *
    */
    public function __construct() {
        //$this->middleware(['role:admin|creator']);
        $this->middleware(['role:admin'])->except('stopImpersonate');
    }

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $users = User::all();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display a listing of User.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexLoginLogs()
    {
        $userLoginActivities = UserLoginLog::paginate(10);

        return view('admin.activity.logs', compact('userLoginActivities'));
    }

    /**
     * Show the form for creating new User.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $roles = Role::get()->pluck('name', 'name');

        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created User in storage.
     *
     * @param  \App\Http\Requests\StoreUsersRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'numeric', 'digits:10', 'unique:users'],
            'password' => ['required','min:8','regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
            'roles.*' => ['required']
        ], [
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.regex' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่และตัวเลขอย่างน้อย 1 ตัว',
        ]);

        $user = User::create(array_merge($request->all(),['password' => bcrypt($request->password)]));
        $roles = $request->input('roles') ? $request->input('roles') : [];
        $user->assignRole($roles);

        return redirect()->route('users.index')->with('success', "สร้างผู้ใช้ $user->name สำเร็จ");
    }


    /**
     * Show the form for editing User.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $roles = Role::get()->pluck('name', 'name');

        $user = User::findOrFail($id);

        $data = $user->roles()->pluck('name');
        $selectedRoles = $data->first() ?? '';

        return view('admin.users.edit', compact('user', 'roles','selectedRoles'));
    }

    /**
     * Update User in storage.
     *
     * @param  \App\Http\Requests\UpdateUsersRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required','string', 'email', 'max:255', Rule::unique('users')->ignore($id)],
            'mobile' => ['required', 'numeric', 'digits:10', Rule::unique('users')->ignore($id)],
            'password' => ['sometimes','nullable','min:8','regex:/^(?=.*[A-Z])(?=.*\d).+$/'],
            'roles.*' => ['required']
        ], [
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.regex' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่และตัวเลขอย่างน้อย 1 ตัว',
        ]);

        $user = User::findOrFail($id);
        $data = $request->except(['password']);
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);
        $roles = $request->input('roles') ? $request->input('roles') : [];
        $user->syncRoles($roles);

        return redirect()->route('users.index')->with('success', "อัปเดตผู้ใช้ $user->name สำเร็จ");
    }

    /**
     * Remove User from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->route('users.index')->with('danger', "ลบผู้ใช้ $user->name สำเร็จ");
    }

    /**
     * Delete all selected User at once.
     *
     * @param Request $request
     */
    public function massDestroy(Request $request)
    {
        if ($request->input('ids')) {
            User::whereIn('id', $request->input('ids'))->delete();
        }
    }

    public function impersonate($id)
    {
        $user = User::findOrFail($id);

        session()->put('impersonator_id', Auth::id());
        session()->put('impersonator_name', Auth::user()->name);

        Auth::login($user);

        return redirect()->route('home')->with('success', "เข้าสู่ระบบแทน {$user->name} สำเร็จ");
    }

    public function stopImpersonate()
    {
        $adminId = session()->pull('impersonator_id');
        session()->forget('impersonator_name');

        if ($adminId) {
            Auth::loginUsingId($adminId);
        }

        return redirect()->route('users.index')->with('success', 'กลับสู่ระบบ Admin สำเร็จ');
    }

}
