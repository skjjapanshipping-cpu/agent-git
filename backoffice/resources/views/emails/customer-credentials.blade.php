<!DOCTYPE html>
<html lang="th">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ยินดีต้อนรับสู่ SKJ Japan Shipping</title>
<style>
  body { margin:0; padding:0; background:#f1f5f9; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Sarabun", "Noto Sans Thai", sans-serif; color:#1e293b; line-height:1.55; }
  .wrap { max-width:640px; margin:0 auto; padding:24px 16px; }
  .card { background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.06); }
  .header { background: linear-gradient(135deg, #1D8AC9 0%, #0c5e8e 100%); color:#fff; padding:32px 28px; text-align:center; }
  .header h1 { margin:8px 0 4px; font-size:22px; font-weight:700; letter-spacing:.3px; }
  .header p { margin:0; opacity:.85; font-size:14px; }
  .body { padding:28px; }
  .body h2 { font-size:18px; margin:0 0 12px; color:#0c5e8e; }
  .greeting { font-size:16px; margin-bottom:18px; }
  .code-box { background: linear-gradient(135deg, #1D8AC9 0%, #0c5e8e 100%); color:#fff; padding:20px 24px; border-radius:14px; text-align:center; margin:18px 0; }
  .code-box .label { font-size:13px; opacity:.85; text-transform:uppercase; letter-spacing:1.2px; }
  .code-box .code { font-size:32px; font-weight:800; letter-spacing:2px; margin-top:6px; font-family: "SF Mono", "Menlo", monospace; }
  .info-grid { display:block; margin:18px 0; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
  .info-row { padding:14px 18px; border-bottom:1px solid #f1f5f9; display:flex; justify-content:space-between; align-items:center; gap:12px; }
  .info-row:last-child { border-bottom:0; }
  .info-row .k { color:#64748b; font-size:13px; min-width:120px; }
  .info-row .v { color:#0f172a; font-weight:600; font-size:15px; text-align:right; word-break:break-all; }
  .info-row .v.mono { font-family:"SF Mono","Menlo",monospace; color:#1D8AC9; }
  .warehouse { background:#fffbeb; border:1px solid #fde68a; border-radius:14px; padding:18px 20px; margin:20px 0; }
  .warehouse h3 { margin:0 0 10px; font-size:15px; color:#92400e; }
  .warehouse .addr { font-size:15px; line-height:1.7; color:#1f2937; white-space:pre-line; }
  .warehouse .addr-en { margin-top:8px; padding-top:8px; border-top:1px dashed #fde68a; font-size:13px; color:#6b7280; }
  .warehouse .phone { margin-top:8px; font-size:14px; color:#1f2937; }
  .alert { background:#dbeafe; border-left:4px solid #1D8AC9; padding:14px 16px; border-radius:8px; margin:18px 0; font-size:14px; color:#1e3a8a; }
  .alert strong { color:#0c5e8e; }
  .btn { display:inline-block; background:#1D8AC9; color:#fff !important; text-decoration:none; padding:14px 32px; border-radius:10px; font-weight:600; margin:18px 0 6px; }
  .footer { text-align:center; padding:22px 28px; color:#64748b; font-size:13px; background:#f8fafc; border-top:1px solid #e2e8f0; }
  .footer a { color:#1D8AC9; text-decoration:none; }
  .small { font-size:12px; color:#94a3b8; margin-top:14px; }
</style>
</head>
<body>
  <div class="wrap">
    <div class="card">

      <div class="header">
        <div style="font-size:32px;">🚢 ✈️</div>
        <h1>ยินดีต้อนรับสู่ SKJ Japan Shipping</h1>
        <p>บริการนำเข้าสินค้าจากญี่ปุ่นแบบครบวงจร</p>
      </div>

      <div class="body">
        <p class="greeting">เรียนคุณ <strong>{{ $user->name }}</strong>,</p>
        <p>แอดมินได้เปิดบัญชีให้คุณเรียบร้อยแล้ว ด้านล่างคือข้อมูลสำคัญที่คุณต้องเก็บไว้ใช้งาน</p>

        <div class="code-box">
          <div class="label">รหัสลูกค้าของคุณ</div>
          <div class="code">{{ $customerno }}</div>
        </div>

        <h2>🔑 ข้อมูลการเข้าสู่ระบบ</h2>
        <div class="info-grid">
          <div class="info-row">
            <div class="k">อีเมล</div>
            <div class="v mono">{{ $user->email }}</div>
          </div>
          <div class="info-row">
            <div class="k">รหัสผ่าน</div>
            <div class="v mono">{{ $plainPassword }}</div>
          </div>
          @if($user->mobile)
          <div class="info-row">
            <div class="k">เบอร์โทรศัพท์</div>
            <div class="v">{{ $user->mobile }}</div>
          </div>
          @endif
        </div>

        <a href="{{ $loginUrl }}" class="btn">🔓 เข้าสู่ระบบ</a>

        <div class="alert">
          <strong>⚠️ ความปลอดภัย:</strong> กรุณาเปลี่ยนรหัสผ่านหลังเข้าสู่ระบบครั้งแรก เพื่อความปลอดภัยของบัญชีคุณ
        </div>

        <h2 style="margin-top:30px;">🏭 ที่อยู่โกดังที่ญี่ปุ่น</h2>
        <p style="font-size:14px; color:#64748b; margin:0 0 12px;">สำหรับแจ้งให้ร้านค้าในญี่ปุ่นส่งของมายังโกดัง — แยกตามประเภทการขนส่ง</p>

        @foreach(['sea','air'] as $t)
          @php
            $w = $warehouses[$t] ?? null;
            $bg = $t === 'sea' ? '#fffbeb' : '#dbeafe';
            $border = $t === 'sea' ? '#fde68a' : '#93c5fd';
            $titleColor = $t === 'sea' ? '#92400e' : '#1e40af';
          @endphp
          @if($w && (!empty($w['address_jp']) || !empty($w['address_en'])))
            <div style="background:{{ $bg }}; border:1px solid {{ $border }}; border-radius:14px; padding:16px 20px; margin:0 0 14px;">
              <h3 style="margin:0 0 10px; font-size:15px; color:{{ $titleColor }};">{{ $w['icon'] }} {{ $w['label'] }}</h3>

              {{-- ENGLISH ADDRESS (PRIMARY) --}}
              @if(!empty($w['address_en']))
                <div style="font-weight:700; font-size:16px; color:{{ $titleColor }}; margin-bottom:4px; letter-spacing:.2px;">{{ $w['name_en'] ?? $w['name_jp'] ?? '' }}</div>
                <div style="font-size:15px; line-height:1.65; color:#1f2937;">
                  <span style="font-weight:600;">{{ $w['postcode'] ?? '' }}</span><br>
                  {{ $w['address_en'] }}
                  @if(!empty($w['phone']))<br><span style="font-weight:600;">📞 Tel: {{ $w['phone'] }}</span>@endif
                </div>
                {{-- JAPANESE ADDRESS (SECONDARY) --}}
                @if(!empty($w['address_jp']))
                  <div style="margin-top:10px; padding-top:10px; border-top:1px dashed {{ $border }}; font-size:12px; color:#6b7280; line-height:1.55;">
                    <em style="color:#475569;">{{ $w['name_jp'] }}</em><br>
                    〒{{ $w['postcode'] ?? '' }}<br>
                    {{ $w['address_jp'] }}
                  </div>
                @endif
              @else
                {{-- fallback: ถ้าไม่มี EN ใช้ JP เด่นแทน --}}
                <div style="font-weight:700; font-size:16px; color:{{ $titleColor }}; margin-bottom:4px;">{{ $w['name_jp'] ?? '' }}</div>
                <div style="font-size:15px; line-height:1.65; color:#1f2937; white-space:pre-line;">〒{{ $w['postcode'] ?? '' }}
{{ $w['address_jp'] ?? '' }}</div>
                @if(!empty($w['phone']))
                  <div style="margin-top:6px; font-size:14px; color:#1f2937;">📞 โทร: {{ $w['phone'] }}</div>
                @endif
              @endif
            </div>
          @endif
        @endforeach

        <div class="alert" style="background:#fef3c7; border-left-color:#f59e0b; color:#78350f;">
          <strong>📝 สำคัญมาก:</strong> {{ $contactNote ?? 'กรุณาเขียนรหัสลูกค้า ' . $customerno . ' บนกล่องพัสดุทุกครั้ง' }}
          <div style="margin-top:8px; padding:10px; background:#fff; border-radius:8px; text-align:center; font-family:'SF Mono','Menlo',monospace; font-size:18px; font-weight:700; color:#1D8AC9;">
            รหัสลูกค้า: {{ $customerno }}
          </div>
        </div>

        <p class="small">หากมีข้อสงสัย ติดต่อแอดมินผ่าน LINE: <a href="{{ $support['line_url'] ?? '#' }}">{{ $support['line_id'] ?? '@skjjapan' }}</a> หรือโทร <a href="tel:{{ $support['phone'] ?? '' }}">{{ $support['phone'] ?? '' }}</a></p>
      </div>

      <div class="footer">
        <strong>SKJ Japan Shipping Company</strong><br>
        <a href="https://skjjapanshipping.com">https://skjjapanshipping.com</a>
        <div class="small" style="margin-top:8px;">อีเมลนี้สร้างโดยระบบ กรุณาอย่าตอบกลับ</div>
      </div>

    </div>
  </div>
</body>
</html>
