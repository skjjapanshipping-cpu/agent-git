<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScannerAuthController extends Controller
{
    /**
     * แสดงหน้า Login สำหรับคนสแกนพัสดุ
     */
    public function showLogin()
    {
        if (Auth::check() && (Auth::user()->hasRole('scanner') || Auth::user()->hasRole('admin'))) {
            return redirect('/scanner');
        }
        return view('scanner.login');
    }

    /**
     * ล็อกอินเข้าระบบสแกน
     */
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

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // ตรวจว่าเป็น role scanner เท่านั้น
            if (!$user->hasRole('scanner')) {
                Auth::logout();
                return back()->withErrors(['email' => 'บัญชีนี้ไม่มีสิทธิ์เข้าระบบสแกน'])->withInput();
            }

            $request->session()->regenerate();
            return redirect('/scanner');
        }

        return back()->withErrors(['email' => 'ID หรือรหัสผ่านไม่ถูกต้อง'])->withInput();
    }

    /**
     * ออกจากระบบ
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/scanner/login');
    }
}
