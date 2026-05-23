@extends('layouts.app')

@section('template_title')
    ข้อมูลบัญชี — {{ strtoupper($customer->customerno) }}
@endsection

@section('content')
<section class="content container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-10">

            @if(!$credentials)
                <div class="alert alert-warning">
                    <i class="fa fa-exclamation-triangle"></i> ดูข้อมูลรหัสผ่านได้เพียงครั้งเดียวหลังเปิดบัญชีหรือรีเซ็ตรหัสผ่าน
                    หากต้องการรหัสผ่านใหม่ ให้กด "รีเซ็ตรหัสผ่าน" ในรายการลูกค้า
                </div>
                <div class="text-center">
                    <a href="{{ route('customers.index') }}" class="btn btn-primary"><i class="fa fa-list"></i> กลับไปรายการลูกค้า</a>
                </div>
            @else

            <!-- Success header -->
            <div class="card no-print" style="border:0; background:linear-gradient(135deg, #10b981 0%, #059669 100%); color:#fff; border-radius:14px; padding:20px 24px; margin-bottom:18px; box-shadow:0 4px 16px rgba(16,185,129,.3);">
                <div style="display:flex; align-items:center; gap:14px;">
                    <div style="font-size:36px;">{{ $credentials['is_reset'] ?? false ? '🔑' : '🎉' }}</div>
                    <div style="flex:1;">
                        <h4 style="margin:0; font-weight:700;">
                            {{ $credentials['is_reset'] ?? false ? 'รีเซ็ตรหัสผ่านสำเร็จ' : 'เปิดบัญชีลูกค้าสำเร็จ!' }}
                        </h4>
                        <p style="margin:4px 0 0; opacity:.9; font-size:14px;">
                            @if($credentials['email_status']==='sent')
                                ✅ ส่งอีเมล welcome พร้อมรหัสผ่านไปที่ <strong>{{ $credentials['email'] }}</strong> เรียบร้อย
                            @elseif($credentials['email_status']==='failed')
                                ⚠️ บัญชีถูกสร้างแล้ว แต่ส่งอีเมลไม่สำเร็จ — กรุณาแจ้งลูกค้าด้วยตัวเอง
                            @else
                                ⚠️ ไม่ได้ส่งอีเมล — กรุณาแจ้งลูกค้าด้วยตัวเอง
                            @endif
                        </p>
                    </div>
                </div>
                @if($credentials['email_status']==='failed' && !empty($credentials['email_error']))
                    <div style="margin-top:12px; padding:10px 12px; background:rgba(0,0,0,.15); border-radius:8px; font-size:13px; font-family:monospace;">
                        {{ $credentials['email_error'] }}
                    </div>
                @endif
            </div>

            <!-- Printable card -->
            <div id="print-area" class="card" style="border:0; border-radius:14px; box-shadow:0 4px 20px rgba(0,0,0,.08); overflow:hidden;">

                <div style="background:linear-gradient(135deg, #1D8AC9 0%, #0c5e8e 100%); color:#fff; padding:28px 32px; text-align:center;">
                    <div style="font-size:32px; margin-bottom:6px;">🚢 ✈️</div>
                    <h2 style="margin:0 0 4px; font-weight:700;">SKJ Japan Shipping</h2>
                    <p style="margin:0; opacity:.85;">ข้อมูลบัญชีของคุณ</p>
                </div>

                <div style="padding:28px 32px;">
                    <p style="font-size:16px;">เรียนคุณ <strong>{{ $credentials['name'] }}</strong>,</p>

                    <!-- Customer Code -->
                    <div style="background:linear-gradient(135deg, #1D8AC9 0%, #0c5e8e 100%); color:#fff; padding:24px; border-radius:14px; text-align:center; margin:18px 0;">
                        <div style="font-size:13px; opacity:.85; text-transform:uppercase; letter-spacing:1.2px;">รหัสลูกค้าของคุณ</div>
                        <div id="cust-code" style="font-size:36px; font-weight:800; letter-spacing:2px; margin-top:6px; font-family:'SF Mono','Menlo',monospace;">
                            {{ $credentials['customerno'] }}
                        </div>
                        <button class="no-print" onclick="copyText('{{ $credentials['customerno'] }}', this)" style="margin-top:10px; background:rgba(255,255,255,.2); color:#fff; border:1px solid rgba(255,255,255,.4); padding:6px 14px; border-radius:8px; cursor:pointer; font-size:13px;">
                            <i class="fa fa-copy"></i> Copy
                        </button>
                    </div>

                    <!-- Login Info -->
                    <h5 style="color:#0c5e8e; margin-top:24px;"><i class="fa fa-key"></i> ข้อมูลการเข้าสู่ระบบ</h5>
                    <table class="table" style="margin-bottom:0;">
                        <tbody>
                            <tr>
                                <td width="160" style="color:#64748b; vertical-align:middle;">อีเมล</td>
                                <td style="font-family:'SF Mono','Menlo',monospace; font-weight:600;">
                                    <span id="cust-email">{{ $credentials['email'] }}</span>
                                    <button class="no-print btn btn-sm btn-light ml-2" onclick="copyText('{{ $credentials['email'] }}', this)">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td style="color:#64748b; vertical-align:middle;">รหัสผ่าน</td>
                                <td style="font-family:'SF Mono','Menlo',monospace; font-weight:700; color:#1D8AC9; font-size:18px;">
                                    <span id="cust-password">{{ $credentials['plain_password'] }}</span>
                                    <button class="no-print btn btn-sm btn-light ml-2" onclick="copyText('{{ $credentials['plain_password'] }}', this)">
                                        <i class="fa fa-copy"></i>
                                    </button>
                                </td>
                            </tr>
                            @if($credentials['mobile'])
                            <tr>
                                <td style="color:#64748b; vertical-align:middle;">เบอร์โทร</td>
                                <td>{{ $credentials['mobile'] }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td style="color:#64748b; vertical-align:middle;">เข้าสู่ระบบที่</td>
                                <td><a href="{{ url('/login') }}" target="_blank" style="color:#1D8AC9;">{{ url('/login') }}</a></td>
                            </tr>
                        </tbody>
                    </table>

                    <div style="background:#fef3c7; border-left:4px solid #f59e0b; padding:12px 16px; border-radius:8px; margin:18px 0; font-size:14px; color:#78350f;">
                        <strong>⚠️ สำคัญ:</strong> กรุณาเก็บรหัสผ่านนี้ไว้ ระบบจะไม่แสดงรหัสผ่านนี้อีก หลังจากออกจากหน้านี้
                    </div>

                    <!-- Warehouse Addresses (Sea + Air) -->
                    <h5 style="color:#0c5e8e; margin-top:30px;"><i class="fa fa-building"></i> ที่อยู่โกดังในญี่ปุ่น</h5>
                    <p style="font-size:14px; color:#64748b;">แจ้งให้ร้านค้าในญี่ปุ่นส่งของมา — เลือกที่อยู่ตามประเภทขนส่งของออเดอร์</p>

                    <div class="row">
                        @foreach(['sea','air'] as $t)
                            @php
                                $w = $warehouses[$t] ?? null;
                                $bg = $t === 'sea' ? '#fffbeb' : '#dbeafe';
                                $border = $t === 'sea' ? '#fde68a' : '#93c5fd';
                                $titleColor = $t === 'sea' ? '#92400e' : '#1e40af';
                            @endphp
                            @if($w && (!empty($w['address_jp']) || !empty($w['address_en'])))
                            <div class="col-md-6 mb-3">
                                <div style="background:{{ $bg }}; border:1px solid {{ $border }}; border-radius:12px; padding:18px 22px; height:100%;">
                                    <div style="font-weight:700; font-size:14px; color:{{ $titleColor }}; margin-bottom:10px;">{{ $w['icon'] }} {{ $w['label'] }}</div>

                                    {{-- ENGLISH ADDRESS (PRIMARY — เด่น) --}}
                                    <div id="warehouse-{{ $t }}-text" style="font-size:15px; line-height:1.65; color:#0f172a;">
                                        @if(!empty($w['address_en']))
                                            <strong style="color:{{ $titleColor }}; font-size:16px; letter-spacing:.2px;">{{ $w['name_en'] ?? $w['name_jp'] }}</strong><br>
                                            <span style="font-weight:600;">{{ $w['postcode'] }}</span><br>
                                            <span>{{ $w['address_en'] }}</span>
                                            @if(!empty($w['phone']))<br><i class="fa fa-phone" style="margin-right:4px; color:{{ $titleColor }};"></i><span style="font-weight:600; font-family:'SF Mono','Menlo',monospace;">{{ $w['phone'] }}</span>@endif
                                        @else
                                            <strong style="color:{{ $titleColor }}; font-size:16px;">{{ $w['name_jp'] }}</strong><br>
                                            〒{{ $w['postcode'] }}<br>
                                            {{ $w['address_jp'] }}
                                            @if(!empty($w['phone']))<br><i class="fa fa-phone" style="margin-right:4px; color:{{ $titleColor }};"></i><span style="font-weight:600; font-family:'SF Mono','Menlo',monospace;">{{ $w['phone'] }}</span>@endif
                                        @endif

                                        {{-- JAPANESE ADDRESS (SECONDARY — รอง) --}}
                                        @if(!empty($w['address_jp']) && !empty($w['address_en']))
                                            <div style="margin-top:10px; padding-top:10px; border-top:1px dashed {{ $border }}; font-size:12px; color:#6b7280; line-height:1.55;">
                                                <em style="color:#475569;">{{ $w['name_jp'] }}</em><br>
                                                〒{{ $w['postcode'] }}<br>
                                                {{ $w['address_jp'] }}
                                            </div>
                                        @endif
                                    </div>

                                    <button class="no-print" onclick="copyWarehouse('{{ $t }}', this)" style="margin-top:12px; background:#fff; border:1px solid {{ $border }}; color:{{ $titleColor }}; padding:6px 14px; border-radius:8px; cursor:pointer; font-size:12px;">
                                        <i class="fa fa-copy"></i> Copy ที่อยู่ {{ $w['icon'] }}
                                    </button>
                                </div>
                            </div>
                            @endif
                        @endforeach
                    </div>

                    @if(!empty($contactNote))
                    <div style="background:#dbeafe; border-left:4px solid #1D8AC9; padding:14px 18px; border-radius:8px; margin:18px 0;">
                        <strong style="color:#0c5e8e;">📝 หมายเหตุ:</strong>
                        <div style="margin-top:6px; color:#1e3a8a;">{{ $contactNote }}</div>
                        <div style="margin-top:10px; padding:10px 12px; background:#fff; border-radius:8px; text-align:center; font-family:'SF Mono','Menlo',monospace; font-weight:700; color:#1D8AC9; font-size:18px;">
                            รหัสลูกค้า: {{ $credentials['customerno'] }}
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            <!-- Actions -->
            <div class="no-print" style="display:flex; gap:10px; justify-content:center; margin-top:20px; flex-wrap:wrap;">
                <a href="{{ route('customers.index') }}" class="btn btn-secondary"><i class="fa fa-list"></i> กลับไปรายการลูกค้า</a>
                <button onclick="window.print()" class="btn btn-info"><i class="fa fa-print"></i> พิมพ์</button>
                <button onclick="copyAll()" class="btn btn-warning"><i class="fa fa-copy"></i> Copy ทั้งหมด</button>
                <form action="{{ route('customers.resetPassword', $customer->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('ยืนยันรีเซ็ตรหัสผ่านใหม่ + ส่งอีเมลใหม่?')">
                    @csrf
                    <button type="submit" class="btn btn-danger"><i class="fa fa-key"></i> รีเซ็ตรหัสผ่าน + ส่งใหม่</button>
                </form>
            </div>

            @endif
        </div>
    </div>
</section>

<style>
@media print {
    .no-print, .sidebar-modern, .navbar, .sidebar-wrapper, .sidebar-header, .sidebar-logout, .main-header, .main-footer, footer, header { display:none !important; }
    body { background:#fff !important; }
    .content-wrapper { margin:0 !important; padding:0 !important; }
}
</style>

@endsection

@section('extra-script')
<script>
function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(()=>{
        if (btn) {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fa fa-check"></i>';
            setTimeout(()=>{ btn.innerHTML = orig; }, 1200);
        }
    });
}
function copyWarehouse(type, btn) {
    const el = document.getElementById('warehouse-' + type + '-text');
    if (!el) return;
    copyText(el.innerText, btn);
}
@if($credentials)
function copyAll() {
    const lines = [];
    lines.push('SKJ Japan Shipping — ข้อมูลบัญชี');
    lines.push('======================');
    lines.push('ชื่อ: ' + @json($credentials['name']));
    lines.push('รหัสลูกค้า: ' + @json($credentials['customerno']));
    lines.push('อีเมล: ' + @json($credentials['email']));
    lines.push('รหัสผ่าน: ' + @json($credentials['plain_password']));
    lines.push('เข้าสู่ระบบ: ' + @json(url('/login')));
    lines.push('');

    @foreach(['sea' => '🚢 โกดังขนส่งทางเรือ', 'air' => '✈️ โกดังขนส่งทางเครื่องบิน'] as $type => $label)
        @php $w = $warehouses[$type] ?? null; @endphp
        @if($w && (!empty($w['address_jp']) || !empty($w['address_en'])))
            lines.push('----- ' + @json($label) + ' -----');
            // English (เด่น/หลัก)
            @if(!empty($w['address_en']))
                lines.push('[English]');
                lines.push(@json($w['name_en'] ?? $w['name_jp'] ?? ''));
                lines.push(@json($w['postcode'] ?? ''));
                lines.push(@json($w['address_en']));
                @if(!empty($w['phone'])) lines.push('Tel: ' + @json($w['phone'])); @endif
                lines.push('');
            @endif
            // Japanese (รอง)
            @if(!empty($w['address_jp']))
                lines.push('[日本語]');
                lines.push(@json($w['name_jp'] ?? ''));
                lines.push('〒' + @json($w['postcode'] ?? ''));
                lines.push(@json($w['address_jp']));
                @if(!empty($w['phone'])) lines.push('電話: ' + @json($w['phone'])); @endif
            @endif
            lines.push('');
        @endif
    @endforeach

    lines.push('⚠️ กรุณาเขียนรหัส ' + @json($credentials['customerno']) + ' บนกล่องพัสดุทุกครั้ง');

    navigator.clipboard.writeText(lines.join('\n')).then(()=>alert('Copy ข้อมูลทั้งหมดแล้ว'));
}
@endif
</script>
@endsection
