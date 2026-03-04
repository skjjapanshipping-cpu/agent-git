<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Tambon;
use App\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'mobile' => ['required', 'numeric', 'digits:10', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'regex:/^(?=.*[A-Z])(?=.*\d).+$/', 'confirmed'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.email' => 'รูปแบบอีเมลไม่ถูกต้อง',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้ว',
            'mobile.required' => 'กรุณากรอกเบอร์โทรศัพท์',
            'mobile.digits' => 'เบอร์โทรศัพท์ต้องเป็นตัวเลข 10 หลัก',
            'mobile.unique' => 'เบอร์โทรศัพท์นี้ถูกใช้งานแล้ว',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร',
            'password.regex' => 'รหัสผ่านต้องมีตัวพิมพ์ใหญ่และตัวเลขอย่างน้อย 1 ตัว',
            'password.confirmed' => 'ยืนยันรหัสผ่านไม่ตรงกัน',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $latestUser = User::whereNotNull('customerno')->latest()->first();
        // หากไม่มีผู้ใช้ในฐานข้อมูล
        if (!$latestUser) {
            $runNumber = 500; // กำหนดค่าเริ่มต้น
        } else {
            // ดึง customerno ล่าสุด
            $latestCustomerno = $latestUser->customerno;

            // แยกเอาเฉพาะตัวเลขจาก customerno ล่าสุด
            $latestRunNumber = (int)explode('-', $latestCustomerno)[1];

            // เพิ่มเลขลำดับ
            $runNumber = $latestRunNumber + 1;
        }
// dd($data);
        // สร้าง customerno ใหม่
        $customerno = 'anw-' . $runNumber;

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'mobile' => $data['mobile'],
            'addr'=>$data['addr'],
            'province'=>$data['province'],
            'distrinct'=>$data['distrinct'],
            'subdistrinct'=>$data['subdistrinct'],
            'postcode'=>$data['postcode'],
            'password' => Hash::make($data['password']),
            'customerno' => $customerno
        ]);
        $user->assignRole('user');
        return $user;
    }

    public function showRegistrationForm()
    {
        $provinces = Tambon::getCachedProvinces();
        $amphoes = Tambon::getCachedAmphoes();
        $tambons = Tambon::getCachedTambons();
        return view('auth.register', compact('provinces','amphoes','tambons'));

    }
}
