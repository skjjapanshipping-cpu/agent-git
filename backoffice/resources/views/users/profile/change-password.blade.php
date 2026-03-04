@extends('home')

@section('title')
    เปลี่ยนรหัสผ่าน
@endsection

@section('extra-css')
    <style>
        /* ========================================
           HIDE LEGACY NAVBAR
           ======================================== */
        .navbar-modern,
        .navbar,
        .main-panel > .navbar {
            display: none !important;
        }
        .content { padding-top: 0 !important; margin-top: 0 !important; }
        .content > .row { margin-top: 0 !important; }
        .main-panel > .content { margin-top: 0 !important; padding: 0 !important; }
        .main-panel > .alert,
        .main-panel > div > .alert,
        .main-panel > div[style*="margin-top"] {
            display: none !important;
        }
        .mobile-nav-toggle { display: none; }

        /* ========================================
           GLOBAL LAYOUT
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
           CHANGE PASSWORD PAGE
           ======================================== */
        .cp-container {
            max-width: 680px;
            margin: 0 auto;
            padding: 0 24px 60px;
        }

        /* --- Header Banner --- */
        .cp-header {
            background: linear-gradient(135deg, #0f4c75 0%, #1D8AC9 50%, #00b4d8 100%);
            height: 140px;
            border-radius: 0 0 24px 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        .cp-header-decor {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .cp-header-decor .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .cp-header-decor .c1 { width: 250px; height: 250px; top: -80px; right: -50px; }
        .cp-header-decor .c2 { width: 160px; height: 160px; bottom: -60px; left: -30px; }
        .cp-header-content {
            position: relative;
            z-index: 2;
            text-align: center;
        }
        .cp-header-content h1 {
            font-size: 1.6rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .cp-header-content h1 i {
            font-size: 1.4rem;
            opacity: 0.85;
        }

        /* --- Card --- */
        .cp-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.06);
            border: 1px solid #e8ecf1;
            margin-top: 28px;
            overflow: hidden;
        }
        .cp-card-body {
            padding: 32px;
        }

        /* --- Success Alert --- */
        .cp-alert-success {
            background: linear-gradient(135deg, #e0f7e9 0%, #c6f6d5 100%);
            border: 1px solid #86efac;
            color: #166534;
            padding: 14px 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            font-weight: 600;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideDown 0.4s ease;
        }
        .cp-alert-success .close-btn {
            margin-left: auto;
            background: none;
            border: none;
            color: #166534;
            font-size: 1.1rem;
            cursor: pointer;
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        .cp-alert-success .close-btn:hover {
            opacity: 1;
        }

        /* --- Error Alert --- */
        .cp-alert-error {
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 14px 18px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-weight: 500;
            font-size: 0.9rem;
        }
        .cp-alert-error ul {
            margin: 0;
            padding-left: 18px;
        }

        /* --- Form --- */
        .cp-form-group {
            margin-bottom: 22px;
        }
        .cp-form-group label {
            display: block;
            margin-bottom: 8px;
            color: #334155;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .cp-form-group label i {
            margin-right: 6px;
            color: #1D8AC9;
        }
        .cp-form-group label .required {
            color: #ef4444;
            margin-left: 2px;
        }
        .cp-input-wrap {
            position: relative;
        }
        .cp-input {
            width: 100%;
            padding: 13px 46px 13px 16px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            color: #1e293b;
            background: #fafbfc;
            transition: all 0.25s;
        }
        .cp-input:focus {
            border-color: #1D8AC9;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(29, 138, 201, 0.12);
        }
        .cp-toggle-pw {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #0c6da1;
            cursor: pointer;
            font-size: 1rem;
            padding: 4px;
            transition: color 0.2s;
        }
        .cp-toggle-pw:hover {
            color: #1D8AC9;
        }
        .cp-hint {
            margin-top: 6px;
            font-size: 0.8rem;
            color: #94a3b8;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .cp-hint i {
            font-size: 0.75rem;
        }

        /* --- Security Tips --- */
        .cp-tips {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #93c5fd;
            border-radius: 14px;
            padding: 20px 24px;
            margin-top: 8px;
            margin-bottom: 28px;
        }
        .cp-tips-title {
            font-size: 0.9rem;
            font-weight: 700;
            color: #1D8AC9;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .cp-tips ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .cp-tips ul li {
            padding: 5px 0;
            font-size: 0.85rem;
            color: #1e3a5f;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .cp-tips ul li i {
            color: #22c55e;
            font-size: 0.8rem;
            flex-shrink: 0;
        }

        /* --- Buttons --- */
        .cp-buttons {
            display: flex;
            gap: 16px;
        }
        .cp-btn-submit {
            flex: 1;
            background: linear-gradient(135deg, #1D8AC9 0%, #0c6da1 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(29, 138, 201, 0.3);
            text-decoration: none;
        }
        .cp-btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(29, 138, 201, 0.4);
        }
        .cp-btn-back {
            flex: 1;
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 14px 24px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
            text-decoration: none !important;
        }
        .cp-btn-back:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(245, 158, 11, 0.4);
            color: white;
            text-decoration: none;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ========================================
           SIDEBAR OVERRIDES
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
           RESPONSIVE
           ======================================== */
        @media (max-width: 992px) {
            .main-panel {
                width: 100% !important;
                float: none !important;
            }
            .cp-container {
                padding: 0 16px 40px !important;
            }
            .cp-header {
                height: 120px;
                border-radius: 0 0 16px 16px;
            }
            .cp-header-content h1 { font-size: 1.3rem; }
            .cp-card-body { padding: 24px 20px; }
            .cp-buttons {
                flex-direction: column;
            }
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
        }
    </style>
@endsection

@section('index')
    <!-- Mobile Toggle -->
    <button class="mobile-nav-toggle" id="sidebarToggle"><i class="fa fa-bars"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="cp-container">

        <!-- ===== HEADER ===== -->
        <div class="cp-header">
            <div class="cp-header-decor">
                <div class="circle c1"></div>
                <div class="circle c2"></div>
            </div>
            <div class="cp-header-content">
                <h1><i class="fa fa-key"></i> เปลี่ยนรหัสผ่าน</h1>
            </div>
        </div>

        <!-- ===== CARD ===== -->
        <div class="cp-card">
            <div class="cp-card-body">

                @if(session('success'))
                    <div class="cp-alert-success" id="cpSuccessAlert">
                        <i class="fa fa-check-circle" style="font-size:1.1rem;"></i>
                        {{ session('success') }}
                        <button class="close-btn" onclick="this.parentElement.remove()">&times;</button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="cp-alert-error">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('change-password.update') }}" method="POST">
                    @csrf

                    <div class="cp-form-group">
                        <label><i class="fa fa-lock"></i> รหัสผ่านเดิม <span class="required">*</span></label>
                        <div class="cp-input-wrap">
                            <input type="password" name="current_password" class="cp-input" id="currentPw" placeholder="กรอกรหัสผ่านปัจจุบัน" required>
                            <button type="button" class="cp-toggle-pw" onclick="togglePw('currentPw', this)"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>

                    <div class="cp-form-group">
                        <label><i class="fa fa-pencil"></i> รหัสผ่านใหม่ <span class="required">*</span></label>
                        <div class="cp-input-wrap">
                            <input type="password" name="password" class="cp-input" id="newPw" placeholder="กรอกรหัสผ่านใหม่" required>
                            <button type="button" class="cp-toggle-pw" onclick="togglePw('newPw', this)"><i class="fa fa-eye"></i></button>
                        </div>
                        <div class="cp-hint"><i class="fa fa-info-circle"></i> รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร</div>
                    </div>

                    <div class="cp-form-group">
                        <label><i class="fa fa-check-circle"></i> ยืนยันรหัสผ่านใหม่ <span class="required">*</span></label>
                        <div class="cp-input-wrap">
                            <input type="password" name="password_confirmation" class="cp-input" id="confirmPw" placeholder="กรุณากรอกรหัสผ่านใหม่อีกครั้งเพื่อยืนยัน" required>
                            <button type="button" class="cp-toggle-pw" onclick="togglePw('confirmPw', this)"><i class="fa fa-eye"></i></button>
                        </div>
                        <div class="cp-hint"><i class="fa fa-info-circle"></i> กรุณากรอกรหัสผ่านใหม่อีกครั้งเพื่อยืนยัน</div>
                    </div>

                    <!-- Security Tips -->
                    <div class="cp-tips">
                        <div class="cp-tips-title"><i class="fa fa-shield"></i> คำแนะนำความปลอดภัย</div>
                        <ul>
                            <li><i class="fa fa-check"></i> รหัสผ่านต้องมีอย่างน้อย 8 ตัวอักษร</li>
                            <li><i class="fa fa-check"></i> ผสมตัวอักษรพิมพ์ใหญ่, พิมพ์เล็ก, ตัวเลข และสัญลักษณ์</li>
                            <li><i class="fa fa-check"></i> ไม่ใช้รหัสผ่านที่เกี่ยวข้องกับข้อมูลส่วนตัว</li>
                            <li><i class="fa fa-check"></i> เปลี่ยนรหัสผ่านเป็นประจำ</li>
                        </ul>
                    </div>

                    <div class="cp-buttons">
                        <button type="submit" class="cp-btn-submit">
                            <i class="fa fa-check-circle"></i> ยืนยันเปลี่ยนรหัสผ่าน
                        </button>
                        <a href="{{ route('profile.index') }}" class="cp-btn-back">
                            <i class="fa fa-times-circle"></i> กลับหน้าเดิม
                        </a>
                    </div>

                </form>
            </div>
        </div>

    </div>
@endsection

@section('extra-script')
    <script>
        function togglePw(inputId, btn) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fa fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fa fa-eye';
            }
        }

        // Auto-hide success alert
        (function() {
            var alert = document.getElementById('cpSuccessAlert');
            if (alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() { alert.remove(); }, 500);
                }, 5000);
            }
        })();

        // Mobile sidebar
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');
            var sidebar = document.querySelector('.sidebar-modern');
            if (toggle && overlay && sidebar) {
                toggle.addEventListener('click', function() { sidebar.classList.add('show'); overlay.classList.add('show'); });
                overlay.addEventListener('click', function() { sidebar.classList.remove('show'); overlay.classList.remove('show'); });
            }
        });
    </script>
@endsection
