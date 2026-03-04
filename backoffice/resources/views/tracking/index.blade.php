@extends('layouts.app')
@section('title')
    SKJ JAPAN TRACKING
@endsection
@section('extra-css')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Thai:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* =============================================
           TRACKING PAGE - MODERN REDESIGN
           ============================================= */
        * { box-sizing: border-box; }

        body {
            font-family: 'Noto Sans Thai', -apple-system, BlinkMacSystemFont, sans-serif;
            background: #0a1628;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            position: relative;
        }

        body::after {
            content: '';
            position: fixed;
            inset: 0;
            background-image: url('{{ asset("img/bg-login.png") }}');
            background-size: cover;
            background-position: center;
            z-index: -1;
        }

        /* Blue Overlay (same tone as Login page) + Animated Gradient */
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                linear-gradient(135deg, rgba(26, 26, 46, 0.88) 0%, rgba(15, 76, 117, 0.85) 100%),
                radial-gradient(ellipse at 20% 50%, rgba(29, 138, 201, 0.15) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(56, 189, 248, 0.1) 0%, transparent 50%);
            animation: bgPulse 8s ease-in-out infinite alternate;
            z-index: 0;
        }

        @keyframes bgPulse {
            0% { opacity: 0.7; transform: scale(1); }
            100% { opacity: 1; transform: scale(1.05); }
        }

        /* Floating Particles */
        .particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            overflow: hidden;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(56, 189, 248, 0.3);
            border-radius: 50%;
            animation: particleFloat linear infinite;
        }

        .particle:nth-child(1) { left: 10%; animation-duration: 12s; animation-delay: 0s; width: 3px; height: 3px; }
        .particle:nth-child(2) { left: 25%; animation-duration: 15s; animation-delay: 2s; width: 5px; height: 5px; }
        .particle:nth-child(3) { left: 40%; animation-duration: 10s; animation-delay: 4s; }
        .particle:nth-child(4) { left: 55%; animation-duration: 14s; animation-delay: 1s; width: 6px; height: 6px; }
        .particle:nth-child(5) { left: 70%; animation-duration: 11s; animation-delay: 3s; width: 3px; height: 3px; }
        .particle:nth-child(6) { left: 85%; animation-duration: 13s; animation-delay: 5s; }
        .particle:nth-child(7) { left: 50%; animation-duration: 16s; animation-delay: 0s; width: 5px; height: 5px; }
        .particle:nth-child(8) { left: 15%; animation-duration: 9s; animation-delay: 6s; width: 3px; height: 3px; }

        @keyframes particleFloat {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.6; }
            90% { opacity: 0.6; }
            100% { transform: translateY(-10vh) rotate(360deg); opacity: 0; }
        }

        /* Container */
        .tracking-container {
            width: 100%;
            max-width: 520px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* Logo */
        .tracking-logo {
            text-align: center;
            margin-bottom: 32px;
        }

        .tracking-logo img {
            max-width: 220px;
            height: auto;
            filter: drop-shadow(0 8px 24px rgba(29, 138, 201, 0.3));
            animation: logoFloat 5s ease-in-out infinite;
        }

        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-8px); }
        }

        /* Card */
        .tracking-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(40px);
            -webkit-backdrop-filter: blur(40px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 28px;
            padding: 44px 36px;
            animation: cardSlideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            overflow: hidden;
        }

        .tracking-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
        }

        @keyframes cardSlideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Title */
        .tracking-title {
            text-align: center;
            margin-bottom: 32px;
        }

        .tracking-title h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 8px;
            letter-spacing: -0.02em;
        }

        .tracking-title p {
            font-size: 0.88rem;
            color: rgba(255, 255, 255, 0.45);
            margin: 0;
            font-weight: 400;
        }

        /* Search Box */
        .search-box {
            position: relative;
            margin-bottom: 24px;
        }

        .search-box input {
            width: 100%;
            padding: 18px 64px 18px 22px;
            background: rgba(255, 255, 255, 0.06);
            border: 1.5px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            font-size: 1.05rem;
            font-family: inherit;
            color: #ffffff;
            transition: all 0.3s ease;
            outline: none;
        }

        .search-box input::placeholder {
            color: rgba(255, 255, 255, 0.35);
        }

        .search-box input:focus {
            border-color: rgba(29, 138, 201, 0.6);
            background: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 0 4px rgba(29, 138, 201, 0.1), 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .search-box .search-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1D8AC9, #0ea5e9);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .search-box .search-btn:hover {
            transform: translateY(-50%) scale(1.06);
            box-shadow: 0 6px 20px rgba(29, 138, 201, 0.5);
        }

        .search-box .search-btn:active {
            transform: translateY(-50%) scale(0.98);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
            margin-bottom: 0;
            flex-wrap: wrap;
        }

        .action-buttons a,
        .action-buttons button {
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            font-family: inherit;
            text-decoration: none;
            transition: all 0.25s ease;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-outline {
            background: rgba(255, 255, 255, 0.05);
            border: 1.5px solid rgba(255, 255, 255, 0.12) !important;
            color: rgba(255, 255, 255, 0.6);
        }

        .btn-outline:hover {
            background: rgba(29, 138, 201, 0.12);
            border-color: rgba(29, 138, 201, 0.4) !important;
            color: #38bdf8;
            text-decoration: none;
        }

        .btn-reset {
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.5);
        }

        .btn-reset:hover {
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.8);
        }

        /* Tracking Results */
        .tracking-results {
            padding-top: 28px;
            margin-top: 28px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            animation: resultsFadeIn 0.5s ease-out;
        }

        @keyframes resultsFadeIn {
            from { opacity: 0; transform: translateY(15px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .results-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .results-header h3 {
            font-size: 1.05rem;
            font-weight: 600;
            color: #ffffff;
            margin-bottom: 12px;
        }

        .results-header .order-info {
            background: rgba(29, 138, 201, 0.12);
            border: 1px solid rgba(29, 138, 201, 0.2);
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 0.85rem;
            color: #38bdf8;
            display: inline-block;
            font-weight: 500;
        }

        /* Timeline */
        .tracking-timeline {
            position: relative;
            padding: 10px 0;
        }

        .timeline-line {
            position: absolute;
            left: 25px;
            top: 10px;
            bottom: 10px;
            width: 2px;
            background: linear-gradient(180deg, rgba(29, 138, 201, 0.3), rgba(255, 255, 255, 0.06));
            border-radius: 2px;
        }

        .timeline-item {
            display: flex;
            gap: 18px;
            margin-bottom: 20px;
            position: relative;
            animation: timelineItemIn 0.4s ease-out backwards;
        }

        .timeline-item:nth-child(2) { animation-delay: 0.1s; }
        .timeline-item:nth-child(3) { animation-delay: 0.2s; }
        .timeline-item:nth-child(4) { animation-delay: 0.3s; }

        @keyframes timelineItemIn {
            from { opacity: 0; transform: translateX(-10px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .timeline-icon {
            width: 52px;
            height: 52px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            color: white;
            flex-shrink: 0;
            z-index: 1;
            transition: all 0.4s ease;
        }

        .timeline-icon.japan {
            background: linear-gradient(135deg, #E63946, #ff6b6b);
            box-shadow: 0 6px 20px rgba(230, 57, 70, 0.35);
        }

        .timeline-icon.ship {
            background: linear-gradient(135deg, #1D8AC9, #0ea5e9);
            box-shadow: 0 6px 20px rgba(29, 138, 201, 0.35);
        }

        .timeline-icon.thailand {
            background: linear-gradient(135deg, #22c55e, #16a34a);
            box-shadow: 0 6px 20px rgba(34, 197, 94, 0.35);
        }

        .timeline-icon.pending {
            background: rgba(255, 255, 255, 0.06);
            color: rgba(255, 255, 255, 0.25);
            box-shadow: none;
        }

        .timeline-content {
            flex: 1;
            padding: 16px 20px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid rgba(255, 255, 255, 0.06);
            border-radius: 14px;
            transition: all 0.3s ease;
        }

        .timeline-content h4 {
            font-size: 0.95rem;
            font-weight: 600;
            color: #ffffff;
            margin: 0 0 4px 0;
        }

        .timeline-content .date {
            font-size: 0.82rem;
            color: rgba(255, 255, 255, 0.45);
            font-weight: 400;
        }

        .timeline-content .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 600;
            margin-top: 8px;
            letter-spacing: 0.02em;
        }

        .status.complete {
            background: rgba(34, 197, 94, 0.12);
            color: #4ade80;
            border: 1px solid rgba(34, 197, 94, 0.2);
        }

        .status.in-progress {
            background: rgba(29, 138, 201, 0.12);
            color: #38bdf8;
            border: 1px solid rgba(29, 138, 201, 0.2);
        }

        .status.waiting {
            background: rgba(255, 255, 255, 0.05);
            color: rgba(255, 255, 255, 0.35);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        /* Footer Text */
        .tracking-footer {
            text-align: center;
            margin-top: 28px;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.2);
        }

        .tracking-footer a {
            color: rgba(255, 255, 255, 0.3);
            text-decoration: none;
            transition: color 0.2s;
        }

        .tracking-footer a:hover {
            color: #38bdf8;
        }

        /* SweetAlert Override - Dark Modern Theme */
        .swal2-container {
            z-index: 9999 !important;
            backdrop-filter: blur(8px) !important;
            -webkit-backdrop-filter: blur(8px) !important;
            background: rgba(10, 22, 40, 0.6) !important;
        }

        .swal2-popup {
            font-size: 0.92rem !important;
            font-family: 'Noto Sans Thai', sans-serif !important;
            border-radius: 24px !important;
            background: rgba(15, 23, 42, 0.95) !important;
            backdrop-filter: blur(40px) !important;
            -webkit-backdrop-filter: blur(40px) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.5), 0 0 0 1px rgba(255, 255, 255, 0.05) inset !important;
            padding: 32px 28px !important;
            color: #ffffff !important;
        }

        .swal2-title {
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 1.4rem !important;
        }

        .swal2-html-container {
            color: rgba(255, 255, 255, 0.6) !important;
            font-size: 0.92rem !important;
        }

        .swal2-icon.swal2-warning {
            border-color: #f59e0b !important;
            color: #f59e0b !important;
        }

        .swal2-icon.swal2-error {
            border-color: #ef4444 !important;
        }

        .swal2-icon.swal2-error [class^='swal2-x-mark-line'] {
            background-color: #ef4444 !important;
        }

        .swal2-icon.swal2-success {
            border-color: #22c55e !important;
            color: #22c55e !important;
        }

        .swal2-icon.swal2-success [class^='swal2-success-line'] {
            background-color: #22c55e !important;
        }

        .swal2-icon.swal2-success .swal2-success-ring {
            border-color: rgba(34, 197, 94, 0.3) !important;
        }

        .swal2-confirm.swal2-styled {
            background: linear-gradient(135deg, #1D8AC9, #0ea5e9) !important;
            border: none !important;
            border-radius: 12px !important;
            padding: 12px 32px !important;
            font-family: 'Noto Sans Thai', sans-serif !important;
            font-weight: 600 !important;
            font-size: 0.9rem !important;
            box-shadow: 0 4px 15px rgba(29, 138, 201, 0.4) !important;
            transition: all 0.2s ease !important;
        }

        .swal2-confirm.swal2-styled:hover {
            box-shadow: 0 6px 20px rgba(29, 138, 201, 0.6) !important;
            transform: translateY(-1px);
        }

        .swal2-timer-progress-bar {
            background: rgba(29, 138, 201, 0.5) !important;
        }

        /* Responsive */
        @media (max-width: 576px) {
            .tracking-container {
                padding: 16px;
            }

            .tracking-card {
                padding: 32px 24px;
                border-radius: 22px;
            }

            .tracking-logo img {
                max-width: 170px;
            }

            .tracking-title h1 {
                font-size: 1.25rem;
            }

            .search-box input {
                padding: 16px 58px 16px 18px;
                font-size: 0.95rem;
            }

            .search-box .search-btn {
                width: 42px;
                height: 42px;
            }

            .action-buttons a,
            .action-buttons button {
                padding: 9px 16px;
                font-size: 0.8rem;
            }

            .timeline-icon {
                width: 44px;
                height: 44px;
                border-radius: 12px;
                font-size: 1rem;
            }

            .timeline-content {
                padding: 14px 16px;
            }
        }
    </style>
@endsection

@section('extra-script')
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function () {
            $('#trackno').keypress(function (e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#submitForm').click();
                }
            });

            $('#submitForm').on('click', function () {
                var trackingNo = $('#trackno').val();

                if (!trackingNo.trim()) {
                    Swal.fire({
                        title: 'แจ้งเตือน!',
                        text: 'กรุณากรอกเลขพัสดุ',
                        icon: 'warning',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    return;
                }

                hideAll();

                $.ajax({
                    url: '{{ route("submit-tracking") }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        tracking_no: trackingNo
                    },
                    success: function (response) {
                        var data = response.track;
                        $('#orderdata').html('');

                        if (response.success) {
                            $('#tracking-body').removeClass('d-none');

                            // เปลี่ยน icon/text ตามประเภทขนส่ง
                            var isAir = response.track.shipping_method == 2;
                            var $step2 = $('.timeline-item').eq(1);
                            if (isAir) {
                                $step2.find('.timeline-icon i').removeClass('fa-ship').addClass('fa-plane');
                                $step2.find('.timeline-content h4').text('จัดส่งทางอากาศ');
                                $step2.find('.status').html('⟳ อยู่ระหว่างขนส่ง (3-7 วัน)');
                            } else {
                                $step2.find('.timeline-icon i').removeClass('fa-plane').addClass('fa-ship');
                                $step2.find('.timeline-content h4').text('ขึ้นตู้สินค้า');
                                $step2.find('.status').html('⟳ อยู่ระหว่างขนส่ง');
                            }

                            if (response.track.source_date !== null) {
                                showTrackingItem(0);
                                setTrackingDate(0, response.track.source_date);
                                $('.timeline-item').eq(0).find('.timeline-icon').removeClass('pending').addClass('japan');
                            }

                            if (response.track.ship_date !== null) {
                                showTrackingItem(1);
                                setTrackingDate(1, response.track.ship_date);
                                $('.timeline-item').eq(1).find('.timeline-icon').removeClass('pending').addClass('ship');
                            }

                            if (response.track.destination_date !== null) {
                                showTrackingItem(2);
                                setTrackingDate(2, response.track.destination_date);
                                $('.timeline-item').eq(2).find('.timeline-icon').removeClass('pending').addClass('thailand');
                            }

                            let cod = data.total_cod ? " COD: ¥" + data.total_cod + ' |' : '';
                            let weight = data.total_weight ? " Weight: " + data.total_weight.toFixed(2) + " kg |" : '';
                            let boxCount = data.box_count ? " จำนวน: " + data.box_count + ' กล่อง' : '';
                            $('#orderdata').html(cod + weight + boxCount);
                        } else {
                            Swal.fire({
                                title: 'ไม่พบข้อมูล!',
                                text: 'ไม่พบข้อมูลเลขพัสดุที่ค้นหา',
                                icon: 'error',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: 'เกิดข้อผิดพลาด!',
                            text: 'กรุณาลองใหม่อีกครั้ง',
                            icon: 'error',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });

            $('#reset').on('click', function (event) {
                event.preventDefault();
                hideAll();
                $('#trackno').val('');
                $('#orderdata').html('');
            });
        });

        function hideAll() {
            $('#tracking-body, .timeline-item').addClass('d-none');
            // Reset icons to pending
            $('.timeline-item').find('.timeline-icon').removeClass('japan ship thailand').addClass('pending');
        }

        function showTrackingItem(index) {
            $('.timeline-item').eq(index).removeClass('d-none');
        }

        function setTrackingDate(index, dateval) {
            $('.timeline-item').eq(index).find('.date').html(dateval);
        }
    </script>
@endsection

@section('content')
    <!-- Floating Particles -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <div class="tracking-container">
        <!-- Logo -->
        <div class="tracking-logo">
            <img src="{{ asset('img/skj-logo-full.png') }}" alt="SKJ Japan Shipping">
        </div>

        <div class="tracking-card">
            <!-- Title -->
            <div class="tracking-title">
                <h1>เช็คเลขพัสดุ</h1>
                <p>ติดตามสถานะการขนส่งสินค้าจากญี่ปุ่น</p>
            </div>

            <!-- Search Box -->
            <div class="search-box">
                <input type="text" id="trackno" placeholder="กรอกเลขพัสดุของคุณ..." autofocus>
                <button class="search-btn" type="button" id="submitForm">
                    <i class="fa fa-search"></i>
                </button>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="{{ route('login') }}" class="btn-outline">
                    <i class="fa fa-sign-in"></i> เข้าสู่ระบบ
                </a>
                <a href="{{ route('register') }}" class="btn-outline">
                    <i class="fa fa-user-plus"></i> สมัครสมาชิก
                </a>
                <button class="btn-reset" id="reset">
                    <i class="fa fa-refresh"></i> รีเซ็ต
                </button>
            </div>

            <!-- Tracking Results -->
            <div class="tracking-results d-none" id="tracking-body">
                <div class="results-header">
                    <h3>ข้อมูลการขนส่ง</h3>
                    <div class="order-info" id="orderdata"></div>
                </div>

                <!-- Timeline -->
                <div class="tracking-timeline">
                    <div class="timeline-line"></div>

                    <div class="timeline-item d-none">
                        <div class="timeline-icon pending">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>คลังสินค้าญี่ปุ่น</h4>
                            <div class="date"></div>
                            <span class="status complete">✓ รับสินค้า</span>
                        </div>
                    </div>

                    <div class="timeline-item d-none">
                        <div class="timeline-icon pending">
                            <i class="fa fa-ship"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>ขึ้นตู้สินค้า</h4>
                            <div class="date"></div>
                            <span class="status in-progress">⟳ อยู่ระหว่างขนส่ง</span>
                        </div>
                    </div>

                    <div class="timeline-item d-none">
                        <div class="timeline-icon pending">
                            <i class="fa fa-flag"></i>
                        </div>
                        <div class="timeline-content">
                            <h4>สินค้าถึงไทยแล้ว</h4>
                            <div class="date"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="tracking-footer">
            &copy; {{ date('Y') }} SKJ Japan Shipping Company
        </div>
    </div>
@endsection