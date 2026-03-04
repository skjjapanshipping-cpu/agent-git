@extends('layouts.app')

@section('title')
    รายการสั่งซื้อ
@endsection

@section('extra-css')
    <style>
        /* ========================================
                       COMPLETE LAYOUT OVERRIDE - Fix Paper Dashboard
                       ======================================== */

        /* Global Overflow - Remove bottom scrollbar */
        html,
        body {
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100vw !important;
        }

        /* Wrapper - Flexbox layout */
        .wrapper {
            display: flex !important;
            flex-direction: row !important;
            min-height: 100vh;
            position: relative !important;
            width: 100vw !important;
            overflow-x: hidden !important;
        }

        /* Sidebar - Fixed left position */
        .sidebar-modern {
            position: fixed !important;
            top: 0 !important;
            left: 0 !important;
            width: 260px !important;
            height: 100vh !important;
            z-index: 1001 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-modern .sidebar-wrapper {
            flex: 1 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            position: relative !important;
            height: auto !important;
            padding-bottom: 20px !important;
            width: 100% !important;
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .sidebar-modern .sidebar-wrapper::-webkit-scrollbar {
            display: none;
        }

        /* Main Panel */
        .main-panel {
            margin-left: 260px !important;
            width: calc(100% - 260px) !important;
            background: #f1f5f9 !important;
            min-height: 100vh !important;
            padding: 0 !important;
            position: relative !important;
            float: none !important;
            flex: 1 !important;
            overflow-x: hidden !important;
        }

        /* Hide Panel Headers */
        .panel-header,
        .panel-header-lg,
        .panel-header-sm {
            display: none !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            position: absolute !important;
            top: -9999px !important;
        }

        .main-panel>.content {
            display: none !important;
        }

        .main-panel::before,
        .main-panel::after {
            display: none !important;
            content: none !important;
        }

        /* Dashboard Content */
        .dashboard-content {
            padding: 30px;
            position: relative;
            z-index: 100;
            background: #f1f5f9;
            min-height: 100vh;
        }

        .table td,
        .table th {
            vertical-align: middle;
            white-space: nowrap;
        }

        /* Link Cell truncating */
        .link-cell {
            max-width: 150px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: inline-block;
            vertical-align: middle;
            color: #1D8AC9;
            text-decoration: none;
        }

        .link-cell:hover {
            text-decoration: underline;
        }

        /* ==========================================
                       CONTROLS MODERNIZATION
                       ========================================== */
        .dataTables_length label,
        .dataTables_filter label {
            font-size: 0 !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center;
            width: 100%;
        }

        .dataTables_length select,
        .dataTables_filter input {
            font-size: 14px !important;
            height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0 15px !important;
            background-color: white !important;
            color: #475569 !important;
            width: 100% !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
            transition: all 0.2s;
        }

        .dataTables_filter input:focus,
        .dataTables_length select:focus {
            border-color: #1D8AC9 !important;
            box-shadow: 0 0 0 3px rgba(29, 138, 201, 0.1) !important;
            outline: none !important;
        }

        .dataTables_filter input {
            padding-left: 38px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
        }

        div.dataTables_wrapper div.dataTables_filter,
        div.dataTables_wrapper div.dataTables_length {
            text-align: left;
            margin: 0;
            padding: 0;
            width: 100%;
        }

        /* Controls Container - SAME AS SHIPPING VIEW */
        .controls-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            /* Show(1) Search(2) */
            gap: 10px;
            align-items: center;
            width: 100%;
            background: white;
            padding: 15px;
            border-bottom: 1px solid #edf2f9;
        }

        .control-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .control-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }

        /* Badges */

        /* Utility - No Wrap */
        .whitespace-nowrap {
            white-space: nowrap !important;
        }

        /* Badges - Force Single Line */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 30px;
            font-size: 0.75rem;
            font-weight: 700;
            white-space: nowrap !important; /* Force Single Line */
        }

        .status-badge::before {
            content: '';
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-success {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }

        .status-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        .status-info {
            background: rgba(29, 138, 201, 0.1);
            color: #1D8AC9;
        }

        .status-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* Pay status badges */
        .pay-unpaid {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .pay-paid {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .pay-waiting-ship {
            background: rgba(29, 138, 201, 0.1);
            color: #1D8AC9;
        }
        .pay-cancelled {
            background: rgba(30, 30, 30, 0.08);
            color: #1e1e1e;
        }
        .pay-waiting-transfer {
            background: rgba(245, 158, 11, 0.1);
            color: #f59e0b;
        }

        /* Shipping status badges (match My Shipping colors) */
        .status-shipping {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }
        .status-arrived {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
        }
        .status-completed {
            background: rgba(236, 72, 153, 0.1);
            color: #ec4899;
        }
        .status-pending {
            background: rgba(148, 163, 184, 0.1);
            color: #94a3b8;
        }

        /* Images */
        .table-img {
            width: 45px;
            height: 45px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid white;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            transition: transform 0.2s;
        }

        .table-img:hover {
            transform: scale(1.15) rotate(2deg);
        }

        /* Modern Page Header */
        .modern-page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
            background: transparent !important;
            box-shadow: none !important;
        }

        .modern-page-title {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .modern-page-title-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #1D8AC9, #0f4c75);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.3rem;
            box-shadow: 0 5px 15px rgba(29, 138, 201, 0.3);
        }

        .modern-page-title h1 {
            font-size: 1.8rem;
            color: #0f172a;
            font-weight: 700;
            margin: 0;
        }

        .modern-page-title p {
            color: #64748b;
            font-size: 0.95rem;
            margin: 3px 0 0 0;
        }

        /* Card Modern */
        .card-modern {
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.02);
            overflow: hidden;
            margin-bottom: 30px;
        }

        /* Table Modern */
        .table-modern {
            width: 100%;
            margin-bottom: 0;
            color: #1a1a2e;
        }

        .table-modern thead th {
            background-color: #fcfcfd;
            color: #95aac9;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .table-modern tbody td {
            padding: 15px 20px;
            vertical-align: middle;
            border-top: 1px solid #edf2f9;
            font-size: 0.9rem;
        }

        .table-modern tbody tr:hover td {
            background-color: #f8f9fa;
        }

        /* Gallery Overlay */
        .gallery-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 99999;
            display: none;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .gallery-overlay.active {
            display: flex;
            opacity: 1;
        }

        .gallery-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-img {
            max-width: 90vw;
            max-height: 85vh;
            border-radius: 8px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.6);
        }

        .gallery-close,
        .gallery-nav {
            position: absolute;
            color: white;
            cursor: pointer;
            z-index: 100000;
        }

        .gallery-close {
            top: 20px;
            right: 30px;
            font-size: 30px;
        }

        .gallery-nav {
            top: 50%;
            transform: translateY(-50%);
            font-size: 40px;
            background: rgba(255, 255, 255, 0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gallery-prev {
            left: 40px;
        }

        .gallery-next {
            right: 40px;
        }

        .gallery-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0, 0, 0, 0.6);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }

        /* Mobile */
        .mobile-nav-toggle {
            display: none;
        }

        .sidebar-overlay {
            display: none;
        }

        /* Export Button */
        .btn-export-green {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            color: white !important;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.25);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-export-green:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(25, 135, 84, 0.35);
            background: linear-gradient(135deg, #28a745 0%, #198754 100%);
        }

        .btn-export-green i {
            font-size: 1.1rem;
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar-modern {
                transform: translateX(-260px);
                box-shadow: none;
            }

            .sidebar-modern.show {
                transform: translateX(0);
                box-shadow: 0 0 50px rgba(0, 0, 0, 0.5);
            }

            .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
                background: white !important;
            }

            .modern-page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                padding-top: 60px;
                margin-bottom: 20px;
            }

            .modern-page-title h1 {
                font-size: 1.5rem;
            }

            /* Bigger Icons for Mobile Sidebar */
            .sidebar-modern .nav li a i {
                font-size: 1.8rem !important;
                /* Larger Icon */
                width: 40px !important;
                text-align: center;
            }

            .sidebar-modern .nav li a {
                padding: 15px 20px !important;
                /* Larger Drop Zone */
            }

            /* Hide Sidebar Logo/Header on Mobile if requested */
            .sidebar-modern .sidebar-header {
                display: none !important;
            }

            /* Hide Logout on Top if it exists (Sidebar Logout separate) */
            /* If user meant sidebar logout: */
            .sidebar-logout {
                /* display: none !important;  -- Uncomment if user wants logout gone too */
            }

            /* Controls Layout - MOBILE ONE LINE */
            .controls-container {
                display: flex !important;
                flex-direction: row !important;
                /* Force row */
                gap: 5px !important;
                padding: 10px 0 !important;
                align-items: center;
                background: transparent;
                border: none;
                flex-wrap: nowrap !important;
            }

            /* Adjust widths for one line */
            .control-group {
                width: auto;
            }

            #length-container {
                flex: 2;
                min-width: 50px;
            }

            #filter-container {
                flex: 4;
            }

            .dataTables_length select,
            .dataTables_filter input {
                padding: 0 5px !important;
                font-size: 13px !important;
                height: 40px !important;
                background-position: 8px center !important;
            }

            .dataTables_filter input {
                padding-left: 28px !important;
            }

            /* Show Arrow Fix */
            .dataTables_length select {
                padding-right: 20px !important;
                padding-left: 5px !important;
                text-align: center;
                text-align-last: center;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
                background-repeat: no-repeat !important;
                background-position: right 2px center !important;
                background-size: 14px !important;
            }

            .mobile-nav-toggle {
                display: flex !important;
                position: fixed;
                top: 15px;
                left: 20px;
                z-index: 1030;
                background: white;
                padding: 10px;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
                border: none;
                color: #1D8AC9;
                font-size: 1.2rem;
                width: 45px;
                height: 45px;
                align-items: center;
                justify-content: center;
            }

            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
                z-index: 1000;
            }

            .sidebar-overlay.show {
                display: block;
            }

            .dashboard-content {
                padding: 15px;
            }

            .table-responsive {
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
                margin-bottom: 20px;
                background: white;
            }

            /* Sticky First Column */
            table.dataTable thead th:first-child,
            table.dataTable tbody td:first-child {
                position: sticky;
                left: 0;
                z-index: 10;
                background-color: white;
                border-right: 1px solid #e9ecef;
                box-shadow: 2px 0 5px rgba(0, 0, 0, 0.05);
            }

            table.dataTable thead th:first-child {
                z-index: 20;
                background-color: #fcfcfd;
            }
        }

        .sidebar-logout {
            padding: 20px;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            background: inherit;
            flex-shrink: 0;
            display: flex;
            justify-content: center;
        }

        .sidebar-logout .logout-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 20px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s;
            width: 100%;
            justify-content: center;
        }

        .sidebar-logout .logout-link:hover {
            box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
        }

        /* ==========================================
           MOBILE RESPONSIVE STYLES
           ========================================== */
        @media (max-width: 992px) {
            /* Full sidebar on mobile when opened */
            .sidebar-modern {
                width: 260px;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }

            .sidebar-modern.show {
                transform: translateX(0);
            }

            /* User info - SHOW and make larger */
            .sidebar-modern .user-info {
                display: block !important;
                background: rgba(255, 255, 255, 0.1);
                padding: 15px;
                border-radius: 12px;
                margin-top: 15px;
            }

            .sidebar-modern .user-name {
                font-size: 16px !important;
                font-weight: 700 !important;
                color: #fff !important;
                margin-bottom: 5px;
            }

            .sidebar-modern .user-email {
                font-size: 13px !important;
                opacity: 0.85 !important;
                color: rgba(255, 255, 255, 0.8) !important;
                word-break: break-word;
            }

            /* Menu items - SHOW text and make larger */
            .sidebar-modern .nav li a {
                padding: 16px 20px !important;
                font-size: 15px !important;
                min-height: 52px;
            }

            .sidebar-modern .nav li a span {
                display: inline !important; /* SHOW TEXT */
                font-weight: 500;
            }

            .sidebar-modern .nav li a i {
                font-size: 20px !important;
                min-width: 24px;
            }

            /* Main panel - full width on mobile */
            .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
            }
        }

        @media (max-width: 768px) {
            /* Sidebar - show when toggled */
            .sidebar-modern {
                display: flex !important;
                flex-direction: column;
                width: 280px;
                transform: translateX(-100%);
                box-shadow: 0 0 20px rgba(0, 0, 0, 0.3);
                z-index: 1001 !important;
            }

            .sidebar-modern.show {
                transform: translateX(0);
            }

            /* Enhanced User Info for mobile */
            .sidebar-modern .sidebar-header {
                padding: 25px 20px !important;
            }

            .sidebar-modern .sidebar-header img {
                height: 60px !important;
                margin-bottom: 15px;
            }

            .sidebar-modern .user-info {
                display: block !important;
                background: rgba(29, 138, 201, 0.2);
                padding: 16px;
                border-radius: 14px;
                margin-top: 15px;
                border-left: 4px solid #1D8AC9;
            }

            .sidebar-modern .user-name {
                font-size: 17px !important;
                font-weight: 700 !important;
                color: #fff !important;
                margin-bottom: 6px;
                letter-spacing: 0.5px;
            }

            .sidebar-modern .user-email {
                font-size: 14px !important;
                opacity: 0.9 !important;
                color: rgba(255, 255, 255, 0.85) !important;
            }

            /* Full menu with text */
            .sidebar-modern .nav li a {
                padding: 18px 22px !important;
                font-size: 16px !important;
                min-height: 54px;
                display: flex !important;
                align-items: center;
                gap: 14px;
            }

            .sidebar-modern .nav li a span {
                display: inline !important; /* SHOW TEXT */
                font-weight: 600 !important;
                flex: 1;
            }

            .sidebar-modern .nav li a i {
                font-size: 22px !important;
                min-width: 26px;
            }

            /* Active state more prominent */
            .sidebar-modern .nav li.active a {
                background: rgba(29, 138, 201, 0.25) !important;
                border-left: 4px solid #1D8AC9;
            }

            /* Logout button */
            .sidebar-logout .logout-link {
                padding: 18px 22px !important;
                font-size: 16px !important;
                min-height: 54px;
            }

            .sidebar-logout .logout-link span {
                display: inline !important;
                font-weight: 600 !important;
            }

            .sidebar-logout .logout-link i {
                font-size: 22px !important;
            }

            /* Main panel full width */
            .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
            }

            /* Dashboard content padding */
            .dashboard-content {
                padding: 15px !important;
                padding-top: 70px !important;
            }
        }
    </style>
