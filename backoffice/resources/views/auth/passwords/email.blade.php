@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping">
                <p>รีเซ็ตรหัสผ่าน</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success" role="alert" style="border-radius:12px; border:none; border-left:4px solid #22c55e; background:#f0fdf4; color:#166534; padding:14px 18px; font-size:0.9rem; margin-bottom:20px;">
                    <i class="fa fa-check-circle"></i> {{ session('status') }}
                </div>
            @endif

            <!-- Forgot Password Form -->
            <form method="POST" action="{{ route('password.email') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <div class="input-icon-wrapper">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ old('email') }}" required autocomplete="email" autofocus
                            placeholder="กรอกอีเมลที่ลงทะเบียน">
                        <i class="fa fa-envelope input-icon"></i>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-paper-plane"></i> ส่งลิงก์รีเซ็ตรหัสผ่าน
                </button>

                <!-- Links -->
                <div class="auth-links">
                    <a href="{{ route('login') }}">กลับเข้าสู่หน้าล็อกอิน</a>
                </div>
            </form>
        </div>
    </div>
@endsection
