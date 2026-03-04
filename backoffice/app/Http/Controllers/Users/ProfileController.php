<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Tambon;
use Illuminate\Http\Request;
use Auth;
use App\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
   /**
    *
    * allow admin only
    *
    */
    public function __construct() {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $user = Auth::user();
//        dd($user);
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();

        return view('users.profile.profile',compact('user','provinces','amphoes','tambons'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $currentUserId = Auth::id();

        // ป้องกัน IDOR - ตรวจสอบว่า user แก้ไขโปรไฟล์ตัวเองเท่านั้น
        if ((int)$id !== $currentUserId) {
            abort(403, 'Unauthorized');
        }

        $request->validate([
            'name' => ['required','string', 'max:255'],
            'email' => ['required','string', 'email', 'max:255',Rule::unique('users')->ignore($currentUserId)],
//            'avatar' => ['required','mimes:jpeg,bmp,png,PNG,JPG,jpg,JPEG','max:9000'],
            'mobile' => ['required','numeric','digits_between:9,15',Rule::unique('users', 'mobile')->ignore($currentUserId)]
        ]);



//        $name = null;
//        $newImageName = null;

        //check if file attached
//        if($file = $request->file('avatar')){
//            $tmp = explode('.', $file->getClientOriginalName());//get client file name
//            $name = $file->getClientOriginalName();
//            $newImageName = round(microtime(true)).'.'.end($tmp);
//            $file->move(storage_path('app\public\profile-pic'), $newImageName);
//        }
        $user = User::findOrFail($id);
//        $newImage = null;
//        $newImage = $newImageName == null? $user->avatar:$newImageName;
//        $user->update(array_merge($request->all(),['avatar' => $newImage]));
        $user->update($request->only([
            'name',
            'email',
            'mobile',
            'addr',
            'province',
            'distrinct',
            'subdistrinct',
            'postcode',
        ]));

        return redirect()->route('profile.index')->with('success','อัปเดตโปรไฟล์สำเร็จ');
    }

    public function showChangePassword()
    {
        return view('users.profile.change-password');
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password' => ['required', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/', 'confirmed'],
        ], [
            'current_password.required' => 'กรุณากรอกรหัสผ่านเดิม',
            'password.required' => 'กรุณากรอกรหัสผ่านใหม่',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.regex' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่และตัวเลขอย่างน้อย 1 ตัว',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'รหัสผ่านเดิมไม่ถูกต้อง']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('change-password')->with('success', 'รหัสผ่านของคุณถูกเปลี่ยนเรียบร้อยแล้ว');
    }

}
