<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>ลืมรหัสผ่าน — SKJ Japan Shipping</title>

    <link rel="icon" type="image/png" sizes="48x48" href="{{ url('/favicon-48x48.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ url('/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ url('/favicon-16x16.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ url('/favicon.ico') }}">

    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">

    <style>
        :root {
            --skj-blue:        #1D8AC9;
            --skj-blue-dark:   #0e5d8a;
            --skj-blue-light:  #36d1dc;
        }

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

        .login-stage {
            min-height: 100vh;
            width: 100%;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
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
            content: ""; position: absolute; inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.04) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.04) 1px, transparent 1px);
            background-size: 56px 56px;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 30%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 40%, #000 30%, transparent 100%);
            pointer-events: none;
        }

        .blob { position: absolute; border-radius: 50%; filter: blur(70px); opacity: 0.5;
            pointer-events: none; animation: blobFloat 14s ease-in-out infinite; }
        .blob.b1 { width: 360px; height: 360px; background: #36d1dc; top: -80px; left: -80px; }
        .blob.b2 { width: 420px; height: 420px; background: #1D8AC9; bottom: -120px; right: -100px; animation-delay: -6s; }
        .blob.b3 { width: 220px; height: 220px; background: #5eead4; top: 50%; left: 65%; animation-delay: -3s; opacity: 0.3; }
        @keyframes blobFloat {
            0%, 100% { transform: translate(0,0) scale(1); }
            50%      { transform: translate(20px,-30px) scale(1.06); }
        }

        .auth-shell {
            position: relative; z-index: 2;
            width: 100%; max-width: 440px;
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
            box-shadow: 0 24px 64px rgba(0,0,0,0.32), 0 8px 24px rgba(29,138,201,0.18);
        }

        .auth-hero {
            position: relative;
            padding: 28px 24px 22px;
            text-align: center;
            background: linear-gradient(135deg, var(--skj-blue) 0%, var(--skj-blue-light) 100%);
            color: #fff;
            overflow: hidden;
        }
        .auth-hero::after {
            content: ""; position: absolute; inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,0.25), transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,0.15), transparent 50%);
            pointer-events: none;
        }
        .icon-wrap {
            width: 84px; height: 84px;
            background: rgba(255,255,255,0.18);
            border: 2px solid rgba(255,255,255,0.35);
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 14px;
            position: relative; z-index: 1;
            backdrop-filter: blur(6px);
        }
        .icon-wrap i { font-size: 34px; color: #fff; }
        .auth-hero h1 {
            font-size: 20px; font-weight: 700;
            letter-spacing: 0.3px;
            position: relative; z-index: 1;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .subtitle {
            font-size: 13px; opacity: 0.92;
            margin-top: 6px;
            position: relative; z-index: 1;
            line-height: 1.5;
            padding: 0 8px;
        }

        .auth-body { padding: 26px 28px 24px; }

        .alert-status {
            background: linear-gradient(135deg, #ecfdf5, #d1fae5);
            border-left: 4px solid #10b981;
            padding: 14px 16px;
            border-radius: 12px;
            margin-bottom: 18px;
            font-size: 13.5px;
            color: #065f46;
            display: flex; gap: 10px; align-items: flex-start;
            line-height: 1.55;
        }
        .alert-status i { font-size: 17px; margin-top: 1px; color: #10b981; }

        .info-banner {
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-left: 4px solid var(--skj-blue);
            padding: 11px 14px;
            border-radius: 10px;
            margin-bottom: 18px;
            font-size: 13px;
            color: #1e3a8a;
            display: flex; gap: 8px; align-items: flex-start;
            line-height: 1.55;
        }
        .info-banner i { margin-top: 2px; color: var(--skj-blue); }

        .field { margin-bottom: 18px; }
        .field label {
            display: block;
            font-size: 13px; font-weight: 600;
            color: #334155;
            margin-bottom: 6px;
        }
        .input-wrap { position: relative; display: flex; align-items: center; }
        .input-wrap > i.input-icon {
            position: absolute; left: 14px;
            color: #94a3b8; font-size: 15px;
            transition: color 0.2s;
            pointer-events: none;
        }
        .input-wrap input {
            width: 100%;
            height: 48px;
            padding: 0 16px 0 42px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            background: #f8fafc;
            font-size: 15px;
            font-family: inherit;
            color: #0f172a;
            transition: all 0.2s;
            outline: none;
        }
        .input-wrap input::placeholder { color: #94a3b8; }
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
        .invalid-msg {
            display: block;
            margin-top: 6px;
            font-size: 12px; color: #ef4444;
            font-weight: 500;
        }

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

        .back-link-wrap {
            margin-top: 22px;
            text-align: center;
            padding-top: 18px;
            border-top: 1px solid #e2e8f0;
        }
        .back-link {
            color: #475569;
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            display: inline-flex; align-items: center; gap: 6px;
            padding: 8px 14px;
            border-radius: 8px;
            transition: all 0.15s;
        }
        .back-link:hover {
            color: var(--skj-blue);
            background: #f1f5f9;
            text-decoration: none;
        }

        .brand-foot {
            margin-top: 22px;
            text-align: center;
            color: rgba(255,255,255,0.55);
            font-size: 11px;
            letter-spacing: 0.4px;
            padding: 0 8px;
        }
        .brand-foot strong { color: #fff; opacity: 0.9; }

        @media (max-width: 480px) {
            .login-stage {
                padding-left:  max(12px, env(safe-area-inset-left));
                padding-right: max(12px, env(safe-area-inset-right));
                padding-top:    14px;
                padding-bottom: 14px;
            }
            .auth-shell { max-width: 100%; }
            .auth-hero { padding: 22px 18px 18px; }
            .icon-wrap { width: 72px; height: 72px; margin-bottom: 10px; }
            .icon-wrap i { font-size: 28px; }
            .auth-hero h1 { font-size: 18px; }
            .auth-body { padding: 22px 18px 18px; }
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

<div class="login-stage">
    <div class="blob b1"></div>
    <div class="blob b2"></div>
    <div class="blob b3"></div>

    <div class="auth-shell">
        <div class="auth-card">
            <div class="auth-hero">
                <div class="icon-wrap"><i class="fa fa-unlock-alt"></i></div>
                <h1>ลืมรหัสผ่าน?</h1>
                <div class="subtitle">กรอกอีเมลของคุณ ระบบจะส่งลิงก์รีเซ็ตรหัสผ่านให้ทางอีเมล</div>
            </div>

            <div class="auth-body">
                @if (session('status'))
                    <div class="alert-status">
                        <i class="fa fa-check-circle"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <div class="info-banner">
                    <i class="fa fa-info-circle"></i>
                    <span>ลิงก์รีเซ็ตจะมีอายุ <strong>60 นาที</strong> หลังได้รับอีเมล กรุณาตรวจสอบในกล่องจดหมาย หรือโฟลเดอร์ Spam/Junk</span>
                </div>

                <form method="POST" action="{{ route('password.email') }}" autocomplete="on">
                    @csrf

                    <div class="field">
                        <label for="email">อีเมลที่ลงทะเบียน</label>
                        <div class="input-wrap">
                            <input id="email" type="email" name="email"
                                value="{{ old('email') }}"
                                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                required autocomplete="email" autofocus
                                placeholder="example@email.com">
                            <i class="fa fa-envelope input-icon"></i>
                        </div>
                        @error('email')
                            <span class="invalid-msg"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa fa-paper-plane"></i> ส่งลิงก์รีเซ็ตรหัสผ่าน
                    </button>
                </form>

                <div class="back-link-wrap">
                    <a href="{{ route('login') }}" class="back-link">
                        <i class="fa fa-arrow-left"></i> กลับเข้าสู่หน้าล็อกอิน
                    </a>
                </div>
            </div>
        </div>

        <div class="brand-foot">
            © {{ date('Y') }} <strong>SKJ Japan Shipping</strong> · บริการนำเข้าสินค้าจากญี่ปุ่นครบวงจร
        </div>
    </div>
</div>

</body>
</html>
