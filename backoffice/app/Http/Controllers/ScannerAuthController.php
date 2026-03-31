<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScannerAuthController extends Controller
{
    protected function guard()
    {
        return Auth::guard('scanner');
    }

    public function showLogin()
    {
        if ($this->guard()->check() && ($this->guard()->user()->hasRole('scanner') || $this->guard()->user()->hasRole('admin'))) {
            return redirect('/scanner');
        }
        return view('scanner.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ], [
            'email.required' => 'กรุณากรอก ID (อีเมล)',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
        ]);

        $credentials = $request->only('email', 'password');

        if ($this->guard()->attempt($credentials, $request->filled('remember'))) {
            $user = $this->guard()->user();

            if (!$user->hasRole('scanner')) {
                $this->guard()->logout();
                return back()->withErrors(['email' => 'บัญชีนี้ไม่มีสิทธิ์เข้าระบบสแกน'])->withInput();
            }

            $request->session()->regenerate();
            return redirect('/scanner');
        }

        return back()->withErrors(['email' => 'ID หรือรหัสผ่านไม่ถูกต้อง'])->withInput();
    }

    public function logout(Request $request)
    {
        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/scanner/login');
    }
}
