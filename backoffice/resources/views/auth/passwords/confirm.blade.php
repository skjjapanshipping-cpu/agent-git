@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping">
                <p>ยืนยันรหัสผ่าน</p>
            </div>

            <div style="text-align:center; color:#475569; font-size:0.9rem; margin-bottom:24px;">
                กรุณากรอกรหัสผ่านเพื่อดำเนินการต่อ
            </div>

            <form method="POST" action="{{ route('password.confirm') }}" class="auth-form">
                @csrf

                <div class="form-group">
                    <label for="password">รหัสผ่าน</label>
                    <div class="input-icon-wrapper">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password"
                            placeholder="กรอกรหัสผ่าน">
                        <i class="fa fa-lock input-icon"></i>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-check"></i> ยืนยัน
                </button>

                <div class="auth-links">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}">ลืมรหัสผ่าน?</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection
