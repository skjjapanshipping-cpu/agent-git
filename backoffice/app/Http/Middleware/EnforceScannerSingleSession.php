<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * บังคับให้ Scanner Login 1 บัญชี ต่อ 1 อุปกรณ์ เท่านั้น
 *
 * Flow:
 *  1. ตอน login จะบันทึก session_id ปัจจุบันลง users.scanner_session_id
 *  2. middleware นี้เช็คทุก request: session_id ปัจจุบันต้องตรงกับใน DB
 *  3. ถ้าไม่ตรง = มีอุปกรณ์อื่น login เข้ามาแทน → kick อุปกรณ์เก่าออก พร้อม flash message
 */
class EnforceScannerSingleSession
{
    public function handle(Request $request, Closure $next)
    {
        $guard = Auth::guard('scanner');

        if ($guard->check()) {
            $user = $guard->user();
            $currentSid = $request->session()->getId();
            $storedSid  = $user->scanner_session_id;

            // ถ้ายังไม่เคยบันทึก session id (เช่น login เก่าก่อนเปิดฟีเจอร์) → บันทึกครั้งแรกให้
            if (empty($storedSid)) {
                $user->forceFill(['scanner_session_id' => $currentSid])->save();
            } elseif ($storedSid !== $currentSid) {
                // มีอุปกรณ์อื่นเข้ามาใช้บัญชีนี้ → เตะออก
                $guard->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                $msg = '⚠️ บัญชีนี้ถูกใช้งานบนอุปกรณ์อื่นแล้ว — กรุณาเข้าสู่ระบบใหม่อีกครั้ง (1 บัญชี ใช้ได้ 1 อุปกรณ์เท่านั้น)';

                if ($request->expectsJson() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'error' => 'session_invalidated',
                        'message' => $msg,
                    ], 401);
                }

                return redirect()->guest('/scanner/login')->withErrors(['email' => $msg]);
            }
        }

        return $next($request);
    }
}
