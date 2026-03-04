@extends('layouts.app')

@section('content')
    <div class="auth-page">
        <div class="auth-card">
            <!-- Logo -->
            <div class="auth-logo">
                <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping">
                <p>ระบบติดตามสินค้า</p>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}" class="auth-form">
                @csrf

                <!-- Email Field -->
                <div class="form-group">
                    <label for="email">{{ __('อีเมล') }}</label>
                    <div class="input-icon-wrapper">
                        <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email"
                            value="{{ old('email') }}" required autocomplete="email" autofocus
                            placeholder="กรอกอีเมลของคุณ">
                        <i class="fa fa-envelope input-icon"></i>
                    </div>
                    @error('email')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <label for="password">{{ __('รหัสผ่าน') }}</label>
                    <div class="input-icon-wrapper">
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror"
                            name="password" required autocomplete="current-password" placeholder="กรอกรหัสผ่าน">
                        <i class="fa fa-lock input-icon"></i>
                        <button type="button" class="password-toggle-btn" onclick="togglePassword()">
                            <i class="fa fa-eye" id="password-toggle-icon"></i>
                        </button>
                    </div>
                    @error('password')
                        <span class="invalid-feedback" role="alert">
                            <strong>{{ $message }}</strong>
                        </span>
                    @enderror
                </div>

                <!-- Remember Me -->
                <div class="remember-check">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">{{ __('จดจำฉัน') }}</label>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn-auth-primary">
                    <i class="fa fa-sign-in"></i> {{ __('เข้าสู่ระบบ') }}
                </button>

                <!-- Links -->
                <div class="auth-links">
                    <a href="{{ route('register') }}">สมัครสมาชิก</a>
                    @if (Route::has('password.request'))
                        <span class="divider">|</span>
                        <a href="{{ route('password.request') }}">{{ __('ลืมรหัสผ่าน?') }}</a>
                    @endif
                </div>
            </form>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('password-toggle-icon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
@endsection