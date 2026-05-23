<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>ตั้งรหัสผ่านใหม่ — SKJ Japan Shipping</title>

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
            width: 100%; max-width: 460px;
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
            padding: 26px 24px 20px;
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
            width: 76px; height: 76px;
            background: rgba(255,255,255,0.18);
            border: 2px solid rgba(255,255,255,0.35);
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 12px;
            position: relative; z-index: 1;
            backdrop-filter: blur(6px);
        }
        .icon-wrap i { font-size: 32px; color: #fff; }
        .auth-hero h1 {
            font-size: 20px; font-weight: 700;
            letter-spacing: 0.3px;
            position: relative; z-index: 1;
            text-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .subtitle {
            font-size: 12.5px; opacity: 0.92;
            margin-top: 5px;
            position: relative; z-index: 1;
            padding: 0 8px;
        }

        .auth-body { padding: 24px 28px 22px; }

        .field { margin-bottom: 16px; }
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

        /* ---------- Password strength ---------- */
        .pw-strength {
            margin-top: 8px;
            font-size: 11.5px;
            color: #64748b;
        }
        .pw-bar {
            height: 5px;
            background: #e2e8f0;
            border-radius: 3px;
            overflow: hidden;
            margin: 6px 0 4px;
        }
        .pw-bar-fill {
            height: 100%;
            width: 0%;
            background: #ef4444;
            transition: width 0.3s, background 0.3s;
            border-radius: 3px;
        }
        .pw-bar-fill.lvl-1 { width: 25%;  background: #ef4444; }
        .pw-bar-fill.lvl-2 { width: 50%;  background: #f59e0b; }
        .pw-bar-fill.lvl-3 { width: 75%;  background: #3b82f6; }
        .pw-bar-fill.lvl-4 { width: 100%; background: #10b981; }
        .pw-label { font-weight: 600; }
        .pw-rules {
            display: grid; grid-template-columns: 1fr 1fr; gap: 4px 12px;
            font-size: 11.5px;
            color: #94a3b8;
            margin-top: 6px;
        }
        .pw-rules .rule { display: inline-flex; align-items: center; gap: 5px; }
        .pw-rules .rule.ok { color: #10b981; }
        .pw-rules .rule i { font-size: 11px; }

        .match-msg {
            margin-top: 6px;
            font-size: 12px;
            font-weight: 500;
            display: inline-flex; align-items: center; gap: 5px;
        }
        .match-msg.ok  { color: #10b981; }
        .match-msg.bad { color: #ef4444; }

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
            margin-top: 6px;
        }
        .btn-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px rgba(29,138,201,0.45), inset 0 -2px 0 rgba(0,0,0,0.12);
        }
        .btn-submit:active { transform: translateY(0); }

        .back-link-wrap {
            margin-top: 20px;
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
            .auth-hero { padding: 22px 18px 16px; }
            .icon-wrap { width: 64px; height: 64px; margin-bottom: 10px; }
            .icon-wrap i { font-size: 26px; }
            .auth-hero h1 { font-size: 18px; }
            .auth-body { padding: 22px 18px 18px; }
            .pw-rules { grid-template-columns: 1fr; }
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
                <div class="icon-wrap"><i class="fa fa-key"></i></div>
                <h1>ตั้งรหัสผ่านใหม่</h1>
                <div class="subtitle">กรุณาตั้งรหัสผ่านใหม่ที่ปลอดภัยและจำง่าย</div>
            </div>

            <div class="auth-body">
                <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="field">
                        <label for="email">อีเมล</label>
                        <div class="input-wrap">
                            <input id="email" type="email" name="email"
                                value="{{ $email ?? old('email') }}"
                                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
                                required autocomplete="email"
                                placeholder="example@email.com">
                            <i class="fa fa-envelope input-icon"></i>
                        </div>
                        @error('email')
                            <span class="invalid-msg"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">รหัสผ่านใหม่</label>
                        <div class="input-wrap">
                            <input id="password" type="password" name="password"
                                class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                                required autocomplete="new-password"
                                placeholder="กรอกรหัสผ่านใหม่">
                            <i class="fa fa-lock input-icon"></i>
                            <button type="button" class="eye-btn" onclick="togglePassword('password', 'password-icon')" aria-label="แสดง/ซ่อน">
                                <i class="fa fa-eye" id="password-icon"></i>
                            </button>
                        </div>

                        <div class="pw-strength">
                            <div class="pw-bar"><div class="pw-bar-fill" id="pwBarFill"></div></div>
                            <span>ความปลอดภัย: <span class="pw-label" id="pwLabel">—</span></span>
                            <div class="pw-rules" id="pwRules">
                                <span class="rule" data-rule="len"><i class="fa fa-circle-o"></i> อย่างน้อย 8 ตัวอักษร</span>
                                <span class="rule" data-rule="upper"><i class="fa fa-circle-o"></i> มีตัวพิมพ์ใหญ่</span>
                                <span class="rule" data-rule="lower"><i class="fa fa-circle-o"></i> มีตัวพิมพ์เล็ก</span>
                                <span class="rule" data-rule="num"><i class="fa fa-circle-o"></i> มีตัวเลข</span>
                            </div>
                        </div>

                        @error('password')
                            <span class="invalid-msg"><i class="fa fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password-confirm">ยืนยันรหัสผ่านใหม่</label>
                        <div class="input-wrap">
                            <input id="password-confirm" type="password" name="password_confirmation"
                                required autocomplete="new-password"
                                placeholder="พิมพ์รหัสผ่านใหม่อีกครั้ง">
                            <i class="fa fa-lock input-icon"></i>
                            <button type="button" class="eye-btn" onclick="togglePassword('password-confirm', 'confirm-icon')" aria-label="แสดง/ซ่อน">
                                <i class="fa fa-eye" id="confirm-icon"></i>
                            </button>
                        </div>
                        <span class="match-msg" id="matchMsg"></span>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa fa-check-circle"></i> ยืนยันการเปลี่ยนรหัสผ่าน
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

<script>
function togglePassword(inputId, iconId) {
    var input = document.getElementById(inputId);
    var icon = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
    }
}

(function () {
    var pw      = document.getElementById('password');
    var pw2     = document.getElementById('password-confirm');
    var bar     = document.getElementById('pwBarFill');
    var label   = document.getElementById('pwLabel');
    var matchEl = document.getElementById('matchMsg');
    var rules   = document.querySelectorAll('#pwRules .rule');

    function checkRules(val) {
        return {
            len:   val.length >= 8,
            upper: /[A-Z]/.test(val),
            lower: /[a-z]/.test(val),
            num:   /[0-9]/.test(val)
        };
    }

    function updateStrength() {
        var v = pw.value;
        var r = checkRules(v);
        var passed = (r.len ? 1 : 0) + (r.upper ? 1 : 0) + (r.lower ? 1 : 0) + (r.num ? 1 : 0);

        rules.forEach(function (el) {
            var key = el.getAttribute('data-rule');
            var icon = el.querySelector('i');
            if (r[key]) {
                el.classList.add('ok');
                icon.classList.remove('fa-circle-o'); icon.classList.add('fa-check-circle');
            } else {
                el.classList.remove('ok');
                icon.classList.remove('fa-check-circle'); icon.classList.add('fa-circle-o');
            }
        });

        bar.classList.remove('lvl-1','lvl-2','lvl-3','lvl-4');
        var labels = ['—','อ่อน','พอใช้','ดี','แข็งแกร่ง'];
        if (v.length > 0 && passed > 0) bar.classList.add('lvl-' + passed);
        label.textContent = labels[passed] || '—';
    }

    function updateMatch() {
        if (pw2.value.length === 0) { matchEl.textContent = ''; matchEl.className = 'match-msg'; return; }
        if (pw.value === pw2.value) {
            matchEl.innerHTML = '<i class="fa fa-check-circle"></i> รหัสผ่านตรงกัน';
            matchEl.className = 'match-msg ok';
        } else {
            matchEl.innerHTML = '<i class="fa fa-times-circle"></i> รหัสผ่านไม่ตรงกัน';
            matchEl.className = 'match-msg bad';
        }
    }

    if (pw)  pw.addEventListener('input', function(){ updateStrength(); updateMatch(); });
    if (pw2) pw2.addEventListener('input', updateMatch);
})();
</script>

</body>
</html>
