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

            <!-- ===== RIGHT: EDIT FORM ===== -->
            <div class="profile-main">
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