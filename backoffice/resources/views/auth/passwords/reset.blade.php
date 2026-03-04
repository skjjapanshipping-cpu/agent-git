@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping">
                <p>ตั้งรหัสผ่านใหม่</p>
            </div>

            <!-- Reset Form -->
            <form method="POST" action="{{ route('password.update') }}" class="auth-form">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <!-- Email -->
                <div class="form-group">
                    <label for="email">อีเมล</label>
                    <div class="input-icon-wrapper">
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror"
                            name="email" value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus
                            placeholder="กรอกอีเมล">
                        <i class="fa fa-envelope input-icon"></i>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">รหัสผ่านใหม่</label>
                    <div class="input-icon-wrapper">
                        <input id="password" type="password"
                            class="form-control @error('password') is-invalid @enderror"
                            name="password" placeholder="กรอกรหัสผ่านใหม่" required autocomplete="new-password">
                        <i class="fa fa-lock input-icon"></i>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password', 'password-icon')">
                            <i class="fa fa-eye" id="password-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label for="password-confirm">ยืนยันรหัสผ่านใหม่</label>
                    <div class="input-icon-wrapper">
                        <input id="password-confirm" type="password" class="form-control"
                            name="password_confirmation" placeholder="ยืนยันรหัสผ่านใหม่" required autocomplete="new-password">
                        <i class="fa fa-lock input-icon"></i>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword('password-confirm', 'confirm-icon')">
                            <i class="fa fa-eye" id="confirm-icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-check"></i> ยืนยันการเปลี่ยนรหัสผ่าน
                </button>

                <!-- Links -->
                <div class="auth-links">
                    <a href="{{ route('login') }}">กลับเข้าสู่หน้าล็อกอิน</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        function togglePassword(inputId, iconId) {
            var input = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endsection
