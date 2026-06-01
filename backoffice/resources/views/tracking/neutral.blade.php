<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $brand['name'] ?? 'Japan Tracking' }} · เช็คเลขพัสดุ</title>
    <meta name="description" content="ติดตามสถานะพัสดุของคุณแบบเรียลไทม์">
    <link rel="icon" type="image/png" href="{{ asset('img/jt-favicon.png') }}?v=3">
    <link rel="apple-touch-icon" href="{{ asset('img/jt-favicon.png') }}?v=3">

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Noto+Sans+Thai:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">

    <style>
        :root {
            --blue: #2f6bff;
            --blue-2: #5b8cff;
            --red: #ff3b5c;
            --red-2: #ff6b7f;
            --ink: #14102e;
            --card-bg: rgba(255, 255, 255, 0.08);
            --card-border: rgba(255, 255, 255, 0.18);
            --text: #f5f6ff;
            --muted: rgba(245, 246, 255, 0.64);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            min-height: 100%;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif;
            color: var(--text);
            -webkit-font-smoothing: antialiased;
        }

        body {
            position: relative;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 16px;
            background:
                linear-gradient(180deg, rgba(20,16,46,0.50) 0%, rgba(20,16,46,0.28) 42%, rgba(20,16,46,0.80) 100%),
                url('{{ asset('img/jt-bg.png') }}?v=2') center center / cover no-repeat fixed,
                #1e1840;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background:
                radial-gradient(40% 40% at 16% 24%, rgba(47,107,255,0.30), transparent 70%),
                radial-gradient(40% 40% at 84% 76%, rgba(255,59,92,0.26), transparent 70%);
            pointer-events: none;
            z-index: 0;
        }

        .wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 560px;
            text-align: center;
        }

        /* ===== Brand wordmark ===== */
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 30px;
        }
        .brand .b-icon {
            width: 60px; height: 60px;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 14px 34px rgba(47,107,255,0.45);
        }
        .brand .b-icon img { width: 100%; height: 100%; display: block; object-fit: cover; }
        .brand .b-txt { text-align: left; }
        .brand .b-name { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.02em; line-height: 1; }
        .brand .b-sub {
            font-size: 0.66rem; font-weight: 600; letter-spacing: 0.28em;
            text-transform: uppercase; color: var(--blue-2); margin-top: 5px;
        }

        /* ===== Card ===== */
        .card {
            background: linear-gradient(180deg, #211d4d 0%, #1a1640 100%);
            border: 1px solid rgba(255, 255, 255, 0.10);
            border-radius: 28px;
            padding: 34px 30px 30px;
            box-shadow: 0 30px 70px rgba(8, 6, 32, 0.55);
        }

        .live-badge {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 6px 14px; border-radius: 999px;
            background: rgba(47,107,255,0.16);
            border: 1px solid rgba(91,140,255,0.36);
            color: var(--blue-2); font-size: 0.72rem; font-weight: 700;
            letter-spacing: 0.12em; text-transform: uppercase;
        }
        .live-badge .dot {
            width: 7px; height: 7px; border-radius: 50%; background: #22c55e;
            box-shadow: 0 0 0 0 rgba(34,197,94,0.7);
            animation: pulse 1.8s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(34,197,94,0.6); }
            70% { box-shadow: 0 0 0 8px rgba(34,197,94,0); }
            100% { box-shadow: 0 0 0 0 rgba(34,197,94,0); }
        }

        .card h1 {
            font-size: 1.85rem; font-weight: 800; margin: 16px 0 8px;
            letter-spacing: -0.02em;
        }
        .card .tagline { color: var(--muted); font-size: 0.95rem; margin-bottom: 24px; }

        /* ===== Service chips ===== */
        .chips {
            display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;
            margin-bottom: 22px;
        }
        .chip {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px; padding: 13px 8px; transition: .25s;
        }
        .chip:hover { background: rgba(255,255,255,0.1); transform: translateY(-3px); }
        .chip .ico { font-size: 1.35rem; }
        .chip .t { font-size: 0.78rem; font-weight: 700; margin-top: 5px; }
        .chip .s { font-size: 0.66rem; color: var(--muted); margin-top: 2px; }

        /* ===== Search ===== */
        .search { position: relative; margin-bottom: 12px; }
        .search input {
            width: 100%;
            padding: 18px 64px 18px 20px;
            border-radius: 18px;
            border: 1.5px solid rgba(255,255,255,0.18);
            background: rgba(16,13,42,0.55);
            color: #fff; font-size: 1rem; font-family: inherit;
            outline: none; transition: .25s;
        }
        .search input::placeholder { color: rgba(245,246,255,0.42); }
        .search input:focus {
            border-color: var(--blue);
            box-shadow: 0 0 0 4px rgba(47,107,255,0.24);
            background: rgba(16,13,42,0.75);
        }
        .search .btn-go {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 48px; height: 48px; border: none; border-radius: 14px;
            background: linear-gradient(135deg, var(--blue), var(--red));
            color: #fff; font-size: 1.05rem; cursor: pointer; transition: .2s;
            box-shadow: 0 8px 20px rgba(47,107,255,0.45);
        }
        .search .btn-go:hover { filter: brightness(1.08); transform: translateY(-50%) scale(1.05); }

        .hint { color: var(--muted); font-size: 0.8rem; margin-bottom: 18px; }
        .hint i { color: var(--blue-2); }

        .btn-reset {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 11px 22px; border-radius: 14px;
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.14);
            color: var(--text); font-size: 0.88rem; font-weight: 600;
            font-family: inherit; cursor: pointer; transition: .2s;
        }
        .btn-reset:hover { background: rgba(255,255,255,0.12); }

        /* ===== Results / Timeline ===== */
        .results { margin-top: 26px; text-align: left; display: none; }
        .results.show { display: block; animation: rise .4s ease; }
        @keyframes rise { from { opacity: 0; transform: translateY(14px); } to { opacity: 1; transform: none; } }

        .results-head {
            display: flex; flex-direction: column; gap: 4px;
            padding-bottom: 16px; margin-bottom: 18px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .results-head h3 { font-size: 1.05rem; font-weight: 700; }
        .order-info { color: var(--blue-2); font-size: 0.82rem; font-weight: 600; }

        .timeline { position: relative; padding-left: 6px; }
        .timeline .line {
            position: absolute; left: 27px; top: 14px; bottom: 14px; width: 2px;
            background: linear-gradient(180deg, var(--blue), var(--red));
            opacity: 0.45;
        }
        .step { position: relative; display: flex; gap: 16px; align-items: flex-start; padding: 10px 0; }
        .step.d-none { display: none; }
        .step .s-icon {
            flex: 0 0 auto; width: 44px; height: 44px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.05rem; z-index: 1;
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.14);
            color: var(--muted);
        }
        .step .s-icon.japan { background: linear-gradient(135deg,#2f6bff,#5b8cff); color:#fff; border-color: transparent; box-shadow: 0 8px 20px rgba(47,107,255,0.45); }
        .step .s-icon.ship  { background: linear-gradient(135deg,#7b5bff,#a06bff); color:#fff; border-color: transparent; box-shadow: 0 8px 20px rgba(123,91,255,0.4); }
        .step .s-icon.thailand { background: linear-gradient(135deg,#ff3b5c,#ff6b7f); color:#fff; border-color: transparent; box-shadow: 0 8px 20px rgba(255,59,92,0.45); }
        .step .s-body { flex: 1; padding-top: 2px; }
        .step .s-body h4 { font-size: 0.98rem; font-weight: 700; }
        .step .s-body .date { font-size: 0.8rem; color: var(--muted); margin-top: 2px; }
        .step .s-body .status { display: inline-block; margin-top: 6px; font-size: 0.78rem; font-weight: 600; }
        .step .s-body .status.complete { color: #4ade80; }
        .step .s-body .status.in-progress { color: var(--blue-2); }

        /* ===== Footer ===== */
        .footer {
            position: relative; z-index: 1;
            margin-top: 26px; text-align: center;
            color: rgba(245,246,255,0.52); font-size: 0.8rem;
        }

        /* ===== SweetAlert (themed) ===== */
        .swal2-container.swal2-backdrop-show { background: rgba(10, 8, 32, 0.55) !important; backdrop-filter: blur(4px); }
        .swal2-popup {
            background: rgba(24, 20, 56, 0.94) !important;
            border: 1px solid var(--card-border) !important;
            border-radius: 24px !important;
            color: var(--text) !important;
            font-family: 'Inter', 'Noto Sans Thai', sans-serif !important;
            box-shadow: 0 30px 70px rgba(8, 6, 32, 0.6) !important;
            padding: 26px 22px 24px !important;
        }
        .swal2-title { color: var(--text) !important; font-weight: 700 !important; font-size: 1.3rem !important; }
        .swal2-html-container { color: var(--muted) !important; font-size: 0.95rem !important; }
        .swal2-timer-progress-bar { background: linear-gradient(90deg, var(--blue), var(--red)) !important; }
        .swal2-close { color: var(--muted) !important; }
        .swal2-close:hover { color: var(--text) !important; }

        .swal2-icon { border-width: 3px !important; margin: 8px auto 18px !important; }
        .swal2-icon.swal2-warning { border-color: rgba(255,59,92,0.55) !important; color: var(--red) !important; }
        .swal2-icon.swal2-error { border-color: rgba(255,59,92,0.55) !important; }
        .swal2-icon.swal2-error [class^='swal2-x-mark-line'] { background-color: var(--red) !important; }
        .swal2-icon.swal2-success { border-color: rgba(74,222,128,0.5) !important; }
        .swal2-icon.swal2-success [class^='swal2-success-line'] { background-color: #4ade80 !important; }
        .swal2-icon.swal2-success .swal2-success-ring { border-color: rgba(74,222,128,0.4) !important; }
        .swal2-icon.swal2-info { border-color: rgba(91,140,255,0.55) !important; color: var(--blue-2) !important; }

        @media (max-width: 480px) {
            .card { padding: 26px 18px 24px; border-radius: 22px; }
            .card h1 { font-size: 1.5rem; }
            .brand .b-name { font-size: 1.25rem; }
            .brand .b-icon { width: 50px; height: 50px; font-size: 1.35rem; }
            .chips { gap: 7px; }
            .chip { padding: 10px 5px; }
            .chip .t { font-size: 0.68rem; }
            .chip .s { font-size: 0.58rem; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="brand">
            <div class="b-icon"><img src="{{ asset('img/jt-favicon.png') }}?v=3" alt="{{ $brand['name'] ?? 'Japan Tracking' }}"></div>
            <div class="b-txt">
                <div class="b-name">{{ $brand['name'] ?? 'Japan Tracking' }}</div>
                <div class="b-sub">Parcel Tracking</div>
            </div>
        </div>

        <div class="card">
            <div class="live-badge"><span class="dot"></span> Live Tracking</div>
            <h1>เช็คเลขพัสดุ</h1>
            <p class="tagline">{{ $brand['tagline'] ?? 'ติดตามสถานะพัสดุของคุณแบบเรียลไทม์' }}</p>

            <div class="chips">
                <div class="chip">
                    <div class="ico">🚢</div>
                    <div class="t">ขนส่งทางเรือ</div>
                    <div class="s">20–25 วัน</div>
                </div>
                <div class="chip">
                    <div class="ico">✈️</div>
                    <div class="t">ขนส่งทางอากาศ</div>
                    <div class="s">3–7 วัน</div>
                </div>
                <div class="chip">
                    <div class="ico">📦</div>
                    <div class="t">ถึงปลายทาง</div>
                    <div class="s">แมส · รับเอง · ส่ง</div>
                </div>
            </div>

            <div class="search">
                <input type="text" id="trackno" placeholder="กรอกเลขพัสดุ เช่น 1234567890" autofocus>
                <button class="btn-go" type="button" id="submitForm" title="ค้นหา"><i class="fa fa-search"></i></button>
            </div>
            <p class="hint"><i class="fa fa-info-circle"></i> เลขพัสดุที่ได้รับหลังสินค้าออกจากร้านค้าต้นทาง</p>

            <button class="btn-reset" type="button" id="reset"><i class="fa fa-refresh"></i> รีเซ็ต</button>

            <div class="results" id="tracking-body">
                <div class="results-head">
                    <h3>ข้อมูลการขนส่ง</h3>
                    <div class="order-info" id="orderdata"></div>
                </div>
                <div class="timeline">
                    <div class="line"></div>

                    <div class="step d-none">
                        <div class="s-icon pending"><i class="fa fa-cubes"></i></div>
                        <div class="s-body">
                            <h4>รับเข้าคลังต้นทาง</h4>
                            <div class="date"></div>
                            <span class="status complete">✓ รับสินค้า</span>
                        </div>
                    </div>

                    <div class="step d-none">
                        <div class="s-icon pending"><i class="fa fa-ship"></i></div>
                        <div class="s-body">
                            <h4>ขึ้นตู้สินค้า</h4>
                            <div class="date"></div>
                            <span class="status in-progress">⟳ อยู่ระหว่างขนส่ง</span>
                        </div>
                    </div>

                    <div class="step d-none">
                        <div class="s-icon pending"><i class="fa fa-flag-checkered"></i></div>
                        <div class="s-body">
                            <h4>ถึงปลายทางแล้ว</h4>
                            <div class="date"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">&copy; {{ date('Y') }} {{ $brand['footer'] ?? 'Japan Tracking' }}</div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            var $steps = $('.timeline .step');

            function hideAll() {
                $('#tracking-body').removeClass('show');
                $steps.addClass('d-none');
                $steps.find('.s-icon').removeClass('japan ship thailand').addClass('pending');
            }
            function showStep(i) { $steps.eq(i).removeClass('d-none'); }
            function setDate(i, v) { $steps.eq(i).find('.date').html(v); }

            $('#trackno').keypress(function (e) {
                if (e.which === 13) { e.preventDefault(); $('#submitForm').click(); }
            });

            $('#submitForm').on('click', function () {
                var trackingNo = $('#trackno').val();
                if (!trackingNo.trim()) {
                    Swal.fire({ title: 'กรุณากรอกเลขพัสดุ', icon: 'warning', timer: 1500, showConfirmButton: false });
                    return;
                }
                hideAll();
                $.ajax({
                    url: '{{ route("submit-tracking") }}',
                    method: 'POST',
                    data: { _token: '{{ csrf_token() }}', tracking_no: trackingNo },
                    success: function (response) {
                        if (!response.success) {
                            Swal.fire({ title: 'ไม่พบข้อมูล!', text: 'ไม่พบข้อมูลเลขพัสดุที่ค้นหา', icon: 'error', timer: 1700, showConfirmButton: false });
                            return;
                        }
                        var data = response.track;
                        $('#orderdata').html('');
                        $('#tracking-body').addClass('show');

                        var isAir = data.shipping_method == 2;
                        var $s2 = $steps.eq(1);
                        if (isAir) {
                            $s2.find('.s-icon i').removeClass('fa-ship').addClass('fa-plane');
                            $s2.find('h4').text('จัดส่งทางอากาศ');
                            $s2.find('.status').html('⟳ อยู่ระหว่างขนส่ง (3-7 วัน)');
                        } else {
                            $s2.find('.s-icon i').removeClass('fa-plane').addClass('fa-ship');
                            $s2.find('h4').text('ขึ้นตู้สินค้า');
                            $s2.find('.status').html('⟳ อยู่ระหว่างขนส่ง');
                        }

                        if (data.source_date !== null) {
                            showStep(0); setDate(0, data.source_date);
                            $steps.eq(0).find('.s-icon').removeClass('pending').addClass('japan');
                        }
                        if (data.ship_date !== null) {
                            showStep(1); setDate(1, data.ship_date);
                            $steps.eq(1).find('.s-icon').removeClass('pending').addClass('ship');
                        }
                        if (data.destination_date !== null) {
                            showStep(2); setDate(2, data.destination_date);
                            $steps.eq(2).find('.s-icon').removeClass('pending').addClass('thailand');
                        }

                        var cod = data.total_cod ? ' COD: ¥' + data.total_cod + ' |' : '';
                        var weight = data.total_weight ? ' น้ำหนัก: ' + data.total_weight.toFixed(2) + ' kg |' : '';
                        var boxCount = data.box_count ? ' จำนวน: ' + data.box_count + ' กล่อง' : '';
                        $('#orderdata').html((cod + weight + boxCount).replace(/\|\s*$/, ''));
                    },
                    error: function () {
                        Swal.fire({ title: 'เกิดข้อผิดพลาด!', text: 'กรุณาลองใหม่อีกครั้ง', icon: 'error', timer: 1500, showConfirmButton: false });
                    }
                });
            });

            $('#reset').on('click', function (e) {
                e.preventDefault();
                hideAll();
                $('#trackno').val('');
                $('#orderdata').html('');
            });
        });
    </script>
</body>
</html>
