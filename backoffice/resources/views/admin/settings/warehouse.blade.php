@extends('layouts.app')

@section('template_title')
    ตั้งค่าที่อยู่โกดังในญี่ปุ่น
@endsection

@section('content')
<section class="content container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-11">

            @if ($message = Session::get('success'))
                <div class="alert alert-success"><i class="fa fa-check-circle"></i> {{ $message }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0; padding-left:18px;">
                        @foreach($errors->all() as $err)<li>{{ $err }}</li>@endforeach
                    </ul>
                </div>
            @endif

            <div class="card" style="border:0; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:24px;">
                <div class="card-header" style="background:linear-gradient(135deg, #1D8AC9 0%, #0c5e8e 100%); color:#fff; padding:18px 22px; border-radius:14px 14px 0 0;">
                    <h4 style="margin:0; font-weight:600;"><i class="fa fa-building"></i> ตั้งค่าที่อยู่โกดังในญี่ปุ่น</h4>
                    <p style="margin:6px 0 0; opacity:.85; font-size:14px;">ข้อมูลนี้จะส่งให้ลูกค้าทุกคนตอนเปิดบัญชี + แสดงในหน้า Dashboard ของลูกค้า — แยกเป็น 2 ส่วน (ทางเรือ + ทางเครื่องบิน)</p>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.settings.warehouse.update') }}" autocomplete="off">
                @csrf

                <div class="row">
                    <!-- ====== โกดังขนส่งทางเรือ 🚢 ====== -->
                    <div class="col-lg-6 mb-4">
                        <div class="card" style="border:0; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); height:100%;">
                            <div class="card-header" style="background:linear-gradient(135deg, #0c5e8e 0%, #38a3d6 100%); color:#fff; padding:16px 20px; border-radius:14px 14px 0 0;">
                                <h5 style="margin:0; font-weight:700;">🚢 โกดังขนส่งทางเรือ</h5>
                                <p style="margin:4px 0 0; opacity:.85; font-size:13px;">ที่อยู่สำหรับให้ร้านค้าญี่ปุ่นส่งของทางเรือ</p>
                            </div>
                            <div class="card-body">
                                @foreach($seaSettings as $s)
                                    <div class="form-group">
                                        <label style="font-weight:600;">
                                            {{ $s->label }}
                                            <small class="text-muted" style="font-weight:400;">({{ $s->key }})</small>
                                        </label>
                                        @if(in_array($s->key, ['warehouse_sea_address_jp','warehouse_sea_address_en']))
                                            <textarea name="settings[{{ $s->key }}]" class="form-control" rows="3">{{ old('settings.'.$s->key, $s->value) }}</textarea>
                                        @else
                                            <input type="text" name="settings[{{ $s->key }}]" class="form-control"
                                                value="{{ old('settings.'.$s->key, $s->value) }}"
                                                style="@if(in_array($s->key, ['warehouse_sea_postcode','warehouse_sea_phone'])) font-family:'SF Mono','Menlo',monospace; @endif">
                                        @endif
                                        @if($s->description)
                                            <small class="text-muted">{{ $s->description }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- ====== โกดังขนส่งทางเครื่องบิน ✈️ ====== -->
                    <div class="col-lg-6 mb-4">
                        <div class="card" style="border:0; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); height:100%;">
                            <div class="card-header" style="background:linear-gradient(135deg, #d97706 0%, #f59e0b 100%); color:#fff; padding:16px 20px; border-radius:14px 14px 0 0;">
                                <h5 style="margin:0; font-weight:700;">✈️ โกดังขนส่งทางเครื่องบิน</h5>
                                <p style="margin:4px 0 0; opacity:.85; font-size:13px;">ที่อยู่สำหรับให้ร้านค้าญี่ปุ่นส่งของทางเครื่องบิน</p>
                            </div>
                            <div class="card-body">
                                @foreach($airSettings as $s)
                                    <div class="form-group">
                                        <label style="font-weight:600;">
                                            {{ $s->label }}
                                            <small class="text-muted" style="font-weight:400;">({{ $s->key }})</small>
                                        </label>
                                        @if(in_array($s->key, ['warehouse_air_address_jp','warehouse_air_address_en']))
                                            <textarea name="settings[{{ $s->key }}]" class="form-control" rows="3">{{ old('settings.'.$s->key, $s->value) }}</textarea>
                                        @else
                                            <input type="text" name="settings[{{ $s->key }}]" class="form-control"
                                                value="{{ old('settings.'.$s->key, $s->value) }}"
                                                style="@if(in_array($s->key, ['warehouse_air_postcode','warehouse_air_phone'])) font-family:'SF Mono','Menlo',monospace; @endif">
                                        @endif
                                        @if($s->description)
                                            <small class="text-muted">{{ $s->description }}</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ====== ใช้ร่วมกัน — หมายเหตุ + ติดต่อแอดมิน ====== -->
                <div class="card" style="border:0; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:24px;">
                    <div class="card-header" style="background:#f8fafc; padding:14px 22px; border-radius:14px 14px 0 0; border-bottom:1px solid #e2e8f0;">
                        <h5 style="margin:0; font-weight:700; color:#0c5e8e;">📝 ข้อมูลทั่วไป (ใช้ร่วมกันทั้ง 2 โกดัง)</h5>
                    </div>
                    <div class="card-body">
                        @foreach($sharedSettings as $s)
                            <div class="form-group">
                                <label style="font-weight:600;">
                                    {{ $s->label }}
                                    <small class="text-muted" style="font-weight:400;">({{ $s->key }})</small>
                                </label>
                                @if($s->key === 'warehouse_contact_note')
                                    <textarea name="settings[{{ $s->key }}]" class="form-control" rows="3">{{ old('settings.'.$s->key, $s->value) }}</textarea>
                                @else
                                    <input type="text" name="settings[{{ $s->key }}]" class="form-control"
                                        value="{{ old('settings.'.$s->key, $s->value) }}"
                                        style="@if($s->key === 'support_phone') font-family:'SF Mono','Menlo',monospace; @endif">
                                @endif
                                @if($s->description)
                                    <small class="text-muted">{{ $s->description }}</small>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <a href="{{ route('home') }}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> กลับ</a>
                    <button type="submit" class="btn btn-primary btn-lg" style="background:#1D8AC9; border-color:#1D8AC9; padding:10px 32px;">
                        <i class="fa fa-save"></i> บันทึก
                    </button>
                </div>
            </form>

            <!-- ====== Live Preview ====== -->
            <div class="card" style="border:0; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); margin-bottom:24px;">
                <div class="card-header" style="background:#f8fafc; padding:14px 22px; border-radius:14px 14px 0 0; border-bottom:1px solid #e2e8f0;">
                    <h5 style="margin:0; font-weight:700;"><i class="fa fa-eye"></i> ตัวอย่างที่ลูกค้าจะเห็น</h5>
                </div>
                <div class="card-body">
                    @php
                        $whs = \App\Models\SystemSetting::warehouses();
                        $note = \App\Models\SystemSetting::contactNote();
                    @endphp
                    <div class="row">
                        @foreach(['sea' => '#92400e', 'air' => '#1e40af'] as $type => $color)
                            @php
                                $w = $whs[$type];
                                $border = $type === 'sea' ? '#fde68a' : '#93c5fd';
                                $bg = $type === 'sea' ? '#fffbeb' : '#dbeafe';
                            @endphp
                            <div class="col-md-6 mb-3">
                                <div style="background:{{ $bg }}; border:1px solid {{ $border }}; border-radius:12px; padding:18px 22px; height:100%;">
                                    <div style="font-weight:700; font-size:14px; color:{{ $color }}; margin-bottom:10px;">{{ $w['icon'] }} {{ $w['label'] }}</div>

                                    {{-- ENGLISH ADDRESS (PRIMARY — เด่น) --}}
                                    @if(!empty($w['address_en']))
                                        <div style="font-size:15px; line-height:1.65; color:#0f172a;">
                                            <strong style="color:{{ $color }}; font-size:16px; letter-spacing:.2px;">{{ $w['name_en'] ?? $w['name_jp'] }}</strong><br>
                                            <span style="font-weight:600;">{{ $w['postcode'] }}</span><br>
                                            <span>{{ $w['address_en'] }}</span>
                                            @if(!empty($w['phone']))<br><i class="fa fa-phone" style="margin-right:4px; color:{{ $color }};"></i><span style="font-weight:600; font-family:'SF Mono','Menlo',monospace;">{{ $w['phone'] }}</span>@endif
                                        </div>
                                        {{-- JAPANESE ADDRESS (SECONDARY — รอง) --}}
                                        @if(!empty($w['address_jp']))
                                            <div style="margin-top:10px; padding-top:10px; border-top:1px dashed {{ $border }}; font-size:12px; color:#6b7280; line-height:1.55;">
                                                <em style="color:#475569;">{{ $w['name_jp'] }}</em><br>
                                                〒{{ $w['postcode'] }}<br>
                                                {{ $w['address_jp'] }}
                                            </div>
                                        @endif
                                    @else
                                        {{-- fallback: ถ้ายังไม่ตั้ง EN ก็แสดง JP เด่นแทน --}}
                                        <div style="font-size:15px; line-height:1.65; color:#0f172a;">
                                            <strong style="color:{{ $color }}; font-size:16px;">{{ $w['name_jp'] }}</strong><br>
                                            〒{{ $w['postcode'] }}<br>
                                            {{ $w['address_jp'] }}
                                            @if(!empty($w['phone']))<br><i class="fa fa-phone" style="margin-right:4px; color:{{ $color }};"></i><span style="font-weight:600; font-family:'SF Mono','Menlo',monospace;">{{ $w['phone'] }}</span>@endif
                                        </div>
                                        <div style="margin-top:10px; padding:8px 10px; background:#fff7ed; border:1px dashed #fdba74; border-radius:6px; font-size:12px; color:#9a3412;">
                                            <i class="fa fa-info-circle"></i> ยังไม่ตั้งค่าที่อยู่ภาษาอังกฤษ — ลูกค้าจะเห็นเฉพาะภาษาญี่ปุ่น
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @if(!empty($note))
                    <div style="background:#dbeafe; border-left:4px solid #1D8AC9; padding:12px 16px; border-radius:8px; margin-top:8px; color:#1e3a8a;">
                        <strong>📝 หมายเหตุ:</strong> {{ $note }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>
@endsection
