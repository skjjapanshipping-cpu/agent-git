<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>เข้าสู่ระบบ — SKJ Japan Shipping</title>

    <link rel="icon" type="image/png" sizes="48x48" href="{{ url('/favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('/favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ url('/favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ url('/apple-touch-icon.png') }}">

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">

    <style>
        :root {
            --skj-blue:        #1D8AC9;
            --skj-blue-dark:   #0e5d8a;
            --skj-blue-light:  #36d1dc;
            --skj-navy:        #0f2235;
        }

        /* =================== HARD RESET — ไม่มี layout/CSS ภายนอกมารบกวน =================== */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            width: 100%;
            min-height: 100vh;
            overflow-x: hidden;
            background: #0a1a2e;
            font-family: 'Prompt', system-ui, -apple-system, "Segoe UI", sans-serif;
            color: #0f172a;
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
        }

        /* =================== STAGE =================== */
        .login-stage {
            min-height: 100vh;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
            padding:
                max(24px, env(safe-area-inset-top))
                max(16px, env(safe-area-inset-right))
                max(24px, env(safe-area-inset-bottom))
                max(16px, env(safe-area-inset-left));
            background:
                radial-gradient(1200px 600px at 80% -10%, rgba(54,209,220,0.25), transparent 60%),
                radial-gradient(900px 500px at -10% 110%, rgba(29,138,201,0.35), transparent 60%),
                linear-gradient(135deg, #0a1a2e 0%, #102d4e 45%, #0e3b66 100%);
        }

        .login-stage::before {
            content: "";
            position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 30%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 30%, transparent 100%);
            pointer-events: none;
        }

        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(70px);
            opacity: 0.5;
            pointer-events: none;
            animation: blobFloat 14s ease-in-out infinite;
        }
        .blob.b1 { width: 360px; height: 360px; background: #36d1dc; top: -80px; left: -80px; }
        .blob.b2 { width: 420px; height: 420px; background: #1D8AC9; bottom: -120px; right: -100px; animation-delay: -6s; }
        .blob.b3 { width: 220px; height: 220px; background: #5eead4; top: 50%; left: 65%; animation-delay: -3s; opacity: 0.3; }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0,0) scale(1); }
            50%      { transform: translate(20px,-30px) scale(1.06); }
        }

        /* =================== SHELL =================== */
        .auth-shell {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
            animation: cardIn 0.55s cubic-bezier(.2,.9,.3,1.2) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(18px) scale(0.97); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }

        .auth-card {
            width: 100%;
            background: #fff;
            border-radius: 24px;
            overflow: hidden;
            box-shadow:
                0 24px 64px rgba(0,0,0,0.32),
                0 8px 24px rgba(29,138,201,0.18);
        }

        /* HERO */
        .auth-hero {
            position: relative;
            padding: 28px 24px 22px;
            text-align: center;
            background: linear-gradient(135deg, var(--skj-blue) 0%, var(--skj-blue-light) 100%);
            color: #fff;
            overflow: hidden;
        }
        .auth-hero::after {
            content: "";
            position: absolute; inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.25), transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.15), transparent 50%);
            pointer-events: none;
        }
        .logo-wrap {
            width: 96px; height: 96px;
            background: #fff;
            border-radius: 50%;
            display: inline-flex;
            align-items: center; justify-content: center;
            box-shadow: 0 12px 28px rgba(0,0,0,0.18), inset 0 -3px 0 rgba(0,0,0,0.04);
            margin-bottom: 14px;
            position: relative; z-index: 1;
        }
        .logo-wrap img { width: 76px; height: 76px; object-fit: contain; }
        .auth-hero h1 {
            font-size: 20px; font-weight: 700;
            letter-spacing: 0.3px;
            position: relative; z-index: 1;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .subtitle {
            font-size: 13px; opacity: 0.92;
            margin-top: 4px;
            position: relative; z-index: 1;
        }
        .secure-pill {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 12px;
            padding: 4px 12px;
            background: rgba(255,255,255,0.18);
            border: 1px solid rgba(255,255,255,0.35);
            border-radius: 999px;
            font-size: 11px; font-weight: 600;
            position: relative; z-index: 1;
        }

        /* BODY */
        .auth-body { padding: 26px 28px 24px; }

        .field { margin-bottom: 16px; }
        .field label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }

        .input-wrap {
            position: relative;
            display: flex; align-items: center;
        }
        .input-wrap > i.input-icon {
            position: absolute; left: 14px;
            color: #94a3b8; font-size: 15px;
            transition: color 0.2s;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            height: 48px;
            padding: 0 44px 0 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 15px;
            font-family: inherit;
            color: #0f172a;
            transition: all 0.2s;
            outline: none;
        }
        .input-wrap input::placeholder { color: #94a3b8; font-weight: 400; }
        .input-wrap input:hover { background: #f1f5f9; }
        .input-wrap input:focus {
            border-color: var(--skj-blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(29,138,201,0.12);
        }
        .input-wrap input:focus ~ i.input-icon { color: var(--skj-blue); }
        .input-wrap input.is-invalid {
            border-color: #ef4444;
            background: #fef2f2;
        }

        .eye-btn {
            position: absolute; right: 8px;
            width: 34px; height: 34px;
            background: transparent; border: none;
            color: #64748b; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            border-radius: 8px;
            transition: all 0.15s;
        }
        .eye-btn:hover { background: #f1f5f9; color: var(--skj-blue); }

        .invalid-msg {
            display: block;
            margin-top: 6px;
            font-size: 12px; color: #ef4444;
            font-weight: 500;
        }

        .row-options {
            display: flex; align-items: center; justify-content: space-between;
            margin: 4px 0 18px;
            font-size: 13px;
        }
        .remember {
            display: inline-flex; align-items: center; gap: 8px;
            cursor: pointer; color: #475569; user-select: none;
        }
        .remember input {
            width: 17px; height: 17px;
            accent-color: var(--skj-blue);
            cursor: pointer;
        }
        .forgot-link {
            color: var(--skj-blue);
            font-weight: 600; text-decoration: none;
        }
        .forgot-link:hover { color: var(--skj-blue-dark); text-decoration: underline; }

        .btn-submit {
            width: 100%;
            height: 50px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--skj-blue), var(--skj-blue-light));
            color: #fff;
            font-size: 15px; font-weight: 700;
            letter-spacing: 0.4px;
            cursor: pointer;
            box-shadow: 0 10px 22px rgba(29,138,201,0.35), inset 0 -2px 0 rgba(0,0,0,0.12);
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            font-family: inherit;
            transition: all 0.2s;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(29,138,201,0.45), inset 0 -2px 0 rgba(0,0,0,0.12);
        }
        .btn-submit:active { transform: translateY(0); }

        .alert-info-box {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-left: 4px solid var(--skj-blue);
            padding: 11px 14px;
            border-radius: 10px;
            margin-top: 14px;
            font-size: 13px;
            color: #1e3a8a;
            display: flex; gap: 8px; align-items: flex-start;
        }

        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 22px 0 14px;
            color: #94a3b8;
            font-size: 11px; font-weight: 600;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }
        .divider::before, .divider::after {
            content: ""; flex: 1; height: 1px;
            background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        }

        .contact-card {
            padding: 14px;
            background: linear-gradient(135deg, #f8fafc, #f1f5f9);
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            text-align: center;
        }
        .ct-title {
            font-size: 13px;
            color: #475569;
            margin-bottom: 10px;
            font-weight: 500;
        }
        .ct-title strong { color: var(--skj-blue-dark); }
        .contact-buttons {
            display: flex; gap: 8px;
            justify-content: center; flex-wrap: wrap;
        }
        .btn-contact {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 10px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600; font-size: 13px;
            color: #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.12);
            transition: all 0.2s;
        }
        .btn-contact:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.18);
            color: #fff; text-decoration: none;
        }
        .btn-contact.line  { background: linear-gradient(135deg, #06C755, #04a544); }
        .btn-contact.phone { background: linear-gradient(135deg, var(--skj-blue), var(--skj-blue-dark)); }

        .quick-links {
            display: flex; justify-content: center; gap: 14px;
            margin-top: 18px;
            font-size: 12px;
            flex-wrap: wrap;
        }
        .quick-links a {
            color: #cbd5e1;
            text-decoration: none;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .quick-links a:hover { color: #fff; }
        .quick-links .sep { color: rgba(255,255,255,0.18); }

        .brand-foot {
            margin-top: 22px;
            text-align: center;
            color: rgba(255,255,255,0.55);
            font-size: 11px;
            letter-spacing: 0.4px;
            padding: 0 8px;
        }
        .brand-foot strong { color: #fff; opacity: 0.9; }

        /* =================== MOBILE =================== */
        @media (max-width: 480px) {
            .login-stage {
                padding-left:  max(12px, env(safe-area-inset-left));
                padding-right: max(12px, env(safe-area-inset-right));
                padding-top:    14px;
                padding-bottom: 14px;
            }
            .auth-shell { max-width: 100%; }
            .auth-hero { padding: 22px 18px 18px; }
            .logo-wrap { width: 80px; height: 80px; margin-bottom: 10px; }
            .logo-wrap img { width: 62px; height: 62px; }
            .auth-hero h1 { font-size: 18px; }
            .auth-body { padding: 22px 18px 18px; }
            .contact-buttons { flex-direction: column; }
            .btn-contact { width: 100%; justify-content: center; }
            .blob.b1 { width: 200px; height: 200px; top: -50px; left: -50px; opacity: 0.4; }
            .blob.b2 { width: 240px; height: 240px; bottom: -70px; right: -50px; opacity: 0.4; }
            .blob.b3 { display: none; }
        }
        @media (max-width: 360px) {
            .login-stage {
                padding-left:  max(8px, env(safe-area-inset-left));
                padding-right: max(8px, env(safe-area-inset-right));
            }
        }
    </style>
</head>
<body>

@php $support = \App\Models\SystemSetting::support(); @endphp

<div class="login-stage">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>

    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-hero">
                <div class="logo-wrap">
                    <img src="{{ asset('img/skj-logo-white.png') }}" alt="SKJ Japan Shipping" onerror="this.style.display='none'">
                </div>
                <h1>SKJ Japan Shipping</h1>
                <div class="subtitle">ระบบติดตามสินค้า · นำเข้าจากญี่ปุ่น</div>
                <span class="secure-pill"><i class="fa fa-lock"></i> เชื่อมต่อปลอดภัย SSL</span>
            </div>

            <div class="auth-body">
                <form method="POST" action="{{ route('login') }}" autocomplete="on">
                    @csrf

                    <div class="field">
                        <label for="email">อีเมล หรือ รหัสลูกค้า</label>
                        <div class="input-wrap">
                            <input id="email" type="text" name="email"
                                value="{{ old('email') }}"
                                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                required autocomplete="username" autofocus
                                autocapitalize="off" autocorrect="off" spellcheck="false" inputmode="email"
                                placeholder="example@email.com หรือ ANW-1234">
                            <i class="fa fa-envelope input-icon"></i>
                        </div>
                        @error('email')
                            <span class="invalid-msg"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">รหัสผ่าน</label>
                        <div class="input-wrap">
                            <input id="password" type="password" name="password"
                                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                required autocomplete="current-password"
                                autocapitalize="off" autocorrect="off" spellcheck="false"
                                placeholder="กรอกรหัสผ่าน">
                            <i class="fa fa-lock input-icon"></i>
                            <button type="button" class="eye-btn" onclick="togglePassword()" aria-label="แสดง/ซ่อนรหัสผ่าน">
                                <i class="fa fa-eye" id="password-toggle-icon"></i>
                            </button>
                        </div>
                        @error('password')
                            <span class="invalid-msg"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="row-options">
                        <label class="remember">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <span>จดจำฉัน</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">ลืมรหัสผ่าน?</a>
                        @endif
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa fa-sign-in"></i> เข้าสู่ระบบ
                    </button>

                    @if(session('info'))
                        <div class="alert-info-box">
                            <i class="fa fa-info-circle" style="margin-top:2px;"></i>
                            <span>{{ session('info') }}</span>
                        </div>
                    @endif
                </form>

                <div class="divider">ยังไม่มีบัญชี?</div>

                <div class="contact-card">
                    <div class="ct-title">ติดต่อ <strong>แอดมิน</strong> เพื่อเปิดบัญชีสมาชิก</div>
                    <div class="contact-buttons">
                        @if(!empty($support['line_url']))
                            <a href="{{ $support['line_url'] }}" target="_blank" rel="noopener" class="btn-contact line">
                                <i class="fa fa-commenting"></i> LINE {{ $support['line_id'] ?? '@skj.japan' }}
                            </a>
                        @endif
                        @if(!empty($support['phone']))
                            <a href="tel:{{ $support['phone'] }}" class="btn-contact phone">
                                <i class="fa fa-phone"></i> {{ $support['phone'] }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="quick-links">
            <a href="{{ url('/tracking') }}"><i class="fa fa-search"></i> เช็คเลขพัสดุ</a>
            <span class="sep">•</span>
            <a href="{{ url('/calc') }}"><i class="fa fa-calculator"></i> คำนวณค่าส่ง</a>
            <span class="sep">•</span>
            <a href="{{ url('/scanner/login') }}"><i class="fa fa-barcode"></i> Scanner Login</a>
            <span class="sep">•</span>
            <a href="https://skjjapanshipping.com" target="_blank" rel="noopener"><i class="fa fa-globe"></i> เว็บไซต์หลัก</a>
        </div>

        <div class="brand-foot">
            © {{ date('Y') }} <strong>SKJ Japan Shipping</strong> · บริการนำเข้าสินค้าจากญี่ปุ่นครบวงจร
        </div>
    </div>
</div>

<script>
function togglePassword() {
    var p = document.getElementById('password');
    var i = document.getElementById('password-toggle-icon');
    // กัน iOS Safari autocorrect/autocapitalize ตอนสลับเป็น type="text"
    p.setAttribute('autocapitalize', 'off');
    p.setAttribute('autocorrect', 'off');
    p.setAttribute('spellcheck', 'false');
    p.setAttribute('autocomplete', 'current-password');
    if (p.type === 'password') { p.type = 'text';     i.classList.remove('fa-eye');       i.classList.add('fa-eye-slash'); }
    else                       { p.type = 'password'; i.classList.remove('fa-eye-slash'); i.classList.add('fa-eye');       }
}
</script>

</body>
</html>
