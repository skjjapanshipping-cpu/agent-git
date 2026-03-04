<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="apple-touch-icon" sizes="76x76" href="{{asset('dashboard/assets/img/apple-icon.png')}}">
    <link rel="icon" type="image/png" href="{{asset('dashboard/assets/img/favicon.png')}}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <title>
        @yield('title')
    </title>
    <meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0, shrink-to-fit=no'
        name='viewport' />
    <meta name="format-detection" content="telephone=no, date=no, email=no, address=no">
    <!--     Fonts and icons     -->
    <link href="https://fonts.googleapis.com/css?family=Montserrat:400,700,200" rel="stylesheet" />
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <!-- CSS Files -->
    <link href="{{asset('dashboard/assets/css/bootstrap.min.css')}}" rel="stylesheet" />
    <link href="{{asset('dashboard/assets/css/paper-dashboard.css?v=2.0.0')}}" rel="stylesheet" />

    <link href="{{ asset('dashboard/assets/datatable/dataTables.bootstrap4.min.css') }}" rel="stylesheet" />

    <!-- <link href="{{ asset('dashboard/assets/datatable/rowReorder.dataTables.min.css') }}" rel="stylesheet" /> -->
    <link href="{{ asset('dashboard/assets/datatable/responsive.dataTables.min.css') }}" rel="stylesheet" />


    <!-- CSS Just for demo purpose, don't include it in your project -->
    <link href="{{asset('dashboard/assets/demo/demo.css')}}" rel="stylesheet" />

    <!-- Styles -->
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style-modern.css') }}?v={{ time() }}" rel="stylesheet">

    @yield('extra-css')

    <style>
        /* === Page Loader — กึ่งกลางจอเสมอ === */
        .page-loader {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            background: rgba(255, 255, 255, 0.92) !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
            align-items: center !important;
            z-index: 99999 !important;
            transition: opacity 0.5s ease;
        }
        .page-loader.fade-out {
            opacity: 0;
            pointer-events: none;
        }
        .page-loader-spinner {
            width: 44px;
            height: 44px;
            border: 4px solid rgba(29, 138, 201, 0.2);
            border-top-color: #1D8AC9;
            border-radius: 50%;
            animation: page-spin 0.8s linear infinite;
        }
        .page-loader-text {
            margin-top: 14px;
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }
        @keyframes page-spin {
            to { transform: rotate(360deg); }
        }

        /* === ซ่อน DataTables Processing ตัวเดิม (ใช้ custom overlay แทน) === */
        .dataTables_processing {
            display: none !important;
        }

        /* === Custom Processing Overlay — แปะที่ body กึ่งกลางจอเสมอ === */
        .dt-loading-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99998;
            justify-content: center;
            align-items: center;
        }
        .dt-loading-overlay.active {
            display: flex;
        }
        .dt-loading-box {
            background: rgba(255, 255, 255, 0.97);
            padding: 20px 40px;
            border-radius: 12px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
            font-size: 15px;
            color: #1D8AC9;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .dt-loading-box .dt-spinner {
            width: 22px;
            height: 22px;
            border: 3px solid rgba(29, 138, 201, 0.2);
            border-top-color: #1D8AC9;
            border-radius: 50%;
            animation: page-spin 0.7s linear infinite;
        }

        /* === Top Progress Bar === */
        .top-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            height: 3px;
            width: 0;
            background: linear-gradient(90deg, #1D8AC9, #36d1dc);
            z-index: 100000;
            transition: width 0.4s ease;
        }
        .top-progress-bar.active {
            width: 70%;
            transition: width 8s cubic-bezier(0.1, 0.5, 0.3, 1);
        }
        .top-progress-bar.done {
            width: 100%;
            transition: width 0.3s ease;
        }

        /* === Content Fade-in === */
        .content-fade-in {
            animation: fadeInContent 0.4s ease forwards;
        }
        @keyframes fadeInContent {
            from { opacity: 0; transform: translateY(8px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* === Button Loading === */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.75;
        }
        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin-top: -8px;
            margin-left: -8px;
            border: 2px solid rgba(255, 255, 255, 0.4);
            border-top-color: #fff;
            border-radius: 50%;
            animation: page-spin 0.6s linear infinite;
        }
    </style>

</head>

<body class="">
    <!-- DataTables Loading Overlay (อยู่ที่ body โดยตรง — กึ่งกลางจอเสมอ) -->
    <div class="dt-loading-overlay" id="dtLoadingOverlay">
        <div class="dt-loading-box">
            <div class="dt-spinner"></div>
            กำลังโหลด...
        </div>
    </div>

    <!-- Page Loader -->
    <div class="page-loader" id="pageLoader">
        <div class="page-loader-spinner"></div>
        <div class="page-loader-text">กำลังโหลด...</div>
    </div>
    <!-- Top Progress Bar -->
    <div class="top-progress-bar" id="topProgressBar"></div>

    @if(session()->has('impersonator_id'))
    <div style="position:fixed;top:0;left:0;right:0;z-index:100001;background:#e74c3c;color:#fff;text-align:center;padding:8px 16px;font-size:14px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:12px;">
        <span><i class="fa fa-exclamation-triangle"></i> กำลังเข้าสู่ระบบแทน: {{ Auth::user()->name }} ({{ Auth::user()->customerno }})</span>
        <a href="{{ route('users.stop-impersonate') }}" style="background:#fff;color:#e74c3c;padding:4px 16px;border-radius:4px;text-decoration:none;font-weight:700;font-size:13px;">
            <i class="fa fa-arrow-left"></i> กลับเป็น Admin
        </a>
    </div>
    <div style="height:40px;"></div>
    @endif

    @yield('content')
    <!--   Core JS Files   -->
    <script src="{{asset('dashboard/assets/js/core/jquery.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/js/core/popper.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/js/core/bootstrap.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/js/plugins/perfect-scrollbar.jquery.min.js')}}"></script>
    <!--  Google Maps Plugin    -->
    <!-- <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_KEY_HERE"></script> -->

    <!--  Notifications Plugin    -->
    <script src="{{asset('dashboard/assets/js/plugins/bootstrap-notify.js')}}"></script>
    <!-- Control Center for Now Ui Dashboard: parallax effects, scripts for the example pages etc -->
    <script src="{{asset('dashboard/assets/js/paper-dashboard.min.js?v=2.0.0')}}"></script>

    <script src="{{asset('dashboard/assets/datatable/jquery.dataTables.min.js')}}"></script>
    <script src="{{asset('dashboard/assets/datatable/dataTables.bootstrap4.min.js')}}"></script>
    <!-- <script src="{{asset('dashboard/assets/datatable/dataTables.rowReorder.min.js')}}"></script> -->
    <script src="{{asset('dashboard/assets/datatable/dataTables.responsive.min.js')}}"></script>

    <!-- Paper Dashboard DEMO methods, don't include it in your project! -->
    <script src="{{asset('dashboard/assets/demo/demo.js')}}"></script>
    <!-- Scripts -->
    <script src="{{ asset('js/main.js') }}" defer></script>

    <script>


        $(document).ready(function () {
            $('#dt-mant-table-fix-showall').DataTable({

            });
            $('#dt-mant-table').DataTable({
                //"dom": 'lfrtip'
                "dom": 'frti',
                //responsive: true
            });
        });

        function showAmphoes(province = "#province", distrinct = "#distrinct") {
            let input_province = document.querySelector(province);
            let url = "{{ url('/api/amphoes') }}?province=" + input_province.value;
            console.log(url);
            // if(input_province.value == "") return;
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    console.log(result);
                    //UPDATE SELECT OPTION
                    let input_amphoe = document.querySelector(distrinct);
                    input_amphoe.innerHTML = '<option value="">กรุณาเลือกเขต/อำเภอ</option>';
                    for (let item of result) {
                        let option = document.createElement("option");
                        option.text = item.amphoe;
                        option.value = item.amphoe;
                        input_amphoe.appendChild(option);
                    }

                    //QUERY AMPHOES
                    showTambons();
                });
        }

        function showTambons(province = "#province", distrinct = "#distrinct", subdistrinct = "#subdistrinct") {
            let input_province = document.querySelector(province);
            let input_amphoe = document.querySelector(distrinct);
            let url = "{{ url('/api/tambons') }}?province=" + input_province.value + "&amphoe=" + input_amphoe.value;
            console.log(url);
            // if(input_province.value == "") return;
            // if(input_amphoe.value == "") return;
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    console.log(result);
                    //UPDATE SELECT OPTION
                    let input_tambon = document.querySelector(subdistrinct);
                    input_tambon.innerHTML = '<option value="">กรุณาเลือกแขวง/ตำบล</option>';
                    for (let item of result) {
                        let option = document.createElement("option");
                        option.text = item.tambon;
                        option.value = item.tambon;
                        input_tambon.appendChild(option);
                    }
                    //QUERY AMPHOES
                    // showZipcode();
                });
        }

        function showZipcode(province = "#province", distrinct = "#distrinct", subdistrinct = "#subdistrinct", postcode = "#postcode") {
            let input_province = document.querySelector(province);
            let input_amphoe = document.querySelector(distrinct);
            let input_tambon = document.querySelector(subdistrinct);
            let url = "{{ url('/api/zipcodes') }}?province=" + input_province.value + "&amphoe=" + input_amphoe.value +
                "&tambon=" + input_tambon.value;
            console.log(url);
            // if(input_province.value == "") return;
            // if(input_amphoe.value == "") return;
            // if(input_tambon.value == "") return;
            fetch(url)
                .then(response => response.json())
                .then(result => {
                    console.log(result);
                    //UPDATE SELECT OPTION
                    let input_zipcode = document.querySelector(postcode);
                    input_zipcode.value = "";
                    for (let item of result) {
                        input_zipcode.value = item.zipcode;
                        break;
                    }
                });
        }
    </script>
    <!-- Global Sidebar Toggle Script -->
    <script>
    (function(){
        var toggleBtn = document.getElementById('navSidebarToggle');
        var sidebar = document.querySelector('.sidebar-modern');
        var overlay = document.getElementById('sidebarOverlayGlobal');

        function openSidebar() {
            if (sidebar) sidebar.classList.add('show');
            if (overlay) overlay.classList.add('show');
            document.body.style.overflow = 'hidden';
        }
        function closeSidebar() {
            if (sidebar) sidebar.classList.remove('show');
            if (overlay) overlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        if (toggleBtn) toggleBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (sidebar && sidebar.classList.contains('show')) {
                closeSidebar();
            } else {
                openSidebar();
            }
        });
        if (overlay) overlay.addEventListener('click', closeSidebar);

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeSidebar();
        });
    })();
    </script>
    <!-- Loading Animations Script -->
    <script>
    (function(){
        var loader = document.getElementById('pageLoader');
        var progressBar = document.getElementById('topProgressBar');

        // Page Load: fade out loader when DOM is ready
        function hideLoader() {
            if (loader && !loader.classList.contains('fade-out')) {
                loader.classList.add('fade-out');
                setTimeout(function(){ loader.style.display = 'none'; }, 500);
                var content = document.querySelector('.main-panel > .content') || document.querySelector('.dashboard-content');
                if (content) content.classList.add('content-fade-in');
            }
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', hideLoader);
        } else {
            hideLoader();
        }
        setTimeout(hideLoader, 2000);

        // Top Progress Bar for AJAX (jQuery)
        if (typeof $ !== 'undefined') {
            $(document).ajaxStart(function() {
                if (progressBar) {
                    progressBar.classList.remove('done');
                    progressBar.classList.add('active');
                }
            });
            $(document).ajaxStop(function() {
                if (progressBar) {
                    progressBar.classList.remove('active');
                    progressBar.classList.add('done');
                    setTimeout(function(){
                        progressBar.classList.remove('done');
                        progressBar.style.width = '0';
                    }, 800);
                }
            });

            // DataTables processing → show/hide custom overlay
            $(document).on('processing.dt', function(e, settings, processing) {
                var overlay = document.getElementById('dtLoadingOverlay');
                if (overlay) {
                    if (processing) {
                        overlay.classList.add('active');
                    } else {
                        overlay.classList.remove('active');
                    }
                }
            });

            // Button loading on form submit
            $(document).on('submit', 'form', function() {
                var btn = $(this).find('input[type="submit"], button[type="submit"]');
                if (btn.length && !btn.hasClass('btn-loading')) {
                    btn.addClass('btn-loading');
                }
            });
        }
    })();
    </script>
    @yield('extra-script')
</body>

</html>