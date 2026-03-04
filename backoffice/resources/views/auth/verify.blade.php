@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping">
                <p>ยืนยันอีเมล</p>
            </div>

            @if (session('resent'))
                <div class="alert alert-success" role="alert" style="border-radius:12px; border:none; border-left:4px solid #22c55e; background:#f0fdf4; color:#166534; padding:14px 18px; font-size:0.9rem; margin-bottom:20px;">
                    <i class="fa fa-check-circle"></i> ลิงก์ยืนยันใหม่ถูกส่งไปยังอีเมลของคุณแล้ว
                </div>
            @endif

            <div style="text-align:center; color:#475569; font-size:0.95rem; line-height:1.7; margin-bottom:24px;">
                <div style="width:70px; height:70px; background:rgba(29,138,201,0.1); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 20px;">
                    <i class="fa fa-envelope" style="font-size:1.8rem; color:#1D8AC9;"></i>
                </div>
                กรุณาตรวจสอบอีเมลของคุณเพื่อยืนยันบัญชี<br>
                หากยังไม่ได้รับอีเมล
            </div>

            <form class="d-inline" method="POST" action="{{ route('verification.resend') }}">
                @csrf
                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-refresh"></i> ส่งลิงก์ยืนยันอีกครั้ง
                </button>
            </form>

            <div class="auth-links">
                <a href="{{ route('login') }}">กลับเข้าสู่หน้าล็อกอิน</a>
            </div>
        </div>
    </div>
@endsection
