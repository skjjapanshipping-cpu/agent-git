<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\MyAuthProvider;
use App\Providers\RouteServiceProvider;
use App\User;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

//use Auth;
use App\UserLoginLog;
use App\Events\UserEvent;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
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
        $this->middleware('guest')->except(['logout', 'redirectToLine', 'handleLineCallback']);
    }

    function authenticated(Request $request, $user)
    {
        if (!Auth::check()) {
            return view('errors.404');
        }
        event(new UserEvent($request, $user));
    }

//    public function login(Request $request){
//        /**
//         *
//         *
//         *
//         */
//
//        $request->validate(
//            [
//                'email'=>['required'],
//                'password'=>['required']
//            ]
//
//        );
//
////        if($request->has(['password']))
//
//
//        $credentials = $request->only('email', 'password');
//        //$user = User::where('email',$request->email)->where('password',Hash::make($request->password))->first();
//        if(\Illuminate\Support\Facades\Auth::attempt($credentials))
//            //\Illuminate\Support\Facades\Auth::login($user);
//            return redirect()->route('home');
//        else
//            throw ValidationException::withMessages([
//                'email'=>["Username or password is incorrect"]
//            ]);
//        return redirect()->route('home');
//    }

    public function redirectToLine()
    {

        return Socialite::driver('line')->redirect();
    }

    public function handleLineCallback()//handleLineCallback
    {
        try {
            $lineUser = Socialite::driver('line')->user();

            $finduser = MyAuthProvider::where('provider', 'line')->where('providerid', $lineUser->id)->first();

            if ($finduser) {
                // LINE เคยเชื่อมแล้ว → login เข้าระบบ
                $appUser = User::where('id', $finduser->userid)->first();
                Auth::login($appUser);

                return redirect('/');
            } else {
                if (Auth::check()) {
                    // ผู้ใช้ login อยู่แล้ว → เชื่อม LINE กับ account ปัจจุบัน
                    $new_provider = new MyAuthProvider();
                    $new_provider->userid = Auth::id();
                    $new_provider->provider = 'line';
                    $new_provider->providerid = $lineUser->id;
                    $new_provider->save();

                    return redirect('/shippingview')->with('success', 'เชื่อมต่อ LINE สำเร็จ! คุณจะได้รับการแจ้งเตือนผ่าน LINE');
                } else {
                    // ยังไม่ได้ login → สร้าง user ใหม่
                    $newUser = new User();
                    $newUser->name = $lineUser->name ? $lineUser->name : $lineUser->nickname;
                    $newUser->email = $lineUser->email;
                    $newUser->save();
                    $newUser->assignRole('user');

                    $new_provider = new MyAuthProvider();
                    $new_provider->userid = $newUser->id;
                    $new_provider->provider = 'line';
                    $new_provider->providerid = $lineUser->id;
                    $new_provider->save();
                    Auth::login($newUser);
                    return redirect('/');
                }
            }
        } catch (\Exception $e) {
            Log::error('LINE Callback Error: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine());
            return redirect('/login')->with('error', 'LINE login error: ' . $e->getMessage());
        }
    }

}
