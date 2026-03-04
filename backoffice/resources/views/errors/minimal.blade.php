<!DOCTYPE html>
<html lang="th">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link rel="dns-prefetch" href="//fonts.gstatic.com">
        <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;600;700&display=swap" rel="stylesheet">
        <style>
            * { box-sizing: border-box; margin: 0; padding: 0; }
            html, body {
                background: linear-gradient(135deg, #f5f7fa 0%, #e4e9f0 100%);
                color: #333;
                font-family: 'Noto Sans Thai', sans-serif;
                height: 100vh;
            }
            .error-container {
                display: flex;
                align-items: center;
                justify-content: center;
                min-height: 100vh;
                padding: 20px;
            }
            .error-card {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 10px 40px rgba(0,0,0,0.08);
                text-align: center;
                padding: 50px 40px;
                max-width: 480px;
                width: 100%;
            }
            .error-code {
                font-size: 100px;
                font-weight: 700;
                background: linear-gradient(135deg, #1D8AC9, #155d8a);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                line-height: 1;
                margin-bottom: 16px;
            }
            .error-message {
                font-size: 18px;
                color: #555;
                font-weight: 400;
                margin-bottom: 30px;
                line-height: 1.6;
            }
            .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
            .btn {
                display: inline-flex;
                align-items: center;
                gap: 6px;
                padding: 10px 24px;
                border-radius: 8px;
                font-size: 14px;
                font-weight: 600;
                text-decoration: none;
                transition: all 0.2s;
            }
            .btn-primary {
                background: #1D8AC9;
                color: #fff;
            }
            .btn-primary:hover { background: #155d8a; }
            .btn-secondary {
                background: #e9ecef;
                color: #495057;
            }
            .btn-secondary:hover { background: #dee2e6; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <div class="error-card">
                <div class="error-code">@yield('code')</div>
                <p class="error-message">@yield('message')</p>
                <div class="error-actions">
                    <a href="javascript:history.back()" class="btn btn-secondary">← ย้อนกลับ</a>
                    <a href="/" class="btn btn-primary">🏠 หน้าหลัก</a>
                </div>
            </div>
        </div>
    </body>
</html>
