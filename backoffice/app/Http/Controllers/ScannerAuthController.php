<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

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

            // เตะ session เก่าก่อน (ถ้ามี) — รองรับ single device per ID
            $this->invalidateOldSession($user->scanner_session_id);

            $request->session()->regenerate();

            // บันทึก session_id ใหม่ → middleware ใช้ตรวจสอบทุก request
            $user->forceFill([
                'scanner_session_id' => $request->session()->getId(),
            ])->save();

            return redirect('/scanner');
        }

        return back()->withErrors(['email' => 'ID หรือรหัสผ่านไม่ถูกต้อง'])->withInput();
    }

    public function logout(Request $request)
    {
        $user = $this->guard()->user();
        if ($user) {
            // ล้าง session id ที่บันทึกไว้ใน DB
            try {
                $user->forceFill(['scanner_session_id' => null])->save();
            } catch (\Throwable $e) {
                // ignore — เพื่อไม่ให้ logout flow พังเพราะ DB
            }
        }

        $this->guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/scanner/login');
    }

    /**
     * พยายามลบ session file เก่าออก (เฉพาะ driver=file)
     * ถ้าใช้ driver อื่น (database/redis/etc) จะปล่อยให้ GC จัดการ
     * — middleware EnforceScannerSingleSession จะคอยเตะ session เก่าออกเองทุก request อยู่แล้ว
     */
    protected function invalidateOldSession(?string $oldSid): void
    {
        if (empty($oldSid)) {
            return;
        }

        try {
            $driver = Config::get('session.driver');
            if ($driver === 'file') {
                $path = Config::get('session.files');
                $file = rtrim($path, '/\\') . DIRECTORY_SEPARATOR . $oldSid;
                if (File::exists($file)) {
                    File::delete($file);
                }
            }
        } catch (\Throwable $e) {
            // เงียบไว้ — middleware จะ handle session mismatch อยู่แล้ว
        }
    }
}
