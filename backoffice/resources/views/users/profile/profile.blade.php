@extends('home')

@section('title')
    Profile
@endsection

@section('extra-css')
    <style>
        /* ========================================
           1. HIDE LEGACY NAVBAR
           ======================================== */
        .navbar-modern,
        .navbar,
        .main-panel > .navbar {
            display: none !important;
        }
        .content { padding-top: 0 !important; margin-top: 0 !important; }
        .content > .row { margin-top: 0 !important; }
        .main-panel > .content { margin-top: 0 !important; padding: 0 !important; }
        /* Hide legacy alert from custom-message.blade.php */
        .main-panel > .alert,
        .main-panel > div > .alert,
        .main-panel > div[style*="margin-top"] {
            display: none !important;
        }
        .mobile-nav-toggle { display: none; }

        /* ========================================
           2. GLOBAL LAYOUT
           ======================================== */
        html, body {
            background-color: #f0f4f8 !important;
            height: 100%;
            overflow-x: hidden;
        }
        .wrapper {
            display: flex !important;
            width: 100% !important;
            overflow-x: hidden;
        }
        .main-panel {
            float: right !important;
            width: calc(100% - 260px) !important;
            min-height: 100vh !important;
            background: #f0f4f8 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* ========================================
           3. PROFILE PAGE
           ======================================== */
        .profile-page-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px 60px;
        }

        /* --- Banner --- */
        .profile-header-banner {
            background: linear-gradient(135deg, #0f4c75 0%, #1D8AC9 50%, #00b4d8 100%);
            height: 200px;
            border-radius: 0 0 24px 24px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: white;
            overflow: hidden;
        }
        .banner-decor {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .banner-decor .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .banner-decor .c1 { width: 300px; height: 300px; top: -100px; right: -60px; }
        .banner-decor .c2 { width: 200px; height: 200px; bottom: -80px; left: -40px; }
        .banner-decor .c3 { width: 120px; height: 120px; top: 20px; left: 20%; background: rgba(255,255,255,0.04); }
        .banner-wave {
            position: absolute;
            bottom: -1px;
            left: 0;
            width: 100%;
            line-height: 0;
        }
        .banner-wave svg {
            display: block;
            width: 100%;
            height: 40px;
        }
        .banner-content {
            position: relative;
            z-index: 2;
        }
        .banner-content h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin: 0 0 4px;
            letter-spacing: -0.5px;
        }
        .banner-content p {
            font-size: 0.95rem;
            opacity: 0.85;
            margin: 0;
            font-weight: 400;
        }

        /* --- Layout Grid --- */
        .profile-content-wrapper {
            display: flex !important;
            gap: 24px !important;
            margin-top: 24px !important;
            position: relative !important;
            z-index: 10 !important;
            align-items: flex-start !important;
        }
        .profile-sidebar {
            flex: 0 0 340px !important;
            max-width: 340px !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }
        .profile-main {
            flex: 1 !important;
            min-width: 0 !important;
            margin-top: 0 !important;
            padding-top: 0 !important;
        }

        /* --- Shared Card --- */
        .card-profile {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #e8ecf1;
            overflow: visible;
        }

        /* --- User Info Card --- */
        .user-avatar-section {
            padding: 30px 24px 28px;
            text-align: center;
            position: relative;
        }
        .avatar-ring {
            width: 100px;
            height: 100px;
            margin: 0 auto 14px;
            border-radius: 50%;
            padding: 4px;
            background: linear-gradient(135deg, #1D8AC9, #00b4d8);
            box-shadow: 0 8px 24px rgba(29, 138, 201, 0.3);
            position: relative;
            z-index: 5;
        }
        .avatar-circle {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #E63946 0%, #c62e3a 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.4rem;
            font-weight: 700;
            border: 3px solid #fff;
        }
        .user-name-display {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0 0 6px;
        }
        .user-role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
            color: #0369a1;
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }
        .customer-code {
            color: #94a3b8;
            font-size: 0.85rem;
            font-weight: 600;
            margin-top: 8px;
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        /* --- Info List --- */
        .info-list {
            padding: 4px 0;
        }
        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 16px 24px;
            border-top: 1px solid #f1f5f9;
            transition: background 0.2s;
        }
        .info-item:hover {
            background: #f8fafc;
        }
        .info-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .info-icon.blue   { background: #eff6ff; color: #3b82f6; }
        .info-icon.green  { background: #f0fdf4; color: #22c55e; }
        .info-icon.orange { background: #fff7ed; color: #f97316; }
        .info-details {
            flex: 1;
            min-width: 0;
        }
        .info-details .label {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 2px;
        }
        .info-details .value {
            color: #334155;
            font-weight: 600;
            font-size: 0.9rem;
            line-height: 1.5;
            word-break: break-word;
            text-decoration: none !important;
        }
        .info-list a,
        .info-details a,
        .info-details .value a,
        .info-item a {
            text-decoration: none !important;
            color: inherit !important;
            border-bottom: none !important;
            pointer-events: none;
        }

        /* --- Edit Card --- */
        .card-header-edit {
            padding: 20px 28px;
            border-bottom: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .card-header-edit .header-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, #1D8AC9, #0ea5e9);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .card-header-edit h3 {
            margin: 0;
            font-size: 1.1rem;
            color: #1e293b;
            font-weight: 700;
        }
        .card-header-edit .header-sub {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 400;
            margin: 0;
        }
        .card-body-edit {
            padding: 28px;
        }

        /* --- Form --- */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        .form-group-modern {
            margin-bottom: 0;
        }
        .form-group-modern label {
            display: block;
            margin-bottom: 6px;
            color: #64748b;
            font-weight: 600;
            font-size: 0.85rem;
        }
        .input-group-modern {
            position: relative;
        }
        .input-group-modern i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            transition: color 0.3s;
            font-size: 0.95rem;
            pointer-events: none;
        }
        .form-control-modern {
            width: 100%;
            padding: 12px 16px 12px 44px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            color: #1e293b;
            transition: all 0.25s;
            background: #fafbfc;
        }
        .form-control-modern:focus {
            border-color: #1D8AC9;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(29, 138, 201, 0.12);
        }
        .input-group-modern:focus-within i {
            color: #1D8AC9;
        }
        select.form-control-modern {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 40px;
            cursor: pointer;
        }
        .form-group-modern.full-width { grid-column: 1 / -1; }

        /* --- Form Section Divider --- */
        .form-section-title {
            grid-column: 1 / -1;
            font-size: 0.8rem;
            font-weight: 700;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .form-section-title i {
            color: #cbd5e1;
        }

        /* --- Address Note Banner --- */
        .address-note {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border: 1px solid #93c5fd;
            border-left: 4px solid #1D8AC9;
            border-radius: 12px;
            padding: 14px 18px;
            color: #1e3a8a;
        }
        .address-note-icon {
            font-size: 22px;
            line-height: 1;
            flex-shrink: 0;
            margin-top: 2px;
        }
        .address-note-title {
            font-weight: 700;
            font-size: 14px;
            color: #0c5e8e;
            margin-bottom: 4px;
        }
        .address-note-text {
            font-size: 13px;
            line-height: 1.55;
            color: #1e3a8a;
        }

        /* --- Address Quick Search --- */
        .address-quick-wrap { position: relative; display: flex; gap: 8px; align-items: stretch; }
        .address-quick-wrap .input-group-modern { flex: 1; }
        .address-quick-clear {
            width: 44px; flex-shrink: 0;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            color: #64748b;
            cursor: pointer;
            transition: all 0.2s;
            font-size: 14px;
        }
        .address-quick-clear:hover { background: #fee2e2; color: #dc2626; border-color: #fca5a5; }
        .address-quick-results {
            position: absolute; z-index: 1050;
            left: 0; right: 52px; top: calc(100% + 4px);
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 12px;
            box-shadow: 0 12px 32px rgba(0,0,0,.14);
            max-height: 340px; overflow-y: auto;
        }
        .address-quick-results .aq-item {
            padding: 10px 14px; cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; gap: 10px; font-size: 14px;
        }
        .address-quick-results .aq-item:last-child { border-bottom: 0; }
        .address-quick-results .aq-item:hover,
        .address-quick-results .aq-item.active { background: #eff6ff; }
        .address-quick-results .aq-zip {
            background: #1D8AC9; color: #fff; font-weight: 600;
            font-family: 'SF Mono', Menlo, monospace; padding: 3px 8px;
            border-radius: 6px; font-size: 12px; min-width: 54px; text-align: center;
        }
        .address-quick-results .aq-text { flex: 1; line-height: 1.35; color: #0f172a; }
        .address-quick-results .aq-text small { color: #64748b; }
        .address-quick-results .aq-empty,
        .address-quick-results .aq-loading {
            padding: 14px; text-align: center; color: #94a3b8; font-size: 13px;
        }
        .address-quick-results mark {
            background: #fef08a; color: inherit; padding: 0 2px; border-radius: 2px;
        }
        .address-quick-status {
            display: block; min-height: 16px; margin-top: 4px;
            color: #64748b; font-size: 12px;
        }

        /* --- Save Button --- */
        .form-actions-modern {
            margin-top: 28px;
            display: flex;
            justify-content: flex-end;
        }
        .btn-save-modern {
            background: linear-gradient(135deg, #1D8AC9 0%, #0c6da1 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(29, 138, 201, 0.3);
        }
        .btn-save-modern:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(29, 138, 201, 0.4);
        }
        .btn-save-modern:active {
            transform: translateY(0);
        }

        /* --- Alerts --- */
        .alert-modern {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .alert-success-modern {
            background: #f0fdf4;
            border-left: 4px solid #22c55e;
            color: #166534;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: opacity 0.5s ease;
        }
        .alert-success-modern.fade-out {
            opacity: 0;
        }

        /* ========================================
           4. SIDEBAR OVERRIDES
           ======================================== */
        .sidebar {
            display: flex;
            flex-direction: column;
        }
        .sidebar-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ========================================
           5. RESPONSIVE
           ======================================== */
        @media (max-width: 992px) {
            .profile-content-wrapper {
                flex-direction: column !important;
                align-items: center !important;
            }
            .profile-sidebar {
                max-width: 400px !important;
                width: 100% !important;
                flex: none !important;
            }
            .profile-main {
                max-width: 100% !important;
                width: 100% !important;
            }
            .form-grid {
                grid-template-columns: 1fr;
            }
            .form-group-modern.full-width { grid-column: auto; }
            .main-panel {
                width: 100% !important;
                float: none !important;
            }
            .profile-page-container {
                padding: 0 16px 40px !important;
            }
            .profile-header-banner {
                height: 160px;
                border-radius: 0 0 16px 16px;
            }
            .banner-content h1 { font-size: 1.4rem; }
            .mobile-nav-toggle {
                display: flex !important;
                position: fixed;
                top: 15px;
                left: 20px;
                z-index: 9000;
                background: white;
                color: #1D8AC9;
                border: none;
                width: 45px;
                height: 45px;
                border-radius: 12px;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.6);
                z-index: 8000;
                backdrop-filter: blur(4px);
            }
            .sidebar-overlay.show { display: block !important; }
            a[href^="tel"], a[href^="mailto"], .info-value, .info-value a,
            .info-details .value, .info-details .value a,
            .info-details .label,
            .info-list a, .info-item a, .info-details a {
                text-decoration: none !important;
                color: inherit !important;
                border-bottom: none !important;
                -webkit-text-decoration: none !important;
            }
        }
    </style>
@endsection

@section('index')
    <!-- Mobile Toggle -->
    <button class="mobile-nav-toggle" id="sidebarToggle"><i class="fa fa-bars"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="profile-page-container">

        <!-- ===== BANNER ===== -->
        <div class="profile-header-banner">
            <div class="banner-decor">
                <div class="circle c1"></div>
                <div class="circle c2"></div>
                <div class="circle c3"></div>
            </div>
            <div class="banner-content">
                <h1><i class="fa fa-user-circle" style="margin-right:10px;opacity:0.7"></i>My Profile</h1>
                <p>View and manage your account information</p>
            </div>
            <div class="banner-wave">
                <svg viewBox="0 0 1440 40" preserveAspectRatio="none">
                    <path d="M0,20 C360,40 720,0 1440,20 L1440,40 L0,40 Z" fill="#f0f4f8"/>
                </svg>
            </div>
        </div>

        <div class="profile-content-wrapper">

            <!-- ===== LEFT: USER CARD ===== -->
            <div class="profile-sidebar">
                <div class="card-profile">
                    <div class="user-avatar-section">
                        <div class="avatar-ring">
                            <div class="avatar-circle">
                                {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                            </div>
                        </div>
                        <h2 class="user-name-display">{{ Auth::user()->name }}</h2>
                        <div class="user-role-badge">
                            <i class="fa fa-shield"></i> {{ ucfirst(Auth::user()->getRoleNames()->first() ?? 'Member') }}
                        </div>
                        <div class="customer-code">{{ strtoupper(Auth::user()->customerno ?? 'N/A') }}</div>
                    </div>

                    <div class="info-list">
                        <div class="info-item">
                            <div class="info-icon blue"><i class="fa fa-envelope"></i></div>
                            <div class="info-details">
                                <div class="label">Email</div>
                                <div class="value">{{ Auth::user()->email }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon green"><i class="fa fa-phone"></i></div>
                            <div class="info-details">
                                <div class="label">Mobile</div>
                                <div class="value">{{ Auth::user()->mobile ?? '-' }}</div>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-icon orange"><i class="fa fa-map-marker"></i></div>
                            <div class="info-details">
                                <div class="label">Address</div>
                                <div class="value">
                                    {{ Auth::user()->addr }}
                                    {{ Auth::user()->subdistrinct }} {{ Auth::user()->distrinct }}
                                    {{ Auth::user()->province }} {{ Auth::user()->postcode }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 16px 24px 24px;">
                        <a href="{{ route('change-password') }}" style="display:flex;align-items:center;justify-content:center;gap:8px;width:100%;padding:12px 20px;background:linear-gradient(135deg,#1D8AC9,#0c6da1);color:#fff;border-radius:12px;font-weight:700;font-size:0.9rem;text-decoration:none;transition:all 0.3s;box-shadow:0 4px 14px rgba(29,138,201,0.3);">
                            <i class="fa fa-key"></i> เปลี่ยนรหัสผ่าน
                        </a>
                    </div>
                </div>
            </div>

            <!-- ===== RIGHT: WAREHOUSE + EDIT FORM ===== -->
            <div class="profile-main">

                @php
                    $myCustomerCode = strtoupper(Auth::user()->customerno ?? '');
                    $warehouses = \App\Models\SystemSetting::warehouses($myCustomerCode);
                @endphp
                @if(!empty($warehouses['sea']['address_jp']) || !empty($warehouses['air']['address_jp']))
                <div class="card-profile" style="margin-bottom:24px;">
                    <div class="card-header-edit">
                        <div class="header-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);"><i class="fa fa-building"></i></div>
                        <div>
                            <h3>ที่อยู่โกดังในญี่ปุ่น</h3>
                            <p class="header-sub">เลือกที่อยู่ตามประเภทขนส่ง — แจ้งให้ร้านค้าในญี่ปุ่นส่งของมา</p>
                        </div>
                    </div>
                    <div class="card-body-edit">
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
                                    <div style="background:{{ $bg }}; border:1px solid {{ $border }}; border-radius:12px; padding:16px 20px; line-height:1.6; height:100%;">
                                        <div style="font-weight:700; font-size:14px; color:{{ $titleColor }}; margin-bottom:8px;">{{ $w['icon'] }} {{ $w['label'] }}</div>

                                        {{-- ENGLISH ADDRESS (PRIMARY) --}}
                                        <div id="prof-wh-{{ $t }}-text" style="font-size:15px; color:#0f172a;">
                                            @if(!empty($w['address_en']))
                                                <strong style="color:{{ $titleColor }}; font-size:16px; letter-spacing:.2px;">{{ $w['name_en'] ?? $w['name_jp'] }}</strong><br>
                                                <span style="font-weight:600;">{{ $w['postcode'] }}</span><br>
                                                <span>{{ $w['address_en'] }}</span>
                                                @if(!empty($w['phone']))<br><i class="fa fa-phone" style="margin-right:4px; color:{{ $titleColor }};"></i><span style="font-weight:600; font-family:'SF Mono','Menlo',monospace;">{{ $w['phone'] }}</span>@endif
                                            @else
                                                {{-- ถ้าไม่มีภาษาอังกฤษ ให้แสดงญี่ปุ่นเด่นแทน --}}
                                                <strong style="color:{{ $titleColor }}; font-size:16px;">{{ $w['name_jp'] }}</strong><br>
                                                〒{{ $w['postcode'] }}<br>
                                                {{ $w['address_jp'] }}
                                                @if(!empty($w['phone']))<br><i class="fa fa-phone" style="margin-right:4px; color:{{ $titleColor }};"></i><span style="font-weight:600; font-family:'SF Mono','Menlo',monospace;">{{ $w['phone'] }}</span>@endif
                                            @endif
                                        </div>

                                        {{-- JAPANESE ADDRESS (SECONDARY) --}}
                                        @if(!empty($w['address_jp']) && !empty($w['address_en']))
                                            <div style="margin-top:10px; padding-top:10px; border-top:1px dashed {{ $border }}; font-size:12px; color:#6b7280; line-height:1.55;">
                                                <em style="color:#475569;">{{ $w['name_jp'] }}</em><br>
                                                〒{{ $w['postcode'] }}<br>
                                                {{ $w['address_jp'] }}
                                            </div>
                                        @endif

                                        <div style="margin-top:12px; text-align:right;">
                                            <button type="button" onclick="_profCopy('prof-wh-{{ $t }}-text', event)" style="background:#fff; border:1px solid {{ $border }}; color:{{ $titleColor }}; padding:6px 12px; border-radius:8px; cursor:pointer; font-size:12px; font-weight:600;">
                                                📋 คัดลอก
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            @endforeach
                        </div>
                        @if($myCustomerCode)
                        <div style="margin-top:8px; padding:12px 16px; background:#dbeafe; border-left:4px solid #1D8AC9; border-radius:10px; color:#1e3a8a; font-size:14px;">
                            <i class="fa fa-info-circle"></i> <strong>สำคัญ:</strong> เขียน <span style="display:inline-block; font-family:'SF Mono','Menlo',monospace; font-weight:700; background:#fff; color:#1D8AC9; padding:2px 10px; border-radius:6px; border:1px solid #93c5fd;">รหัสลูกค้า {{ $myCustomerCode }}</span> ลงบนกล่องพัสดุทุกครั้ง เพื่อให้โกดังจัดส่งให้คุณได้ถูกต้อง
                        </div>
                        @endif
                    </div>
                </div>
                <script>
                    function _profCopy(id, ev){var el=document.getElementById(id);if(!el)return;navigator.clipboard.writeText(el.innerText).then(function(){var b=ev.target;var old=b.innerText;b.innerText='✓ คัดลอกแล้ว';setTimeout(function(){b.innerText=old;},1500);});}
                </script>
                @endif

                <div class="card-profile">
                    <div class="card-header-edit">
                        <div class="header-icon"><i class="fa fa-pencil"></i></div>
                        <div>
                            <h3>Edit Profile</h3>
                            <p class="header-sub">Update your personal information</p>
                        </div>
                    </div>
                    <div class="card-body-edit">

                        @if(session('success'))
                            <div class="alert-success-modern">
                                <i class="fa fa-check-circle"></i> {{ session('success') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert-modern">
                                <ul style="margin: 0; padding-left: 20px;">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="{{route('profile.update',Auth::user()->id)}}" method="POST">
                            {{csrf_field()}}
                            {{method_field('PUT')}}

                            <div class="form-grid">

                                <!-- Section: Basic Info -->
                                <div class="form-section-title">
                                    <i class="fa fa-user"></i> Basic Information
                                </div>

                                <div class="form-group-modern">
                                    <label>Full Name</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-user"></i>
                                        <input type="text" name="name" class="form-control-modern" value="{{Auth::user()->name}}" placeholder="Enter your name">
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>Mobile Number</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-phone"></i>
                                        <input type="text" name="mobile" class="form-control-modern" value="{{Auth::user()->mobile}}" placeholder="08xxxxxxxx">
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>Email Address</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-envelope"></i>
                                        <input type="email" name="email" class="form-control-modern" value="{{Auth::user()->email}}" readonly style="cursor: not-allowed; opacity: 0.6;">
                                    </div>
                                </div>

                                <!-- Section: Address -->
                                <div class="form-section-title">
                                    <i class="fa fa-map-marker"></i> Address Information
                                </div>

                                {{-- หมายเหตุ: บอกลูกค้าว่าใช้ที่อยู่นี้สำหรับจัดส่งที่ไทย --}}
                                <div class="form-group-modern full-width">
                                    <div class="address-note">
                                        <div class="address-note-icon">📍</div>
                                        <div>
                                            <div class="address-note-title">หมายเหตุ — ที่อยู่ปัจจุบันสำหรับจัดส่งที่ไทย</div>
                                            <div class="address-note-text">
                                                หากลูกค้าเลือก My Shipping เป็นที่อยู่ปัจจุบัน
                                                ระบบจะจัดส่งเป็นที่อยู่ตามนี้ในการจัดส่งพัสดุให้ที่ไทย 🙏
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- ✨ Quick Address Search: พิมพ์ จังหวัด/อำเภอ/ตำบล/รหัสไปรษณีย์ → กรอกฟิลด์ที่เหลือให้อัตโนมัติ --}}
                                <div class="form-group-modern full-width">
                                    <label>
                                        <i class="fa fa-search" style="margin-right:4px; color:#1D8AC9;"></i>
                                        ค้นหาที่อยู่ด่วน
                                        <span style="font-weight:400; color:#94a3b8; font-size:0.8rem;">— พิมพ์ จังหวัด / อำเภอ / ตำบล / รหัสไปรษณีย์ อย่างใดอย่างหนึ่ง</span>
                                    </label>
                                    <div class="address-quick-wrap">
                                        <div class="input-group-modern">
                                            <i class="fa fa-search"></i>
                                            <input type="text" id="address_quick_search" class="form-control-modern" autocomplete="off"
                                                   placeholder="เช่น บางรัก, ห้วยขวาง, กรุงเทพ, 10110 ...">
                                        </div>
                                        <button type="button" id="address_quick_clear" class="address-quick-clear" title="ล้าง">
                                            <i class="fa fa-times"></i>
                                        </button>
                                        <div id="address_quick_results" class="address-quick-results" style="display:none;"></div>
                                    </div>
                                    <small id="address_quick_status" class="address-quick-status"></small>
                                </div>

                                <div class="form-group-modern full-width">
                                    <label>Address</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-home"></i>
                                        <input type="text" name="addr" class="form-control-modern" value="{{Auth::user()->addr}}" placeholder="Street address">
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>Province</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-map"></i>
                                        <select class="form-control-modern" name="province" id="province" onchange="showAmphoes()">
                                            <option value="">Select Province</option>
                                            @foreach($provinces as $item)
                                                <option value="{{ $item->province }}" {{ Auth::user()->province == $item->province ? 'selected' : '' }}>{{ $item->province }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>District</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-map-pin"></i>
                                        <select class="form-control-modern" name="distrinct" id="distrinct" onchange="showTambons()">
                                            <option value="{{ Auth::user()->distrinct }}" selected>{{ Auth::user()->distrinct ?? 'Select District' }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>Sub-district</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-location-arrow"></i>
                                        <select class="form-control-modern" name="subdistrinct" id="subdistrinct" onchange="showZipcode()">
                                            <option value="{{ Auth::user()->subdistrinct }}" selected>{{ Auth::user()->subdistrinct ?? 'Select Sub-district' }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group-modern">
                                    <label>Zipcode</label>
                                    <div class="input-group-modern">
                                        <i class="fa fa-hashtag"></i>
                                        <input class="form-control-modern" name="postcode" id="postcode" value="{{ Auth::user()->postcode }}" placeholder="Zip Code">
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions-modern">
                                <button type="submit" class="btn-save-modern">
                                    <i class="fa fa-check"></i> Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        // AUTO-HIDE SUCCESS ALERT
        (function() {
            var successAlert = document.querySelector('.alert-success-modern');
            if (successAlert) {
                setTimeout(function() {
                    successAlert.classList.add('fade-out');
                    setTimeout(function() { successAlert.remove(); }, 500);
                }, 3000);
            }
        })();

        // MOBILE SIDEBAR LOGIC (Injected here because we hid the Navbar which contained the logic)
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');
            var sidebar = document.querySelector('.sidebar-modern'); // From home/side-bar

            if (toggle && overlay && sidebar) {
                toggle.addEventListener('click', () => { 
                    sidebar.classList.add('show'); 
                    overlay.classList.add('show'); 
                });

                overlay.addEventListener('click', () => { 
                    sidebar.classList.remove('show'); 
                    overlay.classList.remove('show'); 
                });
            }
        });
        
        // Location API logic (kept same as before)
        function showAmphoes(province = "#province", distrinct = "#distrinct") {
            let input_province = document.querySelector(province);
            let url = "{{ url('/api/amphoes') }}?province=" + input_province.value;
            fetch(url).then(r => r.json()).then(result => {
                let input_amphoe = document.querySelector(distrinct);
                input_amphoe.innerHTML = '<option value="">Select District</option>';
                result.forEach(item => {
                    let option = document.createElement("option");
                    option.text = item.amphoe; option.value = item.amphoe;
                    input_amphoe.appendChild(option);
                });
                showTambons();
            });
        }

        function showTambons(province = "#province", distrinct = "#distrinct", subdistrinct = "#subdistrinct") {
            let input_province = document.querySelector(province);
            let input_amphoe = document.querySelector(distrinct);
            let url = "{{ url('/api/tambons') }}?province=" + input_province.value + "&amphoe=" + input_amphoe.value;
            fetch(url).then(r => r.json()).then(result => {
                let input_tambon = document.querySelector(subdistrinct);
                input_tambon.innerHTML = '<option value="">Select Sub-district</option>';
                result.forEach(item => {
                    let option = document.createElement("option");
                    option.text = item.tambon; option.value = item.tambon;
                    input_tambon.appendChild(option);
                });
            });
        }

        // ===== Quick Address Search =====
        (function() {
            const searchUrl = "{{ url('/api/tambons/search') }}";
            const amphoesUrl = "{{ url('/api/amphoes') }}";
            const tambonsUrl = "{{ url('/api/tambons') }}";
            const inp = document.getElementById('address_quick_search');
            const box = document.getElementById('address_quick_results');
            const status = document.getElementById('address_quick_status');
            const clearBtn = document.getElementById('address_quick_clear');
            if (!inp || !box) return;

            let debounceTimer = null;
            let activeIdx = -1;
            let lastResults = [];
            let currentReq = 0;

            function escapeHtml(s) {
                return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
            }
            function highlight(text, q) {
                if (!q) return escapeHtml(text);
                const esc = escapeHtml(text);
                try {
                    const re = new RegExp('(' + q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
                    return esc.replace(re, '<mark>$1</mark>');
                } catch { return esc; }
            }
            function hide() { box.style.display = 'none'; box.innerHTML = ''; activeIdx = -1; }
            function show() { box.style.display = 'block'; }
            function setStatus(msg) { status.textContent = msg || ''; }

            function render(results, q) {
                lastResults = results || [];
                if (!results.length) {
                    box.innerHTML = '<div class="aq-empty">😕 ไม่พบที่อยู่ที่ตรงกับ "' + escapeHtml(q) + '"</div>';
                    show(); return;
                }
                box.innerHTML = results.map((r, i) => `
                    <div class="aq-item${i===0?' active':''}" data-idx="${i}">
                        <span class="aq-zip">${escapeHtml(r.zipcode || '-')}</span>
                        <div class="aq-text">
                            <strong>${highlight('ตำบล' + r.tambon, q)}</strong>
                            <small> · อำเภอ${highlight(r.amphoe, q)} · จังหวัด${highlight(r.province, q)}</small>
                        </div>
                    </div>`).join('');
                activeIdx = 0;
                show();
            }

            function ensureOption(sel, value) {
                if (!sel || !value) return;
                if (!Array.from(sel.options).some(o => o.value === value)) {
                    const opt = document.createElement('option');
                    opt.value = value; opt.text = value;
                    sel.appendChild(opt);
                }
            }
            function loadAmphoesPromise(provSel, distSel) {
                return fetch(amphoesUrl + '?province=' + encodeURIComponent(provSel.value))
                    .then(r => r.json())
                    .then(items => {
                        distSel.innerHTML = '<option value="">Select District</option>';
                        items.forEach(it => {
                            const o = document.createElement('option');
                            o.value = it.amphoe; o.text = it.amphoe;
                            distSel.appendChild(o);
                        });
                    });
            }
            function loadTambonsPromise(provSel, distSel, subSel) {
                const url = tambonsUrl + '?province=' + encodeURIComponent(provSel.value)
                          + '&amphoe=' + encodeURIComponent(distSel.value);
                return fetch(url).then(r => r.json()).then(items => {
                    subSel.innerHTML = '<option value="">Select Sub-district</option>';
                    items.forEach(it => {
                        const o = document.createElement('option');
                        o.value = it.tambon; o.text = it.tambon;
                        subSel.appendChild(o);
                    });
                });
            }
            function pick(r) {
                if (!r) return;
                const provSel = document.getElementById('province');
                const distSel = document.getElementById('distrinct');
                const subSel  = document.getElementById('subdistrinct');
                const post    = document.getElementById('postcode');
                ensureOption(provSel, r.province);
                provSel.value = r.province;
                loadAmphoesPromise(provSel, distSel).then(() => {
                    ensureOption(distSel, r.amphoe);
                    distSel.value = r.amphoe;
                    return loadTambonsPromise(provSel, distSel, subSel);
                }).then(() => {
                    ensureOption(subSel, r.tambon);
                    subSel.value = r.tambon;
                    post.value = r.zipcode || '';
                    inp.value = `${r.tambon} · ${r.amphoe} · ${r.province} (${r.zipcode || '-'})`;
                    hide();
                    setStatus('✓ กรอกที่อยู่ให้แล้ว — ยังแก้ไขเพิ่มเติมจากเมนูด้านล่างได้');
                    inp.blur();
                });
            }
            function search(q) {
                if (!q || q.trim().length < 1) { hide(); setStatus(''); return; }
                const reqId = ++currentReq;
                box.innerHTML = '<div class="aq-loading"><i class="fa fa-spinner fa-spin"></i> กำลังค้นหา...</div>';
                show();
                fetch(searchUrl + '?q=' + encodeURIComponent(q) + '&limit=30')
                    .then(r => r.json())
                    .then(data => {
                        if (reqId !== currentReq) return;
                        render(data, q.trim());
                        setStatus(data.length ? `พบ ${data.length} รายการ — ใช้ลูกศร ↑↓ + Enter` : '');
                    })
                    .catch(err => { console.error(err); setStatus('เกิดข้อผิดพลาด ลองใหม่อีกครั้ง'); hide(); });
            }
            inp.addEventListener('input', e => {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => search(e.target.value), 220);
            });
            inp.addEventListener('focus', () => {
                if (lastResults.length && inp.value.trim()) show();
            });
            inp.addEventListener('keydown', e => {
                const items = box.querySelectorAll('.aq-item');
                if (!items.length) return;
                if (e.key === 'ArrowDown')      { e.preventDefault(); activeIdx = Math.min(activeIdx+1, items.length-1); }
                else if (e.key === 'ArrowUp')   { e.preventDefault(); activeIdx = Math.max(activeIdx-1, 0); }
                else if (e.key === 'Enter')     { e.preventDefault(); if (activeIdx >= 0) pick(lastResults[activeIdx]); return; }
                else if (e.key === 'Escape')    { hide(); return; }
                else return;
                items.forEach((el, i) => el.classList.toggle('active', i === activeIdx));
                items[activeIdx]?.scrollIntoView({ block: 'nearest' });
            });
            box.addEventListener('mousedown', e => {
                const item = e.target.closest('.aq-item');
                if (!item) return;
                e.preventDefault();
                pick(lastResults[parseInt(item.dataset.idx, 10)]);
            });
            document.addEventListener('click', e => {
                if (!e.target.closest('.address-quick-wrap')) hide();
            });
            clearBtn?.addEventListener('click', () => {
                inp.value = ''; setStatus(''); hide(); lastResults = []; inp.focus();
            });
        })();

        function showZipcode(province = "#province", distrinct = "#distrinct", subdistrinct = "#subdistrinct", postcode = "#postcode") {
            let input_province = document.querySelector(province);
            let input_amphoe = document.querySelector(distrinct);
            let input_tambon = document.querySelector(subdistrinct);
            let url = "{{ url('/api/zipcodes') }}?province=" + input_province.value + "&amphoe=" + input_amphoe.value + "&tambon=" + input_tambon.value;
            fetch(url).then(r => r.json()).then(result => {
                let input_zipcode = document.querySelector(postcode);
                input_zipcode.value = "";
                if(result.length > 0) input_zipcode.value = result[0].zipcode;
            });
        }
    </script>
@endsection