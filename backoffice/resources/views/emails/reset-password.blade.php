<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title>รีเซ็ตรหัสผ่าน — SKJ Japan Shipping</title>
<style>
    body {
        margin: 0; padding: 0;
        background: #f1f5f9;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Sarabun", "Noto Sans Thai", "Prompt", sans-serif;
        color: #1e293b;
        line-height: 1.6;
        -webkit-text-size-adjust: 100%;
    }
    .wrap { max-width: 600px; margin: 0 auto; padding: 24px 16px; }
    .card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }

    /* Header (Hero gradient) */
    .header {
        background: linear-gradient(135deg, #1D8AC9 0%, #36d1dc 100%);
        color: #fff;
        padding: 36px 28px 28px;
        text-align: center;
        position: relative;
    }
    .header .icon-circle {
        width: 76px; height: 76px;
        background: rgba(255,255,255,0.18);
        border: 2px solid rgba(255,255,255,0.35);
        border-radius: 50%;
        display: inline-block;
        line-height: 76px;
        font-size: 36px;
        margin-bottom: 14px;
    }
    .header h1 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        letter-spacing: 0.3px;
        text-shadow: 0 2px 6px rgba(0,0,0,0.15);
    }
    .header .subtitle {
        margin: 6px 0 0;
        font-size: 13.5px;
        opacity: 0.92;
    }

    /* Body */
    .body { padding: 28px 28px 24px; }
    .greeting { font-size: 16px; margin: 0 0 14px; }
    .greeting strong { color: #0c5e8e; }
    .lead {
        font-size: 14.5px;
        color: #475569;
        margin: 0 0 22px;
    }

    /* CTA Button */
    .btn-wrap { text-align: center; margin: 26px 0 18px; }
    .btn {
        display: inline-block;
        background: linear-gradient(135deg, #1D8AC9 0%, #36d1dc 100%);
        color: #fff !important;
        text-decoration: none;
        padding: 16px 42px;
        border-radius: 12px;
        font-weight: 700;
        font-size: 15.5px;
        letter-spacing: 0.4px;
        box-shadow: 0 8px 20px rgba(29,138,201,0.30);
    }

    /* Expiration Notice */
    .notice {
        background: linear-gradient(135deg, #fffbeb, #fef3c7);
        border-left: 4px solid #f59e0b;
        padding: 14px 16px;
        border-radius: 10px;
        margin: 18px 0;
        font-size: 13.5px;
        color: #78350f;
        line-height: 1.6;
    }
    .notice strong { color: #b45309; }

    /* Manual Link Block */
    .manual-link {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px 16px;
        margin: 22px 0 10px;
        font-size: 12.5px;
        color: #64748b;
        word-break: break-all;
    }
    .manual-link .label {
        display: block;
        font-weight: 600;
        color: #334155;
        margin-bottom: 6px;
        font-size: 12px;
    }
    .manual-link a {
        color: #1D8AC9;
        text-decoration: underline;
    }

    /* Security Note */
    .security {
        background: linear-gradient(135deg, #f0fdf4, #dcfce7);
        border-left: 4px solid #10b981;
        padding: 12px 16px;
        border-radius: 10px;
        margin: 18px 0 6px;
        font-size: 13px;
        color: #065f46;
    }
    .security strong { color: #047857; }

    /* Divider */
    .divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, #e2e8f0, transparent);
        margin: 22px 0 16px;
    }

    /* Footer */
    .footer {
        background: #f8fafc;
        padding: 22px 28px 26px;
        text-align: center;
        border-top: 1px solid #e2e8f0;
    }
    .footer .contact {
        font-size: 13px;
        color: #64748b;
        margin: 0 0 10px;
    }
    .footer .contact a {
        color: #1D8AC9;
        text-decoration: none;
        font-weight: 600;
        margin: 0 4px;
    }
    .footer .brand {
        font-size: 12.5px;
        color: #94a3b8;
        margin: 0;
    }
    .footer .brand strong { color: #1D8AC9; }
    .small { font-size: 11.5px; color: #cbd5e1; margin-top: 10px; }

    /* Mobile */
    @media (max-width: 480px) {
        .wrap { padding: 12px 8px; }
        .body, .header, .footer { padding-left: 18px; padding-right: 18px; }
        .header .icon-circle { width: 64px; height: 64px; line-height: 64px; font-size: 30px; }
        .header h1 { font-size: 19px; }
        .btn { padding: 14px 32px; font-size: 14.5px; }
    }
</style>
</head>
<body>
<div class="wrap">
    <div class="card">

        <div class="header">
            <div class="icon-circle">🔐</div>
            <h1>รีเซ็ตรหัสผ่าน</h1>
            <p class="subtitle">SKJ Japan Shipping · บริการนำเข้าจากญี่ปุ่น</p>
        </div>

        <div class="body">
            <p class="greeting">เรียนคุณ <strong>{{ $user->name ?? 'สมาชิก' }}</strong></p>
            <p class="lead">
                เราได้รับคำขอรีเซ็ตรหัสผ่านสำหรับบัญชีของคุณ
                หากคุณต้องการเปลี่ยนรหัสผ่าน กดปุ่มด้านล่างเพื่อตั้งรหัสผ่านใหม่
            </p>

            <div class="btn-wrap">
                <a href="{{ $resetUrl }}" class="btn">🔑 ตั้งรหัสผ่านใหม่</a>
            </div>

            <div class="notice">
                ⏱️ <strong>ลิงก์นี้จะหมดอายุภายใน {{ $expireMinutes }} นาที</strong>
                หลังจากนั้นคุณต้องขอรีเซ็ตใหม่อีกครั้ง เพื่อความปลอดภัยของบัญชี
            </div>

            <div class="security">
                🛡️ <strong>หากคุณไม่ได้ขอรีเซ็ตรหัสผ่าน</strong>
                — โปรดข้ามอีเมลฉบับนี้ บัญชีของคุณยังคงปลอดภัยอยู่ ไม่ต้องดำเนินการใดๆ
            </div>

            <div class="manual-link">
                <span class="label">หากปุ่มข้างต้นกดไม่ได้ ให้คัดลอกลิงก์ด้านล่างไปวางที่ Browser:</span>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </div>

            <div class="divider"></div>

            <p style="font-size:14px; color:#475569; margin:0;">
                ขอบคุณที่ใช้บริการ <strong style="color:#0c5e8e;">SKJ Japan Shipping</strong>
            </p>
        </div>

        <div class="footer">
            @if(!empty($support['line_id']) || !empty($support['phone']))
                <p class="contact">
                    มีคำถาม? ติดต่อแอดมินได้ที่:
                    @if(!empty($support['line_id']))
                        <a href="{{ $support['line_url'] ?? '#' }}">💬 LINE {{ $support['line_id'] }}</a>
                    @endif
                    @if(!empty($support['line_id']) && !empty($support['phone']))
                        ·
                    @endif
                    @if(!empty($support['phone']))
                        <a href="tel:{{ $support['phone'] }}">📞 {{ $support['phone'] }}</a>
                    @endif
                </p>
            @endif
            <p class="brand">
                © {{ date('Y') }} <strong>SKJ Japan Shipping</strong> · บริการนำเข้าสินค้าจากญี่ปุ่นครบวงจร
            </p>
            <p class="small">อีเมลฉบับนี้ส่งจากระบบอัตโนมัติ กรุณาอย่าตอบกลับ</p>
        </div>
    </div>
</div>
</body>
</html>
