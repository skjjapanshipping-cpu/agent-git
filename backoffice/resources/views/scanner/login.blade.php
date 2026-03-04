<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scanner Login — SKJ Japan Shipping</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; }
        body {
            font-family: 'Prompt', sans-serif;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #0f172a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: #1e293b;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 20px 60px rgba(0,0,0,0.5);
        }

        .login-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            padding: 32px 24px;
            text-align: center;
            color: #fff;
        }
        .login-header .icon {
            width: 64px;
            height: 64px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            margin-bottom: 12px;
        }
        .login-header h1 { font-size: 22px; font-weight: 700; }
        .login-header p { font-size: 13px; opacity: 0.85; margin-top: 4px; }

        .login-body { padding: 28px 24px; }

        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #94a3b8;
            margin-bottom: 6px;
        }
        .input-wrap {
            position: relative;
        }
        .input-wrap i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 16px;
        }
        .input-wrap input {
            width: 100%;
            padding: 14px 16px 14px 44px;
            border: 2px solid #334155;
            border-radius: 12px;
            background: #0f172a;
            color: #fff;
            font-size: 16px;
            font-family: 'Prompt', sans-serif;
            transition: border-color 0.2s;
        }
        .input-wrap input:focus {
            outline: none;
            border-color: #3b82f6;
        }
        .input-wrap input::placeholder { color: #475569; }

        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #94a3b8;
        }
        .remember-row input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #3b82f6;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #2563eb, #3b82f6);
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
            letter-spacing: 1px;
            transition: opacity 0.2s;
        }
        .btn-login:hover { opacity: 0.9; }
        .btn-login:active { transform: scale(0.98); }

        .error-box {
            background: #7f1d1d;
            border: 1px solid #dc2626;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 18px;
            color: #fca5a5;
            font-size: 13px;
            font-weight: 500;
        }

        .login-footer {
            text-align: center;
            padding: 0 24px 24px;
            font-size: 11px;
            color: #475569;
        }
    </style>
</head>
<body>

<div class="login-card">
    <div class="login-header">
        <div class="icon"><i class="fa fa-barcode"></i></div>
        <h1>Scanner Login</h1>
        <p>ระบบสแกนพัสดุ — SKJ Japan Shipping</p>
    </div>

    <div class="login-body">
        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    {{ $error }}<br>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ url('/scanner/login') }}">
            @csrf

            <div class="form-group">
                <label>ID (อีเมล)</label>
                <div class="input-wrap">
                    <i class="fa fa-user"></i>
                    <input type="text" name="email" value="{{ old('email') }}" placeholder="กรอก ID ของคุณ" required autofocus>
                </div>
            </div>

            <div class="form-group">
                <label>รหัสผ่าน</label>
                <div class="input-wrap">
                    <i class="fa fa-lock"></i>
                    <input type="password" name="password" placeholder="กรอกรหัสผ่าน" required>
                </div>
            </div>

            <div class="remember-row">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin:0;cursor:pointer;">จดจำฉัน</label>
            </div>

            <button type="submit" class="btn-login">
                <i class="fa fa-sign-in"></i> เข้าสู่ระบบ
            </button>
        </form>
    </div>

    <div class="login-footer">
        สำหรับพนักงานเช็คสินค้าเท่านั้น
    </div>
</div>

</body>
</html>