@endsection

@section('content')
    <!-- Mobile Elements -->
    <button class="mobile-nav-toggle" id="sidebarToggle"><i class="fa fa-bars"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Gallery -->
    <div class="gallery-overlay" id="galleryOverlay">
        <div class="gallery-close" onclick="closeGallery()"><i class="fa fa-times"></i></div>
        <div class="gallery-nav gallery-prev" onclick="changeImage(-1)"><i class="fa fa-chevron-left"></i></div>
        <div class="gallery-content"><img src="" id="galleryImage" class="gallery-img"></div>
        <div class="gallery-nav gallery-next" onclick="changeImage(1)"><i class="fa fa-chevron-right"></i></div>
        <div class="gallery-counter" id="galleryCounter">1 / 1</div>
    </div>

    <div class="wrapper">
        @include('layouts.partials.side-bar')
        <div class="main-panel">
            <div class="dashboard-content">
                <!-- Page Header -->
                <div class="modern-page-header">
                    <div class="modern-page-title">
                        <div class="modern-page-title-icon"><i class="fa fa-shopping-cart"></i></div>
                        <div>
                            <h1>รายการสั่งซื้อ</h1>
                            <p style="margin:0;font-size:14px;color:#6c757d;font-weight:400;">จัดการและติดตามสถานะรายการสั่งซื้อสินค้า</p>
                        </div>
                    </div>
                </div>

                @if ($message = Session::get('success'))
                    <script>Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: '{{ $message }}', confirmButtonColor: '#1D8AC9', timer: 3000 });</script>
                @endif

                <!-- Card -->
                <div class="card-modern">
                    <div class="card-body p-0">
                        <!-- Controls -->
                        <div class="controls-container">
                            <div class="control-group" id="length-container">
                                <label class="control-label d-md-block d-none">SHOW:</label>
                            </div>
                            <div class="control-group" id="filter-container">
                                <label class="control-label d-md-block d-none">SEARCH:</label>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table-modern" id="dt-order-table">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>รูปภาพ</th>
                                        <th>สถานะ</th>
                                        <th>วันที่</th>
                                        <th class="d-none">รหัสลูกค้า</th>
                                        <th>ลิงค์</th>
                                        <th>จำนวน</th>
                                        <th>เงินเยน</th>
                                        <th>เรท</th>
                                        <th>เงินบาท</th>
                                        <th>เลขพัสดุ</th>
                                        <th>รอบปิดตู้</th>
                                        <th>สถานะขนส่ง</th>
                                        <th>หมายเหตุ</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.partials.footer')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var panelHeaders = document.querySelectorAll('.panel-header');
            panelHeaders.forEach(el => el.remove());

            var mainPanel = document.querySelector('.main-panel');
            if (mainPanel) { mainPanel.style.marginTop = '0'; mainPanel.style.paddingTop = '0'; }

            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');
            var sidebar = document.querySelector('.sidebar-modern');
            if (toggle && overlay && sidebar) {
                toggle.addEventListener('click', () => { sidebar.classList.add('show'); overlay.classList.add('show'); });
                overlay.addEventListener('click', () => { sidebar.classList.remove('show'); overlay.classList.remove('show'); });
            }
        });

        // Gallery Logic (array-based, slide support)
        var currentGalleryImages = [];
        var currentGalleryIndex = 0;

        function openGallery(images, index) {
            if (!images) return;
            // Support old single-URL call: openGallery('url')
            if (typeof images === 'string') { images = [images]; }
            if (images.length === 0) return;
            currentGalleryImages = images;
            currentGalleryIndex = index || 0;
            updateGalleryImage();
            document.getElementById('galleryOverlay').classList.add('active');
            document.body.style.overflow = 'hidden';
        }
        function showImage(url) { openGallery([url], 0); }
        function closeGallery() {
            document.getElementById('galleryOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }
        function changeImage(direction) {
            currentGalleryIndex += direction;
            if (currentGalleryIndex < 0) currentGalleryIndex = currentGalleryImages.length - 1;
            if (currentGalleryIndex >= currentGalleryImages.length) currentGalleryIndex = 0;
            updateGalleryImage();
        }
        function updateGalleryImage() {
            var img = document.getElementById('galleryImage');
            var counter = document.getElementById('galleryCounter');
            var prevBtn = document.querySelector('.gallery-prev');
            var nextBtn = document.querySelector('.gallery-next');
            img.src = currentGalleryImages[currentGalleryIndex];
            counter.textContent = (currentGalleryIndex + 1) + ' / ' + currentGalleryImages.length;
            if (currentGalleryImages.length <= 1) {
                prevBtn.style.display = 'none';
                nextBtn.style.display = 'none';
                counter.style.display = 'none';
            } else {
                prevBtn.style.display = 'flex';
                nextBtn.style.display = 'flex';
                counter.style.display = '';
            }
        }
        document.addEventListener('keydown', function (e) {
            if (!document.getElementById('galleryOverlay').classList.contains('active')) return;
            if (e.key === 'ArrowLeft') changeImage(-1);
            if (e.key === 'ArrowRight') changeImage(1);
            if (e.key === 'Escape') closeGallery();
        });
    </script>

    <!-- Gallery Slide + Touch/Swipe (standalone) -->
    <script>
    function openColumnGallery(col, imgEl) {
        try {
            var table = document.getElementById('dt-order-table');
            if (!table) { openGallery([imgEl.src], 0); return; }

            var colIndex = 1; // image column
            var rows = table.querySelectorAll('tbody tr');
            var images = [];
            var clickedIndex = 0;

            for (var r = 0; r < rows.length; r++) {
                var cells = rows[r].querySelectorAll('td');
                if (cells.length > colIndex) {
                    var img = cells[colIndex].querySelector('img');
                    if (img && img.src) {
                        if (img === imgEl) clickedIndex = images.length;
                        images.push(img.src);
                    }
                }
            }

            if (images.length > 0) {
                openGallery(images, clickedIndex);
            } else {
                openGallery([imgEl.src], 0);
            }
        } catch(e) {
            openGallery([imgEl.src], 0);
        }
    }

    // Touch/Swipe for gallery
    (function() {
        var ov = document.getElementById('galleryOverlay');
        var gi = document.getElementById('galleryImage');
        if (!ov || !gi) return;
        var sx=0, sy=0, dx=0, dy=0, sw=false;
        ov.addEventListener('touchstart', function(e) {
            if(e.touches.length!==1)return; sx=e.touches[0].clientX; sy=e.touches[0].clientY; dx=0; dy=0; sw=true; gi.style.transition='none';
        },{passive:true});
        ov.addEventListener('touchmove', function(e) {
            if(!sw||e.touches.length!==1)return; dx=e.touches[0].clientX-sx; dy=e.touches[0].clientY-sy;
            if(Math.abs(dx)>Math.abs(dy)){e.preventDefault(); var c=Math.max(-120,Math.min(120,dx)); gi.style.transform='translateX('+c+'px)'; gi.style.opacity=1-Math.abs(c)/300;}
        },{passive:false});
        ov.addEventListener('touchend', function(e) {
            if(!sw)return; sw=false; gi.style.transition='transform 0.3s, opacity 0.3s';
            if(Math.abs(dx)>50&&Math.abs(dx)>Math.abs(dy)){var dir=dx<0?1:-1; changeImage(dir);}
            gi.style.transform=''; gi.style.opacity='';
        },{passive:true});
        ov.addEventListener('click', function(e){if(e.target===ov)closeGallery();});
    })();
    </script>
@endsection

@section('extra-script')
    <script>
        function getNameFromDomain(urlData) {
            try {
                const url = new URL(urlData);
                let hostname = url.hostname.replace('www.', '').toLowerCase();

                var siteNames = {
                    'mercari': 'Mercari',
                    'amazon': 'Amazon',
                    'rakuten': 'Rakuten',
                    'yahoo': 'Yahoo',
                    'paypayfleamarket': 'PayPay',
                    'fril': 'Fril',
                    'suruga-ya': 'Suruga-ya',
                    'auctions.yahoo': 'Yahoo Auction',
                    'shopping.yahoo': 'Yahoo Shopping',
                    'store.disney': 'Disney Store',
                    'dior': 'Dior',
                    'uniqlo': 'Uniqlo',
                    'zozotown': 'ZOZOTOWN',
                };

                for (var key in siteNames) {
                    if (hostname.indexOf(key) !== -1) return siteNames[key];
                }

                hostname = hostname.replace(/\.com(\.[a-z]+)?$/, '');
                hostname = hostname.replace(/\.co\.jp$/, '');
                hostname = hostname.replace(/\.jp$/, '');
                hostname = hostname.replace(/\.net$/, '');
                hostname = hostname.replace(/\.org$/, '');
                hostname = hostname.replace(/\.co\.th$/, '');
                hostname = hostname.replace(/\.th$/, '');

                var parts = hostname.split('.');
                if (parts[0] === 'jp' && parts.length > 1) hostname = parts[1];
                else hostname = parts[parts.length - 1];

                return hostname.charAt(0).toUpperCase() + hostname.slice(1);
            } catch (e) { return '-'; }
        }

        $(function () {
            if ($.fn.DataTable.isDataTable('#dt-order-table')) { $('#dt-order-table').DataTable().destroy(); }

            var dataTable = $('#dt-order-table').DataTable({
                "pageLength": 50,
                "lengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
                "processing": true,
                "serverSide": true,
                "ordering": false,
                "searching": true,
                "searchCols": [
                    null, null, null, null, null, null, null, null, null, null, null, null, null, null
                ],
                "language": {
                    "processing": "กำลังโหลด...",
                    "lengthMenu": "_MENU_",
                    "search": "",
                    "searchPlaceholder": "Search..."
                },
                "ajax": {
                    "url": "{{ route('fetch.customerorderview') }}",
                    "type": "POST",
                    "data": function (d) { 
                        d._token = "{{ csrf_token() }}";
                        console.log('DataTables sending:', d);
                        // Disable column search, use only global search
                        d.columns.forEach(function(column) {
                            column.search.value = '';
                        });
                        return d;
                    }
                },
                "order": [[3, "desc"]],
                "initComplete": function (settings, json) {
                    var length = $('#dt-order-table_wrapper .dataTables_length');
                    var filter = $('#dt-order-table_wrapper .dataTables_filter');
                    filter.find('input').attr('placeholder', 'Search...');
                    length.detach().appendTo('#length-container');
                    filter.detach().appendTo('#filter-container');

                    $('.domain-name').each(function () {
                        $(this).text(getNameFromDomain($(this).data('url')));
                    });
                },
                "drawCallback": function (settings) {
                    $('.domain-name').each(function () {
                        $(this).text(getNameFromDomain($(this).data('url')));
                    });
                },
                "columns": [
                    {
                        "data": "row_number",
                        "className": "text-center",
                        "render": (d, t, r, m) => `<span style="font-weight:600;color:#64748b;">${m.row + m.settings._iDisplayStart + 1}</span>`
                    },
                    {
                        "data": "image",
                        "className": "text-center",
                        "render": function (data) {
                            if (!data || data === '-' || data.trim() === '') return '<span style="color:#cbd5e1;">-</span>';
                            // Extract src from <img> tag if controller returns HTML
                            var src = data;
                            if (data.includes('<img')) {
                                var match = data.match(/src=["']([^"']+)["']/);
                                if (match) src = match[1];
                                else return '<span style="color:#cbd5e1;">-</span>';
                            }
                            return '<img src="' + src + '" class="table-img" onclick="openColumnGallery(\'img\', this)" style="cursor:pointer">';
                        }
                    },
                    {
                        "data": "status_name",
                        "className": "text-center",
                        "render": function (data) {
                            let cls = 'status-info';
                            if (data && data.indexOf('ยังไม่ชำระ') !== -1) cls = 'pay-unpaid';
                            else if (data && data.indexOf('ชำระเงินแล้ว') !== -1) cls = 'pay-paid';
                            else if (data && data.indexOf('รอร้านแจ้ง') !== -1) cls = 'pay-waiting-ship';
                            else if (data && data.indexOf('ยกเลิก') !== -1) cls = 'pay-cancelled';
                            else if (data && data.indexOf('รอโอน') !== -1) cls = 'pay-waiting-transfer';
                            return `<span class="status-badge ${cls}">${data}</span>`;
                        }
                    },
                    { "data": "order_date_formatted", "className": "text-center whitespace-nowrap" },
                    { "data": "customerno", "visible": false },
                    {
                        "data": "link_display", 
                        "className": "text-center",
                        "render": function (data, type, row) {
                            // Extract URL from the HTML string if possible, or use row.link if available
                            // The controller returns a complex HTML string in link_display. 
                            // However, we can use the raw 'link' data if we had it, but we only have link_display.
                            // Actually, let's try to extract the URL from the data string (it has <a href="...">)
                            // OR better, we can see row.link is likely available in the full row object from controller
                            // checking controller... yes 'link_display' is constructed from $row->link.
                            // BUT DataTables 'data' param here is 'link_display'. 
                            // We can access row.link if we change data source or just parse it.
                            // Let's use a regex to extract href from the string, or just use the text if it's already a domain.
                            // Wait, the previous code was using `getNameFromDomain($(this).data('url'))` on drawCallback.
                            // We can just output a clean link here using the URL from the HTML.
                            
                            // A safer way since we might not have 'link' column directly exposed in 'columns' array unless we added it.
                            // The controller adds 'link_display'. It DOES NOT add 'link' as a separate column in the `make(true)` response unless it's in the model and not hidden.
                            // Let's assume we can extract it.
                            
                            let url = '-';
                            if (data && data.includes('href="')) {
                                let match = data.match(/href="([^"]*)"/);
                                if (match) url = match[1];
                            }
                            
                            if (url === '-') return '-';
                            
                            let shortName = getNameFromDomain(url);
                            return `<a href="${url}" target="_blank" style="color:#1D8AC9;text-decoration:none;font-weight:600;">${shortName}</a>`;
                        }
                    },
                    { "data": "quantity_formatted", "className": "text-center" },
                    { "data": "product_cost_yen_formatted", "className": "text-right whitespace-nowrap" },
                    { "data": "rateprice_formatted", "className": "text-center" },
                    { "data": "product_cost_baht_formatted", "className": "text-right font-weight-bold whitespace-nowrap" },
                    {
                        "data": "tracking_number_display",
                        "className": "text-center whitespace-nowrap",
                        "render": function (data) {
                            if (!data || data == '-') return '<span style="color:#cbd5e1;">-</span>';
                            // Replace any hyphens with non-breaking hyphens or just style it
                            return `<span style="font-weight:600;color:#000;white-space:nowrap;">${data}</span>`;
                        }
                    },
                    { "data": "cutoff_date_formatted", "className": "text-center whitespace-nowrap" },
                    {
                        "data": "shipping_status_name",
                        "className": "text-center",
                        "render": function (data) {
                            if (!data) return '-';
                            let cls = 'status-pending';
                            if (data.indexOf('ระหว่าง') !== -1 || data.indexOf('ขนส่ง') !== -1) cls = 'status-shipping';
                            else if (data.indexOf('ถึง') !== -1 || data.indexOf('Arrived') !== -1) cls = 'status-arrived';
                            else if (data.indexOf('สำเร็จ') !== -1 || data.indexOf('Complete') !== -1) cls = 'status-completed';
                            return `<span class="status-badge ${cls}">${data}</span>`;
                        }
                    },
                    { "data": "note", "className": "whitespace-nowrap" }
                ]
            });
        });
    </script>
@endsection