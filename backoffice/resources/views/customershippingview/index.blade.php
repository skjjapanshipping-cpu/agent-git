@extends('layouts.app')

@section('title')
    รายการสินค้าเข้าไทย
@endsection

@section('extra-css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ========================================
           COMPLETE LAYOUT OVERRIDE - Fix Paper Dashboard
           ======================================== */
        
        /* Global Overflow - Remove bottom scrollbar */
        html, body {
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
            z-index: 1001 !important; /* Higher than everything */
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        /* Flexbox wrapper for menu */
        .sidebar-modern .sidebar-wrapper {
            flex: 1 !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            position: relative !important;
            height: auto !important;
            padding-bottom: 20px !important;
            width: 100% !important;
        }

        /* Hide scrollbar ONLY for sidebar wrapper (clean look) */
        .sidebar-modern .sidebar-wrapper::-webkit-scrollbar {
            display: none;
        }
        .sidebar-modern .sidebar-wrapper {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
        
        /* Main Panel - Takes remaining space on right */
        .main-panel {
            margin-left: 260px !important;
            width: calc(100% - 260px) !important;
            background: #f1f5f9 !important;
            min-height: 100vh !important;
            padding: 0 !important;
            position: relative !important;
            float: none !important;
            flex: 1 !important;
            
            /* Ensure no horizontal scroll here */
            overflow-x: hidden !important;
        }

        /* CRITICAL: Hide ALL panel-header variants */
        .panel-header,
        .main-panel .panel-header,
        .main-panel > .panel-header,
        div.panel-header,
        .panel-header-lg,
        .panel-header-sm,
        .panel-header-tiny {
            display: none !important;
            height: 0 !important;
            max-height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            min-height: 0 !important;
            overflow: hidden !important;
            background: none !important;
            width: 0 !important;
            opacity: 0 !important;
            visibility: hidden !important;
            position: absolute !important;
            top: -9999px !important;
        }

        /* Hide paper-dashboard content wrapper and pseudo elements */
        .main-panel > .content {
            display: none !important;
        }

        /* Override pseudo elements */
        .main-panel::before,
        .main-panel::after {
            display: none !important;
            content: none !important;
            background: none !important;
        }

        /* Dashboard Content - Cover entire panel area */
        .dashboard-content {
            padding: 30px;
            position: relative;
            z-index: 100;
            background: #f1f5f9;
            min-height: 100vh;
        }

        /* Specific page overrides */
        .table td,
        .table th {
            vertical-align: middle;
        }
        
        /* ==========================================
           CONTROLS MODERNIZATION
           ========================================== */
           
        /* Hide DataTables Labels Text */
        .dataTables_length label,
        .dataTables_filter label {
            font-size: 0 !important; /* Hide text 'Search:' & 'Show entries' */
            margin: 0 !important;
            display: flex !important;
            align-items: center;
            width: 100%;
        }

        /* Modern Inputs */
        .dataTables_length select,
        .dataTables_filter input,
        #start_date {
            font-size: 14px !important;
            height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0 15px !important;
            background-color: white !important;
            color: #475569 !important;
            width: 100% !important; /* Expand to container */
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: none !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .dataTables_length select,
        .unified-select {
            font-size: 14px !important;
            height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0 15px !important;
            padding-right: 36px !important;
            background-color: white !important;
            color: #475569 !important;
            width: 100% !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 12px !important;
            cursor: pointer;
        }
        
        .dataTables_filter input:focus,
        .dataTables_length select:focus,
        #start_date:focus {
            border-color: #1D8AC9 !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            outline: none !important;
        }

        /* Special Styling for Search Input (Add Icon) */
        .dataTables_filter input {
            padding-left: 38px !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='8'%3E%3C/circle%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'%3E%3C/line%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: 12px center;
        }

        /* Remove default margins from DT elements */
        div.dataTables_wrapper div.dataTables_filter,
        div.dataTables_wrapper div.dataTables_length {
            text-align: left;
            margin: 0;
            padding: 0;
            width: 100%;
        }
        
        /* Container for controls */
        .controls-container {
            display: grid;
            grid-template-columns: 2fr 2fr 1fr 2fr; /* Date(2) Recipient(2) Show(1) Search(2) */
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
            min-height: 68px;
            gap: 5px;
        }
        
        .control-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            margin: 0;
        }

        .status-select-header {
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 2px 5px;
            font-size: 0.8rem;
            color: #495057;
        }

        /* ==========================================
           Delivery Type Badges
           ========================================== */
        .delivery-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
        }

        .delivery-badge i {
            font-size: 0.9rem;
        }

        .delivery-badge.pending { background: linear-gradient(135deg, #fef3c7, #fde68a); color: #d97706; }
        .delivery-badge.ems { background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; }
        .delivery-badge.kerry { background: linear-gradient(135deg, #fed7aa, #fdba74); color: #c2410c; }
        .delivery-badge.flash { background: linear-gradient(135deg, #fce7f3, #fbcfe8); color: #be185d; }
        .delivery-badge.jt { background: linear-gradient(135deg, #fde6e8, #fecaca); color: #dc2626; }
        .delivery-badge.self { background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #047857; }
        .delivery-badge.home { background: linear-gradient(135deg, #fde6e8, #fecaca); color: #E63946; }

        /* ==========================================
           Status Badges
           ========================================== */
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 14px;
            border-radius: 30px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .status-badge::before {
            content: '';
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: currentColor;
        }

        .status-badge.shipping { background: rgba(239, 68, 68, 0.1); color: #ef4444; }
        .status-badge.arrived { background: rgba(16, 185, 129, 0.1); color: #10b981; }
        .status-badge.received { background: rgba(236, 72, 153, 0.1); color: #ec4899; }

        /* ==========================================
           Table Styles
           ========================================== */
        /* Mini Slider in Table Cell */
        .mini-slider {
            position: relative;
            width: 55px;
            height: 55px;
            border-radius: 10px;
            overflow: hidden;
            cursor: pointer;
            background: #f1f5f9;
            flex-shrink: 0;
        }

        .mini-slider-track {
            display: flex;
            height: 100%;
            transition: transform 0.3s ease;
        }

        .mini-slider-track img {
            width: 55px;
            height: 55px;
            object-fit: cover;
            flex-shrink: 0;
            user-select: none;
            -webkit-user-drag: none;
        }

        .mini-slider-dots {
            position: absolute;
            bottom: 2px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 3px;
        }

        .mini-slider-dots .dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            transition: background 0.2s;
        }

        .mini-slider-dots .dot.active {
            background: white;
            box-shadow: 0 0 3px rgba(0,0,0,0.3);
        }

        .mini-slider-nav {
            position: absolute;
            top: 0;
            width: 50%;
            height: 100%;
            z-index: 2;
            cursor: pointer;
        }

        .mini-slider-nav.prev { left: 0; }
        .mini-slider-nav.next { right: 0; }

        /* Single image fallback (no slider needed) */
        .table-img {
            width: 55px;
            height: 55px;
            border-radius: 10px;
            object-fit: cover;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08);
            transition: transform 0.2s;
            border: 2px solid white;
            cursor: pointer;
        }

        .table-img-container {
            position: relative;
            display: inline-block;
            cursor: pointer;
        }

        .table-img:hover {
            transform: scale(1.15) rotate(2deg);
            box-shadow: 0 5px 12px rgba(0, 0, 0, 0.15);
        }

        .track-no { font-weight: 700; color: #1a1a2e; font-size: 0.9rem; }

        /* Action Button */
        .btn-table-action {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid transparent;
            background: #f8f9fa;
            color: #95aac9;
            transition: all 0.2s;
            font-size: 0.9rem;
            text-decoration: none;
        }

        .btn-table-action:hover {
            background: #1D8AC9;
            color: white;
            transform: rotate(15deg);
            box-shadow: 0 3px 8px rgba(29, 138, 201, 0.3);
        }

        /* Modern Buttons */
        .btn-modern {
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            cursor: pointer;
        }

        /* Enhanced Primary Button - More Modern */
        .btn-modern-primary {
            background: linear-gradient(135deg, #1D8AC9 0%, #1670a6 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(29, 138, 201, 0.3);
            border: 1px solid rgba(255,255,255,0.1);
            position: relative;
            overflow: hidden;
        }

        .btn-modern-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }

        .btn-modern-primary:hover::before {
            left: 100%;
        }

        .btn-modern-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(29, 138, 201, 0.4);
            color: white;
            background: linear-gradient(135deg, #209ad4 0%, #187bb5 100%);
        }
        
        /* Green Export Button - Modern */
        .btn-export-green {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            color: white;
            border: 1px solid rgba(255,255,255,0.1);
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.2);
            text-decoration: none;
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .btn-export-green::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: 0.5s;
        }
        
        .btn-export-green:hover::before {
            left: 100%;
        }
        
        .btn-export-green:hover {
            background: linear-gradient(135deg, #28a745 0%, #198754 100%);
            border-color: rgba(255,255,255,0.2);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(25, 135, 84, 0.35);
        }

        .btn-modern-outline {
            background: white;
            border: 1px solid #d2ddec;
            color: #6c757d;
        }

        .btn-modern-outline:hover {
            border-color: #1D8AC9;
            color: #1D8AC9;
            background: #f8fbfe;
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

        .card-modern .card-header {
            background: white;
            border-bottom: 1px solid #e9ecef;
            padding: 25px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            display: none; /* Hide as per request */
        }

        /* ==========================================
           RENAMED PAGE HEADER CLASSES (Fix Conflict)
           ========================================== */
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
            line-height: 1.2;
        }

        .modern-page-title p {
            color: #64748b;
            font-size: 0.95rem;
            margin: 3px 0 0 0;
        }

        .modern-header-actions {
            display: flex;
            gap: 15px;
        }
        
        /* Checkbox */
        .custom-checkbox {
            width: 20px;
            height: 20px;
            border-radius: 6px;
            border: 2px solid #cbd5e1;
            cursor: pointer;
            position: relative;
            appearance: none;
            transition: all 0.2s;
        }

        .custom-checkbox:checked {
            background: #1D8AC9;
            border-color: #1D8AC9;
        }

        .custom-checkbox:checked::after {
            content: '✔';
            color: white;
            font-size: 12px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        /* DataTables Fixes */
        div.dataTables_wrapper {
            display: flex;
            flex-wrap: wrap;
            flex-direction: column;
        }
        
        /* Reset specific order hacks as user wants items elsewhere */
        div.dataTables_wrapper div.dataTables_info {
            order: 4; /* Below table */
            padding: 10px 20px;
            width: 100%;
            font-size: 0.85rem;
            color: #64748b;
        }

        div.dataTables_wrapper div.dataTables_paginate {
            order: 5; /* Below info */
            margin: 0;
            white-space: nowrap;
            text-align: right;
            padding: 0 20px;
            width: 100%;
            overflow-x: auto; /* Prevent break */
        }

        div.dataTables_wrapper .table-responsive {
            order: 3; /* Middle */
            width: 100%;
        }
        
        .controls-container {
            order: 1; /* Top */
        }
        
        /* Remove ugly floats */
        .dataTables_length, .dataTables_filter, .dataTables_info, .dataTables_paginate {
            float: none !important;
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

        /* Sidebar Logout Button */
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
            background: rgba(230, 57, 70, 0.9);
            color: white;
            box-shadow: 0 4px 15px rgba(230, 57, 70, 0.4);
        }

        .sidebar-logout .logout-link i {
            font-size: 1.1rem;
        }

        /* Gallery Overlay */
        .gallery-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.9);
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
            box-shadow: 0 10px 40px rgba(0,0,0,0.6);
            transition: transform 0.3s, opacity 0.3s;
            user-select: none;
            -webkit-user-drag: none;
            touch-action: none;
            cursor: zoom-in;
        }

        .gallery-img.zoomed {
            cursor: grab;
            max-width: none;
            max-height: none;
        }

        .gallery-img.zoomed:active {
            cursor: grabbing;
        }

        .gallery-zoom-btn {
            position: absolute;
            bottom: 20px;
            right: 30px;
            color: white;
            background: rgba(0,0,0,0.6);
            border: none;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 14px;
            cursor: pointer;
            z-index: 100000;
            transition: background 0.2s;
        }

        .gallery-zoom-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .gallery-img.slide-left {
            transform: translateX(-60px);
            opacity: 0;
        }

        .gallery-img.slide-right {
            transform: translateX(60px);
            opacity: 0;
        }
        
        .gallery-close {
            position: absolute;
            top: 20px;
            right: 30px;
            color: white;
            font-size: 30px;
            cursor: pointer;
            z-index: 100000;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }
        
        .gallery-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 40px;
            cursor: pointer;
            background: rgba(255,255,255,0.1);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 100000;
        }
        
        .gallery-nav:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-50%) scale(1.1);
        }
        
        .gallery-prev { left: 40px; }
        .gallery-next { right: 40px; }
        
        .gallery-counter {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            color: white;
            background: rgba(0,0,0,0.6);
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
        }

        /* ==========================================
           MOBILE RESPONSIVE
           ========================================== */
        @media (max-width: 991px) {
            .sidebar-modern {
                transform: translateX(-260px);
                box-shadow: none;
            }
            
            .sidebar-modern.show {
                transform: translateX(0);
                box-shadow: 0 0 50px rgba(0,0,0,0.5);
            }
            
            .main-panel {
                margin-left: 0 !important;
                width: 100% !important;
                background: white !important; /* Cleaner mobile bg */
            }
            
            .modern-page-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
                padding-top: 60px; /* Space for hamburger */
                margin-bottom: 20px;
            }
            
            .modern-page-title h1 {
                font-size: 1.5rem;
                margin-bottom: 5px;
            }
            
            .modern-page-title p {
                font-size: 0.9rem;
                margin-bottom: 10px;
            }
            
            .modern-header-actions {
                width: 100%;
                flex-direction: column;
                gap: 10px;
            }
            
            /* Full width buttons */
            .modern-header-actions .btn {
                width: 100%;
                justify-content: center;
                margin: 0;
                padding: 12px;
                font-size: 1rem;
            }

            /* Controls Layout - MOBILE ONE LINE */
            .controls-container {
                display: flex !important;
                flex-direction: row !important; /* Force row */
                gap: 5px !important;
                padding: 10px 0 !important;
                align-items: center;
                background: transparent;
                border: none;
                flex-wrap: nowrap !important;
            }
            
            /* Adjust widths for one line */
            .control-group { width: auto; }
            
            #date-filter-group { flex: 4; }
            #recipient-filter-group { flex: 3; }
            #length-container { flex: 2; min-width: 50px; }
            #filter-container { flex: 4; }

            #recipient_filter {
                padding: 0 5px !important;
                font-size: 13px !important;
                height: 40px !important;
            }
            
            /* Smaller inputs on mobile to fit */
            .dataTables_length select,
            .dataTables_filter input,
            #start_date {
                padding: 0 5px !important;
                font-size: 13px !important;
                height: 40px !important;
                background-position: 8px center !important; /* Adjust Icon pos */
            }
            
            /* Show Arrow on select box but with proper spacing to prevent overlap */
            .dataTables_length select {
                padding-right: 20px !important; /* Space for arrow */
                padding-left: 5px !important;
                text-align: center;
                text-align-last: center;
                -webkit-appearance: none;
                -moz-appearance: none;
                appearance: none;
                /* Modern Chevron Down */
                background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
                background-repeat: no-repeat !important;
                background-position: right 2px center !important;
                background-size: 14px !important;
            }
            
            .dataTables_filter input {
                padding-left: 28px !important; /* Less padding for icon */
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
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                border: none;
                color: #1D8AC9;
                font-size: 1.2rem;
                width: 45px;
                height: 45px;
                align-items: center;
                justify-content: center;
                cursor: pointer;
            }
            
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0,0,0,0.5);
                backdrop-filter: blur(2px);
                z-index: 1000;
            }
            
            .sidebar-overlay.show {
                display: block;
            }

            .dashboard-content {
                padding: 15px;
            }

            /* Card Header hidden, using simplified controls */
            .card-modern {
                background: transparent;
                box-shadow: none;
                border: none;
            }
            
            .card-modern .card-body {
                background: transparent;
            }
            
            /* Sticky Checkbox Column */
            .table-responsive {
                border-radius: 12px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.05);
                margin-bottom: 20px;
                background: white;
            }
            
            table.dataTable {
                min-width: 800px;
            }
            
            table.dataTable thead th, 
            table.dataTable tbody td {
                white-space: nowrap;
            }
            
            table.dataTable thead th:first-child,
            table.dataTable tbody td:first-child {
                position: sticky;
                left: 0;
                z-index: 10;
                background-color: white;
                border-right: 1px solid #e9ecef;
                box-shadow: 2px 0 5px rgba(0,0,0,0.05);
            }
            
            table.dataTable thead th:first-child {
                z-index: 20;
                background-color: #fcfcfd;
            }
            
            /* Ensure hovered row background also applies to pinned column */
            table.table-modern tbody tr:hover td:first-child {
                background-color: #f8f9fa;
            }
        }

        .mobile-nav-toggle {
            display: none;
        }
        .sidebar-overlay {
            display: none;
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
            
            /* Toggle visibility */
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
            
            .sidebar-overlay.show {
                display: block !important;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                backdrop-filter: blur(2px);
                z-index: 1000;
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

        /* ========================================
           SUMMARY CARDS
           ======================================== */
        .summary-cards-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 18px;
        }
        .summary-card {
            background: #fff;
            border-radius: 14px;
            padding: 18px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.04);
            border: 1px solid #e8ecf1;
            display: flex;
            align-items: center;
            gap: 14px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .summary-card-icon {
            width: 48px; height: 48px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.2rem; flex-shrink: 0;
        }
        .summary-card-icon.blue { background: #eff6ff; color: #3b82f6; }
        .summary-card-icon.green { background: #f0fdf4; color: #22c55e; }
        .summary-card-icon.orange { background: #fff7ed; color: #f97316; }
        .summary-card-icon.purple { background: #faf5ff; color: #a855f7; }
        .summary-card-info .sc-label {
            font-size: 0.75rem; color: #94a3b8; font-weight: 600; margin-bottom: 2px;
        }
        .summary-card-info .sc-value {
            font-size: 1.4rem; font-weight: 800; color: #1e293b; line-height: 1.2;
        }
        .summary-card-info .sc-value small {
            font-size: 0.7rem; font-weight: 600; color: #94a3b8;
        }

        /* ========================================
           ACTION REQUIRED BANNER
           ======================================== */
        .action-banner {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 1.5px solid #f59e0b;
            border-radius: 12px;
            padding: 14px 20px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            box-shadow: 0 2px 10px rgba(245, 158, 11, 0.15);
        }
        .action-banner-content {
            display: flex; align-items: center; gap: 12px;
        }
        .action-banner-icon {
            width: 42px; height: 42px;
            background: #f59e0b; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 1.1rem; flex-shrink: 0;
        }
        .action-banner-text h4 { margin: 0; font-size: 0.92rem; font-weight: 700; color: #92400e; }
        .action-banner-text p { margin: 0; font-size: 0.8rem; color: #a16207; }
        .action-banner-btn {
            background: #f59e0b; color: white; border: none; padding: 8px 20px;
            border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer;
            text-decoration: none; white-space: nowrap; transition: background 0.2s;
        }
        .action-banner-btn:hover { background: #d97706; color: white; text-decoration: none; }

        /* ========================================
           QUICK VIEW MODAL
           ======================================== */
        .qv-overlay {
            display: none; position: fixed; inset: 0; z-index: 9998;
            background: rgba(0,0,0,0.5); backdrop-filter: blur(3px);
        }
        .qv-overlay.active { display: flex; align-items: center; justify-content: center; }
        .qv-modal {
            background: white; border-radius: 20px; width: 95%; max-width: 600px;
            max-height: 90vh; overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: qvSlideUp 0.25s ease-out;
        }
        @keyframes qvSlideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .qv-header {
            padding: 20px 24px 14px;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .qv-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .qv-close {
            background: none; border: none; cursor: pointer; padding: 8px;
            color: #94a3b8; font-size: 1.2rem;
        }
        .qv-close:hover { color: #64748b; }
        .qv-body { padding: 20px 24px; }
        .qv-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid #f8fafc;
        }
        .qv-row:last-child { border-bottom: none; }
        .qv-label { font-size: 0.82rem; color: #94a3b8; font-weight: 600; }
        .qv-value { font-size: 0.9rem; color: #1e293b; font-weight: 600; text-align: right; max-width: 60%; }
        .qv-images {
            display: flex; gap: 10px; flex-wrap: wrap; margin-top: 12px;
        }
        .qv-images img {
            width: 80px; height: 80px; object-fit: cover; border-radius: 10px;
            border: 1px solid #e2e8f0; cursor: pointer;
        }
        .qv-address-block {
            background: #f8fafc; border-radius: 10px; padding: 14px;
            margin-top: 8px; font-size: 0.85rem; color: #334155; line-height: 1.6;
        }
        .qv-status-badge {
            display: inline-block; padding: 4px 12px; border-radius: 20px;
            font-size: 0.78rem; font-weight: 700;
        }
        .qv-status-badge.shipping { background: #eff6ff; color: #2563eb; }
        .qv-status-badge.arrived { background: #ecfdf5; color: #059669; }
        .qv-status-badge.received { background: #fdf2f8; color: #db2777; }
        .qv-footer {
            padding: 14px 24px 20px;
            border-top: 1px solid #f1f5f9;
            display: flex; gap: 10px; justify-content: flex-end;
        }
        .qv-footer .btn-qv {
            padding: 10px 22px; border-radius: 10px; font-size: 0.88rem;
            font-weight: 600; cursor: pointer; border: none; transition: all 0.2s;
        }
        .qv-footer .btn-qv-close { background: #f1f5f9; color: #64748b; }
        .qv-footer .btn-qv-edit {
            background: linear-gradient(135deg, #1D8AC9, #0ea5e9); color: white;
            box-shadow: 0 4px 12px rgba(29,138,201,0.3);
        }

        /* ========================================
           INVOICE MODAL
           ======================================== */
        .inv-overlay {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);
        }
        .inv-overlay.active { display: flex; align-items: center; justify-content: center; }
        .inv-modal {
            background: white; border-radius: 20px; width: 95%; max-width: 480px;
            max-height: 92vh; overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: qvSlideUp 0.25s ease-out;
        }
        .inv-header {
            padding: 22px 24px 14px;
            border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .inv-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .inv-body { padding: 20px 24px; }
        .inv-summary-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 0; border-bottom: 1px solid #f1f5f9;
        }
        .inv-summary-row:last-child { border-bottom: none; }
        .inv-summary-row .inv-label { font-size: 0.88rem; color: #64748b; font-weight: 500; }
        .inv-summary-row .inv-value { font-size: 0.95rem; color: #1e293b; font-weight: 700; }
        .inv-total-row {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 16px; margin-top: 10px;
            background: linear-gradient(135deg, #eff6ff, #dbeafe);
            border-radius: 12px;
        }
        .inv-total-row .inv-label { font-size: 1rem; color: #1e40af; font-weight: 700; }
        .inv-total-row .inv-value { font-size: 1.3rem; color: #1e40af; font-weight: 800; }
        .inv-qr-section {
            text-align: center; margin-top: 20px; padding: 20px;
            background: #f8fafc; border-radius: 14px; border: 1px solid #e2e8f0;
        }
        .inv-qr-section img {
            width: 200px; height: 200px; border-radius: 12px;
            border: 2px solid #e2e8f0; background: white; padding: 8px;
        }
        .inv-qr-label {
            margin-top: 12px; font-size: 0.82rem; color: #64748b; font-weight: 600;
        }
        .inv-qr-amount {
            font-size: 1.4rem; font-weight: 800; color: #059669; margin-top: 4px;
        }
        .inv-bank-info {
            margin-top: 16px; padding: 14px; background: #fffbeb;
            border-radius: 10px; border: 1px solid #fde68a;
            font-size: 0.82rem; color: #92400e; line-height: 1.7;
        }
        .inv-footer {
            padding: 14px 24px 20px; border-top: 1px solid #f1f5f9;
            display: flex; gap: 10px; justify-content: center;
        }
        .inv-footer .btn-inv {
            padding: 10px 28px; border-radius: 10px; font-size: 0.88rem;
            font-weight: 600; cursor: pointer; border: none;
        }
        .inv-footer .btn-inv-close { background: #f1f5f9; color: #64748b; }
        .inv-qr-loading {
            display: flex; align-items: center; justify-content: center;
            height: 200px; color: #94a3b8; font-size: 0.9rem;
        }
        .btn-invoice-trigger {
            background: linear-gradient(135deg, #059669, #10b981);
            color: white; border: none; padding: 8px 18px; border-radius: 8px;
            font-weight: 700; font-size: 0.85rem; cursor: pointer;
            box-shadow: 0 3px 10px rgba(5, 150, 105, 0.3);
            transition: all 0.2s; white-space: nowrap;
        }
        .btn-invoice-trigger:hover { background: linear-gradient(135deg, #047857, #059669); transform: translateY(-1px); }

        /* ========================================
           ADDRESS BOOK MODAL
           ======================================== */
        .ab-overlay {
            display: none; position: fixed; inset: 0; z-index: 9999;
            background: rgba(0,0,0,0.55); backdrop-filter: blur(4px);
        }
        .ab-overlay.active { display: flex; align-items: center; justify-content: center; }
        .ab-modal {
            background: white; border-radius: 20px; width: 95%; max-width: 560px;
            max-height: 92vh; overflow-y: auto;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
            animation: qvSlideUp 0.25s ease-out;
        }
        .ab-header {
            padding: 22px 24px 14px; border-bottom: 1px solid #f1f5f9;
            display: flex; align-items: center; justify-content: space-between;
        }
        .ab-header h3 { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .ab-body { padding: 16px 24px; }
        .ab-list { list-style: none; padding: 0; margin: 0; }
        .ab-item {
            display: flex; align-items: flex-start; justify-content: space-between;
            padding: 14px 16px; margin-bottom: 10px;
            background: #f8fafc; border-radius: 12px; border: 1.5px solid #e2e8f0;
            transition: all 0.2s; cursor: pointer; position: relative;
        }
        .ab-item:hover { border-color: #1D8AC9; background: #f0f9ff; }
        .ab-item.ab-default { border-color: #10b981; background: #f0fdf4; }
        .ab-item-info { flex: 1; min-width: 0; }
        .ab-item-label {
            font-size: 0.82rem; font-weight: 700; color: #1D8AC9; margin-bottom: 2px;
            display: flex; align-items: center; gap: 6px;
        }
        .ab-item-label .ab-badge-default {
            background: #10b981; color: white; font-size: 0.68rem; padding: 1px 6px;
            border-radius: 4px; font-weight: 600;
        }
        .ab-item-name { font-size: 0.92rem; font-weight: 600; color: #1e293b; }
        .ab-item-addr { font-size: 0.82rem; color: #64748b; margin-top: 2px; line-height: 1.5; }
        .ab-item-actions {
            display: flex; gap: 4px; flex-shrink: 0; margin-left: 10px;
        }
        .ab-item-actions button {
            width: 30px; height: 30px; border-radius: 8px; border: none;
            cursor: pointer; font-size: 0.78rem; display: flex; align-items: center; justify-content: center;
        }
        .ab-btn-edit { background: #eff6ff; color: #1D8AC9; }
        .ab-btn-del { background: #fef2f2; color: #ef4444; }
        .ab-btn-star { background: #f0fdf4; color: #10b981; }
        .ab-empty {
            text-align: center; padding: 30px 16px; color: #94a3b8; font-size: 0.9rem;
        }
        .ab-empty i { font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.5; }
        .ab-footer {
            padding: 14px 24px 20px; border-top: 1px solid #f1f5f9;
            display: flex; gap: 10px; justify-content: space-between;
        }
        .btn-ab { padding: 10px 20px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; }
        .btn-ab-add { background: linear-gradient(135deg, #1D8AC9, #0ea5e9); color: white; }
        .btn-ab-close { background: #f1f5f9; color: #64748b; }
        .btn-ab-trigger {
            background: linear-gradient(135deg, #8b5cf6, #a78bfa);
            color: white; border: none; padding: 8px 18px; border-radius: 8px;
            font-weight: 700; font-size: 0.85rem; cursor: pointer;
            box-shadow: 0 3px 10px rgba(139, 92, 246, 0.3);
            transition: all 0.2s; white-space: nowrap;
        }
        .btn-ab-trigger:hover { background: linear-gradient(135deg, #7c3aed, #8b5cf6); transform: translateY(-1px); }
        /* AB Form (inline in modal) */
        .ab-form { display: none; padding: 16px; background: #f8fafc; border-radius: 12px; margin-bottom: 12px; border: 1.5px solid #e2e8f0; }
        .ab-form.active { display: block; }
        .ab-form-title { font-size: 0.88rem; font-weight: 700; color: #1e293b; margin-bottom: 12px; }
        .ab-form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .ab-form-grid .ab-fg { }
        .ab-form-grid .ab-fg.full { grid-column: 1 / -1; }
        .ab-form-grid .ab-fg label { display: block; font-size: 0.78rem; font-weight: 600; color: #64748b; margin-bottom: 3px; }
        .ab-form-grid .ab-fg input {
            width: 100%; padding: 8px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px;
            font-size: 0.85rem; outline: none; transition: border-color 0.2s;
        }
        .ab-form-grid .ab-fg input:focus { border-color: #1D8AC9; }
        .ab-form-actions { display: flex; gap: 8px; margin-top: 12px; justify-content: flex-end; }
        .ab-form-actions button { padding: 8px 16px; border-radius: 8px; font-size: 0.82rem; font-weight: 600; cursor: pointer; border: none; }
        .ab-form-save { background: #1D8AC9; color: white; }
        .ab-form-cancel { background: #f1f5f9; color: #64748b; }

        @media (max-width: 768px) {
            .summary-cards-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .summary-card { padding: 14px 16px; }
            .summary-card-icon { width: 40px; height: 40px; font-size: 1rem; }
            .summary-card-info .sc-value { font-size: 1.1rem; }
            .action-banner { flex-direction: column; align-items: flex-start; }
            .qv-modal { width: 98%; max-width: none; border-radius: 16px; }
            .inv-modal { width: 98%; max-width: none; border-radius: 16px; }
            .ab-modal { width: 98%; max-width: none; border-radius: 16px; }
            .ab-form-grid { grid-template-columns: 1fr; }
        }
    </style>
@endsection

@section('content')
    <!-- Mobile Elements -->
    <button class="mobile-nav-toggle" id="sidebarToggle">
        <i class="fa fa-bars"></i>
    </button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>
    
    <!-- Gallery Overlay -->
    <div class="gallery-overlay" id="galleryOverlay">
        <div class="gallery-close" onclick="closeGallery()"><i class="fa fa-times"></i></div>
        <div class="gallery-nav gallery-prev" onclick="changeImage(-1)"><i class="fa fa-chevron-left"></i></div>
        <div class="gallery-content">
            <img src="" id="galleryImage" class="gallery-img">
        </div>
        <div class="gallery-nav gallery-next" onclick="changeImage(1)"><i class="fa fa-chevron-right"></i></div>
        <div class="gallery-counter" id="galleryCounter">1 / 1</div>
        <button class="gallery-zoom-btn" id="galleryZoomBtn" onclick="toggleZoom()"><i class="fa fa-search-plus"></i> ซูม</button>
    </div>

    <div class="wrapper">
        @include('layouts.partials.side-bar')
        <div class="main-panel">
            <div class="dashboard-content">
                <!-- Page Header with Modern Classes -->
                <div class="modern-page-header">
                    <div class="modern-page-title">
                        <div class="modern-page-title-icon">
                            <i class="fa fa-cubes"></i>
                        </div>
                        <div>
                            <h1>รายการสินค้าเข้าไทย</h1>
                            <p>จัดการและติดตามสถานะการจัดส่งสินค้าของคุณ</p>
                        </div>
                    </div>
                    <div class="modern-header-actions">
                        <a href="{{url('customershippingsexport2')}}" id="data-export" class="btn btn-export-green">
                            <i class="fa fa-file-excel-o"></i> Export Excel
                        </a>
                        <!-- Form Hidden, Button Outside -->
                        <form method="POST" action="{{ route('update-delivery-type') }}" id="updateForm" style="display:none;">
                            @csrf
                            <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                        </form>
                        
                        <button type="button" id="updateSelected" class="btn btn-modern btn-modern-primary" onclick="checkAndUpdateSelection()">
                            <i class="fa fa-check-circle"></i> เลือกจัดส่งที่อยู่ปัจจุบัน
                        </button>
                        <button type="button" class="btn btn-modern btn-modern-accent" onclick="openBatchRecipientModal()">
                            <i class="fa fa-users"></i> กำหนดผู้รับ
                        </button>
                        <button type="button" class="btn-invoice-trigger" onclick="openInvoiceModal()">
                            <i class="fa fa-qrcode"></i> ชำระเงิน
                        </button>
                        <button type="button" class="btn-ab-trigger" onclick="openAddressBook()">
                            <i class="fa fa-address-book"></i> สมุดที่อยู่
                        </button>
                    </div>
                </div>

                <!-- Success Message -->
                @if ($message = Session::get('success'))
                    <script>
                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: '{{ $message }}',
                            confirmButtonColor: '#1D8AC9',
                            timer: 3000
                        });
                    </script>
                @endif

                <!-- LINE Connect Banner -->
                @php
                    $hasLine = \App\MyAuthProvider::where('userid', Auth::id())->where('provider', 'line')->exists();
                @endphp
                @if(!$hasLine)
                <div style="background: linear-gradient(135deg, #06C755 0%, #04a847 100%); border-radius: 12px; padding: 16px 20px; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px; box-shadow: 0 2px 12px rgba(6, 199, 85, 0.3);">
                    <div style="display: flex; align-items: center; gap: 12px; color: white;">
                        <i class="fa fa-commenting" style="font-size: 24px;"></i>
                        <div>
                            <div style="font-weight: 700; font-size: 15px;">เชื่อมต่อ LINE เพื่อรับการแจ้งเตือน</div>
                            <div style="font-size: 13px; opacity: 0.9;">รับแจ้งเตือนเมื่อสินค้าของคุณเข้าระบบผ่าน LINE</div>
                        </div>
                    </div>
                    <a href="/skjtrack/auth/line" style="background: white; color: #06C755; padding: 8px 20px; border-radius: 8px; font-weight: 700; font-size: 14px; text-decoration: none; white-space: nowrap;">
                        <i class="fa fa-link"></i> เชื่อมต่อ LINE
                    </a>
                </div>
                @endif

                <!-- Summary Cards (dynamic - updated on each DataTable load) -->
                <div class="summary-cards-grid">
                    <div class="summary-card">
                        <div class="summary-card-icon blue"><i class="fa fa-cube"></i></div>
                        <div class="summary-card-info">
                            <div class="sc-label" id="sc-round-label">รอบปิดตู้</div>
                            <div class="sc-value"><span id="sc-total">-</span> <small>รายการ</small></div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon green"><i class="fa fa-money"></i></div>
                        <div class="summary-card-info">
                            <div class="sc-label">ค่านำเข้ารวม</div>
                            <div class="sc-value"><span id="sc-import-cost">-</span> <small>฿</small></div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon purple"><i class="fa fa-balance-scale"></i></div>
                        <div class="summary-card-info">
                            <div class="sc-label">น้ำหนักรวม</div>
                            <div class="sc-value"><span id="sc-weight">-</span> <small>kg</small></div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon orange"><i class="fa fa-calculator"></i></div>
                        <div class="summary-card-info">
                            <div class="sc-label">ค่า COD + ค่านำเข้า</div>
                            <div class="sc-value"><span id="sc-price-total">-</span> <small>฿</small></div>
                        </div>
                    </div>
                </div>

                <!-- Quick View Modal -->
                <div class="qv-overlay" id="qvOverlay">
                    <div class="qv-modal">
                        <div class="qv-header">
                            <h3><i class="fa fa-cube" style="color:#1D8AC9;margin-right:8px;"></i>รายละเอียดพัสดุ</h3>
                            <button class="qv-close" onclick="closeQuickView()"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="qv-body" id="qvBody">
                            <!-- Filled by JS -->
                        </div>
                        <div class="qv-footer">
                            <button class="btn-qv btn-qv-close" onclick="closeQuickView()">ปิด</button>
                            <a href="#" class="btn-qv btn-qv-edit" id="qvEditLink"><i class="fa fa-pencil"></i> แก้ไขที่อยู่</a>
                        </div>
                    </div>
                </div>

                <!-- Invoice Summary + QR PromptPay Modal -->
                <div class="inv-overlay" id="invOverlay">
                    <div class="inv-modal">
                        <div class="inv-header">
                            <h3><i class="fa fa-file-text-o" style="color:#059669;margin-right:8px;"></i>สรุปยอดชำระ</h3>
                            <button class="qv-close" onclick="closeInvoiceModal()"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="inv-body">
                            <div style="font-size:0.82rem;color:#94a3b8;margin-bottom:12px;font-weight:600;" id="inv-round-label">รอบปิดตู้</div>
                            <div class="inv-summary-row">
                                <span class="inv-label">จำนวนรายการ</span>
                                <span class="inv-value" id="inv-total-items">-</span>
                            </div>
                            <div class="inv-summary-row">
                                <span class="inv-label">ค่านำเข้า</span>
                                <span class="inv-value" id="inv-import-cost">-</span>
                            </div>
                            <div class="inv-summary-row">
                                <span class="inv-label">ค่า COD</span>
                                <span class="inv-value" id="inv-cod-cost">-</span>
                            </div>
                            <div class="inv-total-row">
                                <span class="inv-label">ยอดรวมทั้งหมด</span>
                                <span class="inv-value" id="inv-price-total">-</span>
                            </div>

                            <div class="inv-qr-section" id="inv-qr-section">
                                <div class="inv-qr-loading" id="inv-qr-loading">
                                    <i class="fa fa-spinner fa-spin" style="margin-right:8px;"></i> กำลังสร้าง QR Code...
                                </div>
                                <img src="" id="inv-qr-img" style="display:none;" alt="PromptPay QR">
                                <div class="inv-qr-label">สแกนเพื่อชำระผ่าน PromptPay</div>
                                <div class="inv-qr-amount" id="inv-qr-amount">฿ -</div>
                            </div>

                            <div class="inv-bank-info">
                                <strong><i class="fa fa-university"></i> ข้อมูลการโอนเงิน</strong><br>
                                PromptPay: <strong>1-1020-01570-11-0</strong><br>
                                ชื่อบัญชี: <strong>อนุวัตร สักกระจ่าง</strong><br>
                                <span style="font-size:0.78rem;color:#b45309;">* กรุณาส่งสลิปหลังโอนเงินผ่าน LINE</span>
                            </div>
                        </div>
                        <div class="inv-footer">
                            <button class="btn-inv btn-inv-close" onclick="closeInvoiceModal()">ปิด</button>
                        </div>
                    </div>
                </div>

                <!-- Address Book Modal -->
                <div class="ab-overlay" id="abOverlay">
                    <div class="ab-modal">
                        <div class="ab-header">
                            <h3><i class="fa fa-address-book" style="color:#8b5cf6;margin-right:8px;"></i>สมุดที่อยู่</h3>
                            <button class="qv-close" onclick="closeAddressBook()"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="ab-body">
                            <!-- Add/Edit Form (hidden by default) -->
                            <div class="ab-form" id="abForm">
                                <div class="ab-form-title" id="abFormTitle">เพิ่มที่อยู่ใหม่</div>
                                <input type="hidden" id="ab-edit-id" value="">
                                <div class="ab-form-grid">
                                    <div class="ab-fg">
                                        <label>ชื่อที่อยู่</label>
                                        <input type="text" id="ab-label" placeholder="เช่น บ้าน, ออฟฟิศ">
                                    </div>
                                    <div class="ab-fg">
                                        <label>ชื่อ-นามสกุล *</label>
                                        <input type="text" id="ab-fullname" placeholder="ชื่อผู้รับ" required>
                                    </div>
                                    <div class="ab-fg">
                                        <label>เบอร์โทร</label>
                                        <input type="text" id="ab-mobile" placeholder="0xx-xxx-xxxx">
                                    </div>
                                    <div class="ab-fg full">
                                        <label>ที่อยู่</label>
                                        <input type="text" id="ab-address" placeholder="บ้านเลขที่ ซอย ถนน">
                                    </div>
                                    <div class="ab-fg">
                                        <label>แขวง/ตำบล</label>
                                        <input type="text" id="ab-subdistrict" placeholder="ตำบล">
                                    </div>
                                    <div class="ab-fg">
                                        <label>เขต/อำเภอ</label>
                                        <input type="text" id="ab-district" placeholder="อำเภอ">
                                    </div>
                                    <div class="ab-fg">
                                        <label>จังหวัด</label>
                                        <input type="text" id="ab-province" placeholder="จังหวัด">
                                    </div>
                                    <div class="ab-fg">
                                        <label>รหัสไปรษณีย์</label>
                                        <input type="text" id="ab-postcode" placeholder="xxxxx">
                                    </div>
                                </div>
                                <div class="ab-form-actions">
                                    <button class="ab-form-cancel" onclick="abCancelForm()">ยกเลิก</button>
                                    <button class="ab-form-save" onclick="abSaveForm()"><i class="fa fa-check"></i> บันทึก</button>
                                </div>
                            </div>
                            <!-- Address List -->
                            <div id="abList">
                                <div class="ab-empty"><i class="fa fa-spinner fa-spin"></i>กำลังโหลด...</div>
                            </div>
                        </div>
                        <div class="ab-footer">
                            <button class="btn-ab btn-ab-close" onclick="closeAddressBook()">ปิด</button>
                            <button class="btn-ab btn-ab-add" onclick="abShowAddForm()"><i class="fa fa-plus"></i> เพิ่มที่อยู่</button>
                        </div>
                    </div>
                </div>

                <!-- Main Card -->
                <div class="card-modern">
                    <div class="card-body p-0">
                        <!-- Custom Modern Controls Layout -->
                        <div class="controls-container">
                            <!-- Date Filter -->
                            <div class="control-group" id="date-filter-group">
                                <label class="control-label d-md-block d-none">DATE ETD:</label>
                                @if ($date = Session::get('startdate'))
                                    <select id="start_date" name="start_date">
                                        <option value="">สถานะทั้งหมด</option>
                                        @php
                                            $etdDates = \App\Http\Controllers\CustomerShippingViewController::getETD3Month(strtoupper(Auth::user()->customerno));
                                        @endphp
                                        @foreach($etdDates as $value => $display)
                                            <option value="{{ $value }}" {{ $date == $value ? 'selected' : '' }}>
                                                {{ $display }}</option>
                                        @endforeach
                                    </select>
                                @else
                                    <select id="start_date" name="start_date">
                                        <option value="">สถานะทั้งหมด</option>
                                        @php
                                            $etdDates = \App\Http\Controllers\CustomerShippingViewController::getETD3Month(strtoupper(Auth::user()->customerno));
                                            $latestDate = $etdDates->keys()->first();
                                        @endphp
                                        @foreach($etdDates as $value => $display)
                                            <option value="{{ $value }}" {{ !$date && $value == $latestDate ? 'selected' : '' }}>{{ $display }}</option>
                                        @endforeach
                                    </select>
                                @endif
                                <input type="date" id="end_date" class="form-control d-none">
                            </div>
                            
                            <!-- Show Entries -->
                            <div class="control-group" id="length-container">
                                <label class="control-label d-md-block d-none">SHOW:</label>
                                <select id="custom_page_length" class="unified-select">
                                    <option value="100" selected>100</option>
                                    <option value="150">150</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                </select>
                            </div>
                            
                            <!-- Recipient Filter -->
                            <div class="control-group" id="recipient-filter-group">
                                <label class="control-label d-md-block d-none">RECIPIENT:</label>
                                <select id="recipient_filter" class="unified-select">
                                    <option value="">ผู้รับทั้งหมด</option>
                                </select>
                            </div>

                            <!-- Search Container (Filled by JS) -->
                            <div class="control-group" id="filter-container">
                                <label class="control-label d-md-block d-none">SEARCH:</label>
                                <!-- JS puts Filter here -->
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table-modern" id="dt-mant-table-1">
                                <thead>
                                    <tr>
                                        <th style="width: 40px;"><input type="checkbox" id="checkAll"></th>
                                        <th>No</th>
                                        <th>การจัดส่ง</th>
                                        <th>วันที่</th>
                                        <th>รูปหน้ากล่อง</th>
                                        <th>เลขพัสดุ</th>
                                        <th>COD</th>
                                        <th>น้ำหนัก</th>
                                        <th style="display:none;">หมายเหตุ</th>
                                        <th>ค่านำเข้า</th>
                                        <th>รูปสินค้า</th>
                                        <th>เลขกล่อง</th>
                                        <th>วันที่ใส่ตู้</th>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will fill this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            @include('layouts.partials.footer')
        </div>
    </div>
    
    <!-- Script to remove unwanted panel-header AND handle mobile toggle -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Remove panel-header if it exists (Paper Dashboard auto-injection)
        var panelHeaders = document.querySelectorAll('.panel-header, .panel-header-lg, .panel-header-sm');
        panelHeaders.forEach(function(el) {
            if(el) el.remove();
        });
        
        // Ensure main-panel has correct styles
        var mainPanel = document.querySelector('.main-panel');
        if(mainPanel) {
            mainPanel.style.marginTop = '0';
            mainPanel.style.paddingTop = '0';
        }

        // Mobile Sidebar Toggle
        var toggle = document.getElementById('sidebarToggle');
        var overlay = document.getElementById('sidebarOverlay');
        var sidebar = document.querySelector('.sidebar-modern');

        if(toggle && overlay && sidebar) {
            toggle.addEventListener('click', function() {
                sidebar.classList.add('show');
                overlay.classList.add('show');
            });

            overlay.addEventListener('click', function() {
                sidebar.classList.remove('show');
                overlay.classList.remove('show');
            });
        }
    });

    /* ... Gallery Functions (Same as previous) ... */
    var currentGalleryImages = [];
    var currentGalleryIndex = 0;

    function openGallery(images, index) {
        if (!images || images.length === 0) return;
        currentGalleryImages = images;
        currentGalleryIndex = index || 0;
        updateGalleryImage();
        document.getElementById('galleryOverlay').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    
    function closeGallery() {
        resetZoom();
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
        resetZoom();
        const img = document.getElementById('galleryImage');
        const counter = document.getElementById('galleryCounter');
        const prevBtn = document.querySelector('.gallery-prev');
        const nextBtn = document.querySelector('.gallery-next');
        
        img.src = currentGalleryImages[currentGalleryIndex];
        counter.textContent = (currentGalleryIndex + 1) + ' / ' + currentGalleryImages.length;

        if (currentGalleryImages.length <= 1) {
            prevBtn.style.display = 'none';
            nextBtn.style.display = 'none';
        } else {
            prevBtn.style.display = 'flex';
            nextBtn.style.display = 'flex';
        }
    }

    // ===== Zoom System =====
    var zoomLevel = 1;
    var panX = 0, panY = 0;
    var isZoomed = false;

    function toggleZoom() {
        if (isZoomed) {
            resetZoom();
        } else {
            zoomTo(2.5);
        }
    }

    function zoomTo(level) {
        var img = document.getElementById('galleryImage');
        var btn = document.getElementById('galleryZoomBtn');
        zoomLevel = level;
        panX = 0;
        panY = 0;
        isZoomed = true;
        img.classList.add('zoomed');
        img.style.transition = 'transform 0.3s';
        img.style.transform = 'scale(' + zoomLevel + ')';
        btn.innerHTML = '<i class="fa fa-search-minus"></i> ย่อ';
        // Hide nav when zoomed
        document.querySelector('.gallery-prev').style.opacity = '0';
        document.querySelector('.gallery-next').style.opacity = '0';
    }

    function resetZoom() {
        var img = document.getElementById('galleryImage');
        var btn = document.getElementById('galleryZoomBtn');
        zoomLevel = 1;
        panX = 0;
        panY = 0;
        isZoomed = false;
        img.classList.remove('zoomed');
        img.style.transition = 'transform 0.3s, opacity 0.3s';
        img.style.transform = '';
        img.style.opacity = '';
        btn.innerHTML = '<i class="fa fa-search-plus"></i> ซูม';
        document.querySelector('.gallery-prev').style.opacity = '';
        document.querySelector('.gallery-next').style.opacity = '';
    }

    function applyTransform() {
        var img = document.getElementById('galleryImage');
        img.style.transition = 'none';
        img.style.transform = 'scale(' + zoomLevel + ') translate(' + (panX / zoomLevel) + 'px, ' + (panY / zoomLevel) + 'px)';
    }

    // Desktop: double-click to zoom
    document.getElementById('galleryImage').addEventListener('dblclick', function(e) {
        e.preventDefault();
        toggleZoom();
    });

    // Desktop: mouse wheel zoom
    document.getElementById('galleryContent') || document.querySelector('.gallery-content');
    document.querySelector('.gallery-content').addEventListener('wheel', function(e) {
        if (!document.getElementById('galleryOverlay').classList.contains('active')) return;
        e.preventDefault();
        if (e.deltaY < 0) {
            // Zoom in
            zoomLevel = Math.min(5, zoomLevel + 0.4);
        } else {
            // Zoom out
            zoomLevel = Math.max(1, zoomLevel - 0.4);
        }
        if (zoomLevel <= 1) {
            resetZoom();
        } else {
            isZoomed = true;
            document.getElementById('galleryImage').classList.add('zoomed');
            document.getElementById('galleryZoomBtn').innerHTML = '<i class="fa fa-search-minus"></i> ย่อ';
            document.querySelector('.gallery-prev').style.opacity = '0';
            document.querySelector('.gallery-next').style.opacity = '0';
            applyTransform();
        }
    }, { passive: false });

    // Desktop: drag to pan when zoomed
    (function() {
        var img = document.getElementById('galleryImage');
        var isDragging = false, dragStartX = 0, dragStartY = 0, startPanX = 0, startPanY = 0;

        img.addEventListener('mousedown', function(e) {
            if (!isZoomed) return;
            e.preventDefault();
            isDragging = true;
            dragStartX = e.clientX;
            dragStartY = e.clientY;
            startPanX = panX;
            startPanY = panY;
        });

        document.addEventListener('mousemove', function(e) {
            if (!isDragging) return;
            panX = startPanX + (e.clientX - dragStartX);
            panY = startPanY + (e.clientY - dragStartY);
            applyTransform();
        });

        document.addEventListener('mouseup', function() {
            isDragging = false;
        });
    })();

    document.addEventListener('keydown', function(e) {
        if (!document.getElementById('galleryOverlay').classList.contains('active')) return;
        if (e.key === 'ArrowLeft' && !isZoomed) changeImage(-1);
        if (e.key === 'ArrowRight' && !isZoomed) changeImage(1);
        if (e.key === 'Escape') { if (isZoomed) resetZoom(); else closeGallery(); }
    });

    // ===== Touch: Swipe + Pinch Zoom + Pan =====
    (function() {
        var overlay = document.getElementById('galleryOverlay');
        var galleryImg = document.getElementById('galleryImage');
        var startX = 0, startY = 0, diffX = 0, diffY = 0, isSwiping = false;
        var swipeThreshold = 50;
        // Pinch
        var initialPinchDist = 0, initialZoom = 1;
        var isPinching = false;
        // Pan
        var isPanning = false, panStartX = 0, panStartY = 0, startPX = 0, startPY = 0;

        function getPinchDist(touches) {
            var dx = touches[0].clientX - touches[1].clientX;
            var dy = touches[0].clientY - touches[1].clientY;
            return Math.sqrt(dx * dx + dy * dy);
        }

        overlay.addEventListener('touchstart', function(e) {
            if (e.touches.length === 2) {
                // Pinch start
                isPinching = true;
                isSwiping = false;
                initialPinchDist = getPinchDist(e.touches);
                initialZoom = zoomLevel;
            } else if (e.touches.length === 1) {
                if (isZoomed) {
                    // Pan start
                    isPanning = true;
                    isSwiping = false;
                    panStartX = e.touches[0].clientX;
                    panStartY = e.touches[0].clientY;
                    startPX = panX;
                    startPY = panY;
                    galleryImg.style.transition = 'none';
                } else {
                    // Swipe start
                    isSwiping = true;
                    startX = e.touches[0].clientX;
                    startY = e.touches[0].clientY;
                    diffX = 0;
                    diffY = 0;
                    galleryImg.style.transition = 'none';
                }
            }
        }, { passive: true });

        overlay.addEventListener('touchmove', function(e) {
            if (isPinching && e.touches.length === 2) {
                e.preventDefault();
                var dist = getPinchDist(e.touches);
                zoomLevel = Math.max(1, Math.min(5, initialZoom * (dist / initialPinchDist)));
                if (zoomLevel > 1.05) {
                    isZoomed = true;
                    galleryImg.classList.add('zoomed');
                    document.getElementById('galleryZoomBtn').innerHTML = '<i class="fa fa-search-minus"></i> ย่อ';
                    document.querySelector('.gallery-prev').style.opacity = '0';
                    document.querySelector('.gallery-next').style.opacity = '0';
                }
                applyTransform();
            } else if (isPanning && e.touches.length === 1) {
                e.preventDefault();
                panX = startPX + (e.touches[0].clientX - panStartX);
                panY = startPY + (e.touches[0].clientY - panStartY);
                applyTransform();
            } else if (isSwiping && e.touches.length === 1) {
                diffX = e.touches[0].clientX - startX;
                diffY = e.touches[0].clientY - startY;
                if (Math.abs(diffX) > Math.abs(diffY)) {
                    e.preventDefault();
                    var clamp = Math.max(-120, Math.min(120, diffX));
                    galleryImg.style.transform = 'translateX(' + clamp + 'px)';
                    galleryImg.style.opacity = 1 - Math.abs(clamp) / 300;
                }
            }
        }, { passive: false });

        overlay.addEventListener('touchend', function(e) {
            if (isPinching) {
                isPinching = false;
                if (zoomLevel <= 1.05) {
                    resetZoom();
                }
                return;
            }

            if (isPanning) {
                isPanning = false;
                return;
            }

            if (!isSwiping) return;
            isSwiping = false;
            galleryImg.style.transition = 'transform 0.3s, opacity 0.3s';

            if (Math.abs(diffX) > swipeThreshold && Math.abs(diffX) > Math.abs(diffY)) {
                var direction = diffX < 0 ? 1 : -1;
                var slideClass = direction === 1 ? 'slide-left' : 'slide-right';
                galleryImg.classList.add(slideClass);

                setTimeout(function() {
                    galleryImg.classList.remove(slideClass);
                    galleryImg.style.transform = '';
                    galleryImg.style.opacity = '';
                    changeImage(direction);
                    var enterClass = direction === 1 ? 'slide-right' : 'slide-left';
                    galleryImg.classList.add(enterClass);
                    requestAnimationFrame(function() {
                        requestAnimationFrame(function() {
                            galleryImg.classList.remove(enterClass);
                        });
                    });
                }, 200);
            } else {
                galleryImg.style.transform = '';
                galleryImg.style.opacity = '';
            }
        }, { passive: true });

        // Double-tap to zoom on mobile
        var lastTap = 0;
        galleryImg.addEventListener('touchend', function(e) {
            if (isPinching || isPanning) return;
            var now = Date.now();
            if (now - lastTap < 300) {
                e.preventDefault();
                toggleZoom();
            }
            lastTap = now;
        });

        // Tap on overlay background to close (not on image or nav)
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeGallery();
        });
    })();

    function parseImages(data) {
        if (!data || (typeof data === 'string' && (data.trim() === '' || data.trim() === '-'))) return [];
        if (typeof data === 'string' && data.trim().startsWith('[')) {
            try { return JSON.parse(data); } catch (e) {}
        }
        if (typeof data === 'string' && data.includes(',')) {
            return data.split(',').map(function(item) { return item.trim(); }).filter(function(item) { return item !== ''; });
        }
        return [String(data).trim()];
    }

    </script>

    <!-- Gallery Slide Function (standalone - ไม่พึ่ง jQuery) -->
    <script>
    function openColumnGallery(col, imgEl) {
        try {
            var table = document.getElementById('dt-mant-table-1');
            if (!table) { openGallery([imgEl.src], 0); return; }

            var colIndex = (col === 'box') ? 4 : 9; // product is DOM index 9 (note column 8 is hidden)
            var rows = table.querySelectorAll('tbody tr');
            var images = [];
            var clickedIndex = 0;
            var clickedSrc = imgEl.src;

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

            console.log('Gallery [' + col + ']:', images.length, 'images, start:', clickedIndex);

            if (images.length > 0) {
                openGallery(images, clickedIndex);
            } else {
                openGallery([clickedSrc], 0);
            }
        } catch(e) {
            console.error('openColumnGallery error:', e);
            openGallery([imgEl.src], 0);
        }
    }
    </script>
@endsection

@section('extra-script')
    <script src="{{ asset('js/thai-address-search.js') }}"></script>
    <script>
        // Update Select Global Function (Same as previous)
        window.checkAndUpdateSelection = function() {
            try {
                var selectedCheckboxes = $('#dt-mant-table-1 tbody input[type="checkbox"]:checked');
                if (selectedCheckboxes.length > 0) {
                    var selectedIds = [];
                    selectedCheckboxes.each(function () { selectedIds.push($(this).val()); });
                    $('#trackIdsInput').val(selectedIds.join(','));
                    $('#updateForm').submit();
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกรายการที่ต้องการอัพเดท', confirmButtonColor: '#1D8AC9' });
                    } else { alert('กรุณาเลือกรายการที่ต้องการอัพเดท'); }
                }
            } catch (e) {
                console.error(e);
                alert('An error occurred: ' + e.message);
            }
        };

        $(function () {
            if ($.fn.DataTable.isDataTable('#dt-mant-table-1')) {
                $('#dt-mant-table-1').DataTable().destroy();
            }

            var dataTable = $('#dt-mant-table-1').DataTable({
                "pageLength": 100,
                "lengthMenu": [[100, 150, 200, 300], [100, 150, 200, 300]],
                "processing": true,
                "serverSide": true,
                "paging": true,
                "ordering": false,
                "language": {
                    "processing": "กำลังโหลด...",
                    "lengthMenu": "_MENU_", // Hide text
                    "search": "", // Hide text
                    "searchPlaceholder": "Search..."
                },
                "ajax": {
                    "url": "{{ route('fetch.customershippingsview') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        d.search = $("input[type='search']").val();
                        d.status = $("select.status-select-header").val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.recipient_filter = $('#recipient_filter').val();
                        d._token = "{{ csrf_token() }}";
                        d.customerno = '{{\App\User::find(auth()->id())->customerno}}';
                    }
                },
                "initComplete": function(settings, json) {
                    // NEW MODERN LOGIC: Move to custom grid
                    var length = $('#dt-mant-table-1_wrapper .dataTables_length');
                    var filter = $('#dt-mant-table-1_wrapper .dataTables_filter');
                    
                    // Add Placeholders Manually
                    filter.find('input').attr('placeholder', 'Search...');
                    
                    // Hide original DataTables length (we use custom select)
                    length.hide();
                    filter.detach().appendTo('#filter-container');

                    // Load recipients for current ETD
                    loadRecipients();
                },
                "columns": [
                    { "data": "id", "orderable": false }, // 0
                    { "data": null, "orderable": false }, // 1
                    { "data": "delivery_type_name", "orderable": false }, // 2
                    { "data": "ship_date" }, // 3
                    { "data": "box_image", "orderable": false }, // 4
                    { "data": "track_no", "className": "text-nowrap" }, // 5
                    { "data": "cod" }, // 6
                    { "data": "weight" }, // 7
                    { "data": "note", "visible": false }, // 8
                    { "data": "import_cost" }, // 9
                    { "data": "product_image", "orderable": false }, // 10
                    { "data": "box_no" }, // 11
                    { "data": "etd" }, // 12
                    { "data": "shipping_method_label", "orderable": false }, // 13
                    { "data": "status", "orderable": false }, // 14
                    { "data": null, "orderable": false }, // 15
                ],
                "columnDefs": [
                    {
                        "targets": 0,
                        "render": function (data, type, full, meta) {
                            return `<input type="checkbox" value="${full.id}" class="custom-checkbox">`;
                        }
                    },
                    {
                        "targets": 1,
                        "render": function (data, type, full, meta) {
                            return `<span style="font-weight:600;color:#64748b;">${meta.row + 1}</span>`;
                        }
                    },
                    {
                        "targets": 2, // Delivery
                        "render": function (data, type, full, meta) {
                            let badgeClass = 'pending';
                            let icon = 'fa-exclamation-circle';
                            let text = 'เลือกวิธีจัดส่ง';
                            if (data && data.trim() !== '' && data !== '-') {
                                text = data;
                                if (data.indexOf('ปัจจุบัน') !== -1) { badgeClass = 'home'; icon = 'fa-home'; }
                                else if (data.indexOf('เพิ่มที่อยู่') !== -1) { badgeClass = 'ems'; icon = 'fa-truck'; }
                                else if (data.indexOf('รับเอง') !== -1) { badgeClass = 'self'; icon = 'fa-user'; }
                                else { badgeClass = 'ems'; icon = 'fa-truck'; }
                            }
                            var html = `<span class="delivery-badge ${badgeClass}"><i class="fa ${icon}"></i> ${text}</span>`;
                            if (full.delivery_fullname && full.delivery_fullname.trim()) {
                                html += '<br><span style="color:#dc2626;font-size:10px;font-weight:600;"><i class="fa fa-user"></i> ' + full.delivery_fullname + '</span>';
                            }
                            return html;
                        }
                    },
                    {
                        "targets": 4, "render": function (data, type, full, meta) {
                            var box = parseImages(full.box_image || '');
                            if (box.length === 0) return '<span style="color:#94a3b8;">-</span>';
                            return '<img src="' + box[0] + '" class="table-img" onclick="openColumnGallery(\'box\', this)" style="cursor:pointer">';
                        }
                    },
                    {
                        "targets": 5, "render": function (data) { return `<span class="track-no">${data || '-'}</span>`; }
                    },
                    {
                        "targets": 10, "render": function (data, type, full, meta) {
                            var prod = parseImages(full.product_image || '');
                            if (prod.length === 0) return '<span style="color:#94a3b8;">-</span>';
                            return '<img src="' + prod[0] + '" class="table-img" onclick="openColumnGallery(\'product\', this)" style="cursor:pointer">';
                        }
                    },
                    {
                        "targets": 13,
                        "render": function (data, type, full) {
                            var method = full.shipping_method || 1;
                            if (method == 2) {
                                return '<span style="display:inline-block;padding:3px 8px;border-radius:12px;background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;white-space:nowrap;">✈️ เครื่องบิน</span>';
                            }
                            return '<span style="display:inline-block;padding:3px 8px;border-radius:12px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:600;white-space:nowrap;">🚢 เรือ</span>';
                        }
                    },
                    {
                        "targets": 14,
                        "render": function (data) {
                            let displayText = data || 'อยู่ระหว่างขนส่ง';
                            let badgeClass = 'shipping';
                            if (displayText.includes('ถึง') || displayText.includes('arrived')) badgeClass = 'arrived';
                            else if (displayText.includes('สำเร็จ') || displayText.includes('รับ') || displayText.includes('received')) badgeClass = 'received';
                            return `<span class="status-badge ${badgeClass}">${displayText}</span>`;
                        }
                    },
                    {
                        "targets": 15,
                        "render": function (data, type, full) {
                            return `<a class="btn-table-action btn-edit" href="${full.edit_url}" title="แก้ไข"><i class="fa fa-pencil"></i></a>`;
                        }
                    }
                ],
                "order": []
            });

            $('#start_date').on('change', function () { $('#recipient_filter').val(''); loadRecipients(true); dataTable.ajax.reload(); });
            $('#checkAll').on('change', function () { $(':checkbox', dataTable.rows().nodes()).prop('checked', $(this).prop('checked')); });
            
            dataTable.on('xhr.dt', function (e, settings, json, xhr) {
                if (json.data_export_link) $('#data-export').attr('href', json.data_export_link);
                // Update Summary Cards
                var etdLabel = json.start_date ? ('รอบปิดตู้ (' + json.start_date + ')') : 'รอบปิดตู้ (ทั้งหมด)';
                $('#sc-round-label').text(etdLabel);
                $('#sc-total').text(json.total_records || 0);
                $('#sc-import-cost').text(json.import_cost_total || '0');
                $('#sc-weight').text(json.weight_total || '0');
                $('#sc-price-total').text(json.price_total || '0');
                // Update invoice data
                if (typeof updateInvoiceData === 'function') updateInvoiceData(json);
            });

            // === Quick View: click on row (not checkbox, image, or action) ===
            $('#dt-mant-table-1 tbody').on('click', 'td', function(e) {
                // Skip if clicked on checkbox, image, link, or button
                if ($(e.target).closest('input, a, button, img, .btn-table-action, .custom-checkbox').length) return;
                var colIdx = dataTable.cell(this).index();
                if (!colIdx) return;
                // Skip columns: 0 (checkbox), 4 (box_image), 10 (product_image), 15 (action)
                if ([0, 4, 10, 15].indexOf(colIdx.column) !== -1) return;
                var rowData = dataTable.row($(this).closest('tr')).data();
                if (rowData) openQuickView(rowData);
            });

            // Gallery: openColumnGallery อยู่ใน standalone script (content section)

        });

        // Move modal to body so position:fixed works correctly
        $(function() { $('#qvOverlay').appendTo('body'); });

        // === Quick View Modal Functions ===
        function openQuickView(d) {
            var boxImgs = parseImages(d.box_image || '');
            var prodImgs = parseImages(d.product_image || '');
            var statusText = d.status || 'อยู่ระหว่างขนส่ง';
            var statusClass = 'shipping';
            if (statusText.includes('ถึง') || statusText.includes('arrived')) statusClass = 'arrived';
            else if (statusText.includes('สำเร็จ') || statusText.includes('รับ')) statusClass = 'received';

            var method = (d.shipping_method || 1) == 2 ? '✈️ เครื่องบิน' : '🚢 เรือ';

            var html = '';
            // Status + Track No header
            html += '<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">';
            html += '<span style="font-size:1.1rem;font-weight:700;color:#1D8AC9;">' + (d.track_no || 'ไม่มีเลขพัสดุ') + '</span>';
            html += '<span class="qv-status-badge ' + statusClass + '">' + statusText + '</span>';
            html += '</div>';

            // Info rows
            html += '<div class="qv-row"><span class="qv-label">วันที่</span><span class="qv-value">' + (d.ship_date || '-') + '</span></div>';
            html += '<div class="qv-row"><span class="qv-label">เลขกล่อง</span><span class="qv-value">' + (d.box_no || '-') + '</span></div>';
            html += '<div class="qv-row"><span class="qv-label">ประเภท</span><span class="qv-value">' + method + '</span></div>';
            html += '<div class="qv-row"><span class="qv-label">วันที่ใส่ตู้ (ETD)</span><span class="qv-value">' + (d.etd || '-') + '</span></div>';
            html += '<div class="qv-row"><span class="qv-label">น้ำหนัก</span><span class="qv-value">' + (d.weight || '0') + ' kg</span></div>';
            html += '<div class="qv-row"><span class="qv-label">COD</span><span class="qv-value">' + (d.cod || '0') + '</span></div>';
            html += '<div class="qv-row"><span class="qv-label">ค่านำเข้า</span><span class="qv-value" style="color:#059669;font-weight:800;">' + (d.import_cost || '0') + ' ฿</span></div>';

            // Delivery info
            var deliveryName = d.delivery_type_name || '';
            html += '<div class="qv-row"><span class="qv-label">การจัดส่ง</span><span class="qv-value">' + (deliveryName || '<span style="color:#f59e0b;">ยังไม่กำหนด</span>') + '</span></div>';

            if (d.delivery_fullname || d.delivery_address) {
                html += '<div style="margin-top:10px;"><span class="qv-label">ที่อยู่จัดส่ง</span>';
                html += '<div class="qv-address-block">';
                if (d.delivery_fullname) html += '<strong>' + d.delivery_fullname + '</strong><br>';
                if (d.delivery_mobile) html += '<i class="fa fa-phone" style="color:#94a3b8;"></i> ' + d.delivery_mobile + '<br>';
                if (d.delivery_address) html += d.delivery_address + '<br>';
                var addr2 = '';
                if (d.delivery_subdistrict) addr2 += (d.delivery_province === 'กรุงเทพมหานคร' ? 'แขวง' : 'ต.') + d.delivery_subdistrict + ' ';
                if (d.delivery_district) addr2 += (d.delivery_province === 'กรุงเทพมหานคร' ? 'เขต' : 'อ.') + d.delivery_district + ' ';
                if (addr2) html += addr2 + '<br>';
                if (d.delivery_province) html += d.delivery_province + ' ';
                if (d.delivery_postcode) html += d.delivery_postcode;
                html += '</div></div>';
            }

            // Note
            if (d.note) {
                html += '<div style="margin-top:12px;"><span class="qv-label">หมายเหตุ</span>';
                html += '<div style="margin-top:4px;padding:10px 14px;background:#fffbeb;border-radius:8px;font-size:0.85rem;color:#92400e;">' + d.note + '</div></div>';
            }

            // Images
            if (boxImgs.length > 0 || prodImgs.length > 0) {
                html += '<div style="margin-top:14px;"><span class="qv-label">รูปภาพ</span><div class="qv-images">';
                boxImgs.forEach(function(src) { html += '<img src="' + src + '" onclick="openGallery(parseImages(\'' + (d.box_image||'').replace(/'/g,"\\'") + '\'), 0)" title="รูปกล่อง">'; });
                prodImgs.forEach(function(src) { html += '<img src="' + src + '" onclick="openGallery(parseImages(\'' + (d.product_image||'').replace(/'/g,"\\'") + '\'), 0)" title="รูปสินค้า">'; });
                html += '</div></div>';
            }

            $('#qvBody').html(html);
            $('#qvEditLink').attr('href', d.edit_url || '#');
            $('#qvOverlay').addClass('active');
            $('body').css('overflow', 'hidden');
        }

        function closeQuickView() {
            $('#qvOverlay').removeClass('active');
            $('body').css('overflow', '');
        }

        // Close on backdrop click
        $('#qvOverlay').on('click', function(e) {
            if (e.target === this) closeQuickView();
        });

        // === Invoice Summary + QR PromptPay ===
        var _invData = { total: 0, importCost: '0', codCost: '0', priceTotal: '0', roundLabel: '', priceTotalRaw: 0 };

        function updateInvoiceData(json) {
            _invData.total = json.total_records || 0;
            _invData.importCost = json.import_cost_total || '0';
            _invData.codCost = json.cod_total || '0';
            _invData.priceTotal = json.price_total || '0';
            _invData.roundLabel = json.start_date ? ('รอบปิดตู้ (' + json.start_date + ')') : 'รอบปิดตู้ (ทั้งหมด)';
            _invData.priceTotalRaw = parseFloat(String(json.price_total || '0').replace(/,/g, ''));
        }

        function openInvoiceModal() {
            $('#inv-round-label').text(_invData.roundLabel);
            $('#inv-total-items').text(_invData.total + ' รายการ');
            $('#inv-import-cost').text(_invData.importCost + ' ฿');
            $('#inv-cod-cost').text(_invData.codCost + ' ฿');
            $('#inv-price-total').text(_invData.priceTotal + ' ฿');
            $('#inv-qr-amount').text('฿ ' + _invData.priceTotal);

            // Show loading, hide image
            $('#inv-qr-loading').show();
            $('#inv-qr-img').hide();

            $('#invOverlay').addClass('active');
            $('body').css('overflow', 'hidden');

            // Generate QR
            if (_invData.priceTotalRaw > 0) {
                $.ajax({
                    url: "{{ route('generate.invoice.qr') }}",
                    type: "POST",
                    data: { amount: _invData.priceTotalRaw, _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.success && res.qr_url) {
                            $('#inv-qr-img').attr('src', res.qr_url).show();
                            $('#inv-qr-loading').hide();
                        }
                    },
                    error: function() {
                        $('#inv-qr-loading').html('<span style="color:#ef4444;">ไม่สามารถสร้าง QR ได้</span>');
                    }
                });
            } else {
                $('#inv-qr-loading').html('<span style="color:#94a3b8;">ยอดชำระ 0 ฿ - ไม่ต้องชำระ</span>');
            }
        }

        function closeInvoiceModal() {
            $('#invOverlay').removeClass('active');
            $('body').css('overflow', '');
        }

        $('#invOverlay').on('click', function(e) {
            if (e.target === this) closeInvoiceModal();
        });

        // === Address Book ===
        function openAddressBook() {
            $('#abOverlay').addClass('active');
            $('body').css('overflow', 'hidden');
            abCancelForm();
            abLoadList();
        }
        function closeAddressBook() {
            $('#abOverlay').removeClass('active');
            $('body').css('overflow', '');
        }
        $('#abOverlay').on('click', function(e) {
            if (e.target === this) closeAddressBook();
        });

        function abLoadList() {
            $('#abList').html('<div class="ab-empty"><i class="fa fa-spinner fa-spin"></i> กำลังโหลด...</div>');
            $.ajax({
                url: "{{ route('address-book.index') }}",
                type: "GET",
                success: function(res) {
                    if (!res.addresses || res.addresses.length === 0) {
                        $('#abList').html('<div class="ab-empty"><i class="fa fa-map-marker"></i>ยังไม่มีที่อยู่ในสมุด<br><small>กดปุ่ม "เพิ่มที่อยู่" เพื่อเริ่มต้น</small></div>');
                        return;
                    }
                    var html = '<ul class="ab-list">';
                    res.addresses.forEach(function(a) {
                        var addrParts = [a.address, a.subdistrict, a.district, a.province, a.postcode].filter(Boolean);
                        html += '<li class="ab-item' + (a.is_default ? ' ab-default' : '') + '" data-id="' + a.id + '">';
                        html += '<div class="ab-item-info">';
                        html += '<div class="ab-item-label">' + (a.label || 'ที่อยู่') + (a.is_default ? ' <span class="ab-badge-default">ค่าเริ่มต้น</span>' : '') + '</div>';
                        html += '<div class="ab-item-name">' + a.fullname + (a.mobile ? ' <span style="color:#94a3b8;font-weight:400;font-size:0.82rem;">(' + a.mobile + ')</span>' : '') + '</div>';
                        html += '<div class="ab-item-addr">' + addrParts.join(' ') + '</div>';
                        html += '</div>';
                        html += '<div class="ab-item-actions">';
                        if (!a.is_default) html += '<button class="ab-btn-star" title="ตั้งเป็นค่าเริ่มต้น" onclick="event.stopPropagation();abSetDefault(' + a.id + ')"><i class="fa fa-star-o"></i></button>';
                        html += '<button class="ab-btn-edit" title="แก้ไข" onclick="event.stopPropagation();abEditAddr(' + a.id + ')"><i class="fa fa-pencil"></i></button>';
                        html += '<button class="ab-btn-del" title="ลบ" onclick="event.stopPropagation();abDeleteAddr(' + a.id + ')"><i class="fa fa-trash"></i></button>';
                        html += '</div></li>';
                    });
                    html += '</ul>';
                    $('#abList').html(html);
                },
                error: function() {
                    $('#abList').html('<div class="ab-empty" style="color:#ef4444;"><i class="fa fa-exclamation-circle"></i>โหลดข้อมูลไม่สำเร็จ</div>');
                }
            });
        }

        function abShowAddForm() {
            $('#abFormTitle').text('เพิ่มที่อยู่ใหม่');
            $('#ab-edit-id').val('');
            $('#ab-label, #ab-fullname, #ab-mobile, #ab-address, #ab-subdistrict, #ab-district, #ab-province, #ab-postcode').val('');
            $('#abForm').addClass('active');
        }
        function abCancelForm() {
            $('#abForm').removeClass('active');
            $('#ab-edit-id').val('');
        }

        function abSaveForm() {
            var fullname = $('#ab-fullname').val().trim();
            if (!fullname) { Swal.fire('กรุณากรอกชื่อ-นามสกุล', '', 'warning'); return; }
            var editId = $('#ab-edit-id').val();
            var data = {
                label: $('#ab-label').val().trim(),
                fullname: fullname,
                mobile: $('#ab-mobile').val().trim(),
                address: $('#ab-address').val().trim(),
                subdistrict: $('#ab-subdistrict').val().trim(),
                district: $('#ab-district').val().trim(),
                province: $('#ab-province').val().trim(),
                postcode: $('#ab-postcode').val().trim(),
                _token: "{{ csrf_token() }}"
            };
            var url, method;
            if (editId) {
                url = "{{ url('address-book') }}/" + editId;
                method = "PUT";
                data._method = 'PUT';
            } else {
                url = "{{ route('address-book.store') }}";
                method = "POST";
            }
            $.ajax({
                url: url, type: "POST", data: data,
                success: function() { abCancelForm(); abLoadList(); },
                error: function(xhr) {
                    var msg = 'บันทึกไม่สำเร็จ';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        msg = Object.values(xhr.responseJSON.errors).flat().join('\n');
                    }
                    Swal.fire('เกิดข้อผิดพลาด', msg, 'error');
                }
            });
        }

        function abEditAddr(id) {
            $.get("{{ route('address-book.index') }}", function(res) {
                var a = res.addresses.find(function(x) { return x.id == id; });
                if (!a) return;
                $('#abFormTitle').text('แก้ไขที่อยู่');
                $('#ab-edit-id').val(a.id);
                $('#ab-label').val(a.label || '');
                $('#ab-fullname').val(a.fullname || '');
                $('#ab-mobile').val(a.mobile || '');
                $('#ab-address').val(a.address || '');
                $('#ab-subdistrict').val(a.subdistrict || '');
                $('#ab-district').val(a.district || '');
                $('#ab-province').val(a.province || '');
                $('#ab-postcode').val(a.postcode || '');
                $('#abForm').addClass('active');
            });
        }

        function abDeleteAddr(id) {
            Swal.fire({
                title: 'ลบที่อยู่นี้?',
                text: 'ที่อยู่จะถูกลบออกจากสมุดถาวร',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'ลบเลย',
                cancelButtonText: 'ยกเลิก'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('address-book') }}/" + id,
                        type: "POST",
                        data: { _method: 'DELETE', _token: "{{ csrf_token() }}" },
                        success: function() { abLoadList(); },
                        error: function() { Swal.fire('ลบไม่สำเร็จ', '', 'error'); }
                    });
                }
            });
        }

        function abSetDefault(id) {
            $.ajax({
                url: "{{ url('address-book') }}/" + id + "/default",
                type: "POST",
                data: { _token: "{{ csrf_token() }}" },
                success: function() { abLoadList(); }
            });
        }
    </script>
<script>
    // === Recipient Filter Logic ===
    var _lastRecipientEtd = null;
    function loadRecipients(force) {
        var etd = $('#start_date').val();
        if (!force && etd === _lastRecipientEtd) return;
        _lastRecipientEtd = etd;
        $.ajax({
            url: "{{ route('fetch.recipients') }}",
            type: "POST",
            data: { etd: etd, _token: "{{ csrf_token() }}" },
            success: function(res) {
                var sel = $('#recipient_filter');
                var currentVal = sel.val();
                sel.find('option:not(:first)').remove();
                if (res.recipients && res.recipients.length > 0) {
                    res.recipients.forEach(function(r) {
                        sel.append('<option value="' + r.value + '">' + r.label + ' (' + r.count + ')</option>');
                    });
                }
                if (currentVal) sel.val(currentVal);
            }
        });
    }

    // When recipient filter changes, reload DataTable
    // Custom page length handler (static select - no bounce)
    $('#custom_page_length').on('change', function() {
        var newLen = parseInt($(this).val());
        $('#dt-mant-table-1').DataTable().page.len(newLen).draw();
    });

    $(document).on('change', '#recipient_filter', function() {
        $('#dt-mant-table-1').DataTable().ajax.reload();
    });

    // ป้องกันเลื่อนเม้าส์เปลี่ยนค่า dropdown
    $(document).on('wheel', '#recipient_filter', function(e) { e.preventDefault(); });
</script>

<!-- Batch Recipient Modal -->
<div id="batchRecipientModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.5); backdrop-filter:blur(2px);">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; border-radius:20px; width:95%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
        <!-- Header -->
        <div style="padding:24px 28px 16px; border-bottom:1px solid #f1f5f9;">
            <div style="display:flex; align-items:center; justify-content:space-between;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px; height:42px; background:linear-gradient(135deg,#1D8AC9,#0ea5e9); border-radius:12px; display:flex; align-items:center; justify-content:center; color:white; font-size:1.1rem;">
                        <i class="fa fa-users"></i>
                    </div>
                    <div>
                        <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:#1e293b;">กำหนดผู้รับ</h3>
                        <p id="batchRecipientCount" style="margin:0; font-size:0.82rem; color:#64748b;">0 รายการ</p>
                    </div>
                </div>
                <button onclick="closeBatchRecipientModal()" style="background:none; border:none; cursor:pointer; padding:8px;">
                    <i class="fa fa-times" style="font-size:1.2rem; color:#94a3b8;"></i>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div style="padding:20px 28px;">
            <!-- Delivery Type -->
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">วิธีจัดส่ง</label>
                <select id="batch_delivery_type" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem; color:#1e293b;">
                    <option value="3" selected>เพิ่มที่อยู่เอง</option>
                    <option value="2">ที่อยู่ปัจจุบัน</option>
                    <option value="1">รับเอง</option>
                </select>
            </div>

            <!-- Pickup name (shown for type 1 - รับเอง) -->
            <div id="batchPickupNameFields" style="display:none;">
                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ชื่อผู้รับ</label>
                    <input type="text" id="batch_pickup_name" placeholder="ชื่อผู้มารับ" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>
            </div>

            <!-- Current address preview (shown for type 2) -->
            <div id="batchCurrentAddressPreview" style="display:none; background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:12px; padding:16px; margin-bottom:16px;">
                <div style="font-size:0.82rem; font-weight:600; color:#0369a1; margin-bottom:8px;"><i class="fa fa-map-marker"></i> ที่อยู่ปัจจุบัน</div>
                <div style="font-size:0.88rem; color:#1e293b; line-height:1.6;">
                    <div><b>{{ \App\User::find(auth()->id())->name ?? '' }}</b></div>
                    <div>{{ \App\User::find(auth()->id())->mobile ?? '' }}</div>
                    <div>{{ \App\User::find(auth()->id())->addr ?? '' }}</div>
                    <div>{{ \App\User::find(auth()->id())->subdistrinct ?? '' }} {{ \App\User::find(auth()->id())->distrinct ?? '' }}</div>
                    <div>{{ \App\User::find(auth()->id())->province ?? '' }} {{ \App\User::find(auth()->id())->postcode ?? '' }}</div>
                </div>
            </div>

            <!-- Recipient Fields (shown for type 3) -->
            <div id="batchRecipientFields">
                <!-- Address Book Quick Pick -->
                <div id="batchAbPick" style="margin-bottom:14px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#8b5cf6; margin-bottom:8px;"><i class="fa fa-address-book"></i> เลือกจากสมุดที่อยู่</label>
                    <div id="batchAbPickList" style="display:flex; flex-wrap:wrap; gap:6px;">
                        <span style="font-size:0.82rem; color:#94a3b8;">กำลังโหลด...</span>
                    </div>
                    <div style="border-bottom:1px solid #f1f5f9; margin-top:14px; margin-bottom:14px;"></div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ชื่อ-นามสกุล</label>
                        <input type="text" id="batch_fullname" placeholder="ชื่อ-นามสกุล" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <small style="color:#ef4444; font-size:0.72rem;">*ค้นหาด้วยชื่อ (ประวัติการส่งที่ผ่านมา)*</small>
                        <div id="batch_fullname-results" class="search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">เบอร์โทร</label>
                        <input type="text" id="batch_mobile" placeholder="เบอร์โทร" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <small style="color:#ef4444; font-size:0.72rem;">*ค้นหาด้วยเบอร์โทร (ประวัติการส่งที่ผ่านมา)*</small>
                        <div id="batch_mobile-results" class="search-results"></div>
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ที่อยู่</label>
                    <input type="text" id="batch_address" placeholder="บ้านเลขที่ ซอย ถนน" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">แขวง/ตำบล</label>
                        <input type="text" id="batch_subdistrict" placeholder="พิมพ์เพื่อค้นหาตำบล" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_subdistrict-results" class="search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">เขต/อำเภอ</label>
                        <input type="text" id="batch_district" placeholder="พิมพ์เพื่อค้นหาอำเภอ" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_district-results" class="search-results"></div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">จังหวัด</label>
                        <input type="text" id="batch_province" placeholder="พิมพ์เพื่อค้นหาจังหวัด" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_province-results" class="search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">รหัสไปรษณีย์</label>
                        <input type="text" id="batch_postcode" placeholder="รหัสไปรษณีย์" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_postcode-results" class="search-results"></div>
                    </div>
                </div>
            </div>

            <!-- Note field (always visible) -->
            <div style="margin-bottom:10px; margin-top:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">หมายเหตุ <span style="font-weight:400; color:#94a3b8;">(ไม่บังคับ)</span></label>
                <input type="text" id="batch_note" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                <small style="color:#ef4444; font-size:0.72rem;">*ระบบ Note ทุกรายการที่เลือก*</small>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding:16px 28px 24px; border-top:1px solid #f1f5f9; display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closeBatchRecipientModal()" style="padding:12px 24px; background:#f1f5f9; color:#64748b; border:1.5px solid #e2e8f0; border-radius:12px; font-size:0.9rem; font-weight:600; cursor:pointer;">
                ยกเลิก
            </button>
            <button onclick="submitBatchRecipient()" id="batchSubmitBtn" style="padding:12px 28px; background:linear-gradient(135deg,#1D8AC9,#0ea5e9); color:white; border:none; border-radius:12px; font-size:0.9rem; font-weight:600; cursor:pointer; box-shadow:0 4px 15px rgba(29,138,201,0.3);">
                <i class="fa fa-check"></i> บันทึก
            </button>
        </div>
    </div>
</div>

<style>
    .btn-modern-accent {
        background: linear-gradient(135deg, #8b5cf6, #6d28d9) !important;
        color: white !important;
        border: none !important;
    }
    .btn-modern-accent:hover {
        background: linear-gradient(135deg, #7c3aed, #5b21b6) !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.3);
    }
    #batchRecipientModal .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 10000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        display: none;
    }
    #batchRecipientModal .search-results .search-result-item {
        padding: 10px 14px;
        cursor: pointer;
        font-size: 0.85rem;
        border-bottom: 1px solid #f8fafc;
    }
    #batchRecipientModal .search-results .search-result-item:hover {
        background: #f0f9ff;
    }
    .swal2-container {
        z-index: 99999 !important;
    }
    @media (max-width: 768px) {
        #batchRecipientModal > div > div:nth-child(2) {
            padding: 16px 20px !important;
        }
        #batchRecipientFields [style*="grid-template-columns"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>

<script>
    // === Batch Recipient Modal Logic ===
    var batchSelectedIds = [];

    function openBatchRecipientModal() {
        var selectedCheckboxes = $('#dt-mant-table-1 tbody input[type="checkbox"]:checked');
        if (selectedCheckboxes.length === 0) {
            Swal.fire({ icon: 'warning', title: 'แจ้งเตือน', text: 'กรุณาเลือกรายการที่ต้องการกำหนดผู้รับ', confirmButtonColor: '#1D8AC9' });
            return;
        }
        batchSelectedIds = [];
        selectedCheckboxes.each(function() { batchSelectedIds.push(parseInt($(this).val())); });
        $('#batchRecipientCount').text(batchSelectedIds.length + ' รายการ');

        // Reset form
        $('#batch_delivery_type').val('3');
        $('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode, #batch_note, #batch_pickup_name').val('');
        $('#batchRecipientFields').show();
        $('#batchPickupNameFields').hide();
        $('#batchCurrentAddressPreview').hide();

        $('#batchRecipientModal').fadeIn(200);

        // Load Address Book into batch modal
        loadBatchAddressBook();

        // Initialize customer search for batch modal
        initBatchCustomerSearch();
        initBatchThaiAddressSearch();
    }

    function closeBatchRecipientModal() {
        $('#batchRecipientModal').fadeOut(200);
        // Clean up search results
        $('#batchRecipientModal .search-results').hide().empty();
    }

    function loadBatchAddressBook() {
        var container = $('#batchAbPickList');
        container.html('<span style="font-size:0.82rem; color:#94a3b8;"><i class="fa fa-spinner fa-spin"></i> กำลังโหลด...</span>');
        $.ajax({
            url: "{{ route('address-book.index') }}",
            type: "GET",
            success: function(res) {
                container.empty();
                if (!res.addresses || res.addresses.length === 0) {
                    container.html('<span style="font-size:0.82rem; color:#94a3b8;">ยังไม่มีที่อยู่ — เพิ่มได้ที่ปุ่ม "สมุดที่อยู่"</span>');
                    return;
                }
                res.addresses.forEach(function(a) {
                    var btn = $('<button type="button"></button>');
                    btn.css({
                        padding: '8px 14px', border: '1.5px solid #e2e8f0', borderRadius: '10px',
                        background: a.is_default ? '#f0fdf4' : 'white', color: '#1e293b',
                        fontSize: '0.82rem', fontWeight: '600', cursor: 'pointer',
                        display: 'inline-flex', alignItems: 'center', gap: '6px',
                        transition: 'all 0.2s'
                    });
                    if (a.is_default) btn.css('borderColor', '#10b981');
                    btn.html('<i class="fa fa-map-marker" style="color:#8b5cf6;"></i> ' + (a.label || a.fullname));
                    btn.on('click', function() {
                        $('#batch_fullname').val(a.fullname || '');
                        $('#batch_mobile').val(a.mobile || '');
                        $('#batch_address').val(a.address || '');
                        $('#batch_subdistrict').val(a.subdistrict || '');
                        $('#batch_district').val(a.district || '');
                        $('#batch_province').val(a.province || '');
                        $('#batch_postcode').val(a.postcode || '');
                        // Visual feedback
                        container.find('button').css('borderColor', '#e2e8f0');
                        $(this).css('borderColor', '#8b5cf6');
                    });
                    container.append(btn);
                });
            },
            error: function() {
                container.html('<span style="font-size:0.82rem; color:#ef4444;">โหลดไม่สำเร็จ</span>');
            }
        });
    }

    // Toggle fields based on delivery type
    $('#batch_delivery_type').on('change', function() {
        var val = $(this).val();
        if (val === '3') {
            $('#batchRecipientFields').slideDown(200);
            $('#batchCurrentAddressPreview').slideUp(200);
            $('#batchPickupNameFields').slideUp(200);
        } else if (val === '2') {
            $('#batchRecipientFields').slideUp(200);
            $('#batchCurrentAddressPreview').slideDown(200);
            $('#batchPickupNameFields').slideUp(200);
        } else {
            $('#batchRecipientFields').slideUp(200);
            $('#batchCurrentAddressPreview').slideUp(200);
            $('#batchPickupNameFields').slideDown(200);
        }
    });

    function submitBatchRecipient() {
        var deliveryType = $('#batch_delivery_type').val();
        var batchNote = $('#batch_note').val().trim();
        var data = {
            ids: batchSelectedIds,
            delivery_type_id: parseInt(deliveryType),
            _token: '{{ csrf_token() }}'
        };
        if (batchNote) { data.note = batchNote; }

        if (deliveryType === '3') {
            // Validate required fields
            var fullname = $('#batch_fullname').val().trim();
            var mobile = $('#batch_mobile').val().trim();
            var address = $('#batch_address').val().trim();
            var subdistrict = $('#batch_subdistrict').val().trim();
            var district = $('#batch_district').val().trim();
            var province = $('#batch_province').val().trim();
            var postcode = $('#batch_postcode').val().trim();

            if (!fullname || !mobile || !address || !subdistrict || !district || !province || !postcode) {
                Swal.fire({ icon: 'warning', title: 'กรุณากรอกข้อมูลให้ครบ', text: 'กรุณากรอกชื่อ เบอร์โทร และที่อยู่ให้ครบถ้วน', confirmButtonColor: '#1D8AC9' });
                return;
            }

            data.delivery_fullname = fullname;
            data.delivery_mobile = mobile;
            data.delivery_address = address;
            data.delivery_subdistrict = subdistrict;
            data.delivery_district = district;
            data.delivery_province = province;
            data.delivery_postcode = postcode;
        }

        if (deliveryType === '1') {
            var pickupName = $('#batch_pickup_name').val().trim();
            if (pickupName) { data.delivery_fullname = pickupName; }
        }

        var typeName = deliveryType === '1' ? ('รับเอง: ' + ($('#batch_pickup_name').val().trim() || '-')) : (deliveryType === '2' ? 'ที่อยู่ปัจจุบัน' : data.delivery_fullname);

        Swal.fire({
            title: 'ยืนยันกำหนดผู้รับ?',
            html: 'อัพเดท <b>' + batchSelectedIds.length + '</b> รายการ<br>ผู้รับ: <b>' + typeName + '</b>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1D8AC9',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#batchSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังบันทึก...');

                $.ajax({
                    url: '{{ route("batch.update.recipient") }}',
                    type: 'POST',
                    data: data,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        closeBatchRecipientModal();
                        $('#batchSubmitBtn').prop('disabled', false).html('<i class="fa fa-check"></i> บันทึก');

                        Swal.fire({
                            icon: 'success',
                            title: 'สำเร็จ!',
                            text: res.message,
                            confirmButtonColor: '#1D8AC9',
                            timer: 2500
                        });

                        // Reload DataTable + recipients dropdown
                        $('#dt-mant-table-1').DataTable().ajax.reload();
                        loadRecipients(true);
                    },
                    error: function(xhr) {
                        $('#batchSubmitBtn').prop('disabled', false).html('<i class="fa fa-check"></i> บันทึก');
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'เกิดข้อผิดพลาด';
                        Swal.fire({ icon: 'error', title: 'ผิดพลาด', text: msg, confirmButtonColor: '#1D8AC9' });
                    }
                });
            }
        });
    }

    // Customer search for batch modal (name + phone)
    function initBatchCustomerSearch() {
        var debounceTimer;

        $('#batch_fullname').off('input').on('input', function() {
            var query = $(this).val().trim();
            clearTimeout(debounceTimer);
            if (query.length < 2) { $('#batch_fullname-results').hide().empty(); return; }
            debounceTimer = setTimeout(function() {
                $.get('/skjtrack/api/address/searchCustomerAddress', { term: query, field: 'delivery_fullname' }, function(data) {
                    var $results = $('#batch_fullname-results').empty();
                    if (data.length > 0) {
                        data.forEach(function(c) {
                            var $item = $('<div>').addClass('search-result-item')
                                .text((c.fullname || c.text || '') + ' - ' + (c.mobile || ''))
                                .data({fullname: c.fullname||'', mobile: c.mobile||'', address: c.address||'', province: c.province||'', amphoe: c.amphoe||'', tambon: c.tambon||'', zipcode: c.zipcode||''});
                            $results.append($item);
                        });
                        $results.show();
                    } else { $results.hide(); }
                });
            }, 300);
        });

        $('#batch_mobile').off('input').on('input', function() {
            var query = $(this).val().trim();
            clearTimeout(debounceTimer);
            if (query.length < 3) { $('#batch_mobile-results').hide().empty(); return; }
            debounceTimer = setTimeout(function() {
                $.get('/skjtrack/api/address/searchCustomerAddress', { term: query, field: 'delivery_mobile' }, function(data) {
                    var $results = $('#batch_mobile-results').empty();
                    if (data.length > 0) {
                        data.forEach(function(c) {
                            var $item = $('<div>').addClass('search-result-item')
                                .text((c.fullname || c.text || '') + ' - ' + (c.mobile || ''))
                                .data({fullname: c.fullname||'', mobile: c.mobile||'', address: c.address||'', province: c.province||'', amphoe: c.amphoe||'', tambon: c.tambon||'', zipcode: c.zipcode||''});
                            $results.append($item);
                        });
                        $results.show();
                    } else { $results.hide(); }
                });
            }, 300);
        });

        // Handle click on search result
        $(document).off('click', '#batch_fullname-results .search-result-item, #batch_mobile-results .search-result-item')
            .on('click', '#batch_fullname-results .search-result-item, #batch_mobile-results .search-result-item', function() {
            var $this = $(this);
            $('#batch_fullname').val($this.data('fullname') || '');
            $('#batch_mobile').val($this.data('mobile') || '');
            $('#batch_address').val($this.data('address') || '');
            $('#batch_subdistrict').val($this.data('tambon') || '');
            $('#batch_district').val($this.data('amphoe') || '');
            $('#batch_province').val($this.data('province') || '');
            $('#batch_postcode').val($this.data('zipcode') || '');
            $('#batch_fullname-results, #batch_mobile-results').hide().empty();
        });

        // Close results when clicking outside
        $(document).on('click', function(e) {
            if (!$(e.target).closest('#batch_fullname, #batch_fullname-results').length) {
                $('#batch_fullname-results').hide();
            }
            if (!$(e.target).closest('#batch_mobile, #batch_mobile-results').length) {
                $('#batch_mobile-results').hide();
            }
        });
    }

    // Thai address search for batch modal
    function initBatchThaiAddressSearch() {
        if (typeof initThaiAddressSearch === 'function') {
            initThaiAddressSearch({
                formId: '#batchRecipientModal',
                provinceField: '#batch_province',
                amphoeField: '#batch_district',
                tambonField: '#batch_subdistrict',
                zipcodeField: '#batch_postcode',
                onAddressSelect: function(address) {
                    console.log('Batch address selected:', address);
                }
            });
        }
    }

    // Close modal on ESC or backdrop click
    // ESC key disabled - must use cancel button to close
    // $(document).on('keydown', function(e) {
    //     if (e.key === 'Escape') closeBatchRecipientModal();
    // });
    // Backdrop click disabled - must use cancel button to close
    // $('#batchRecipientModal').on('click', function(e) {
    //     if (e.target === this) closeBatchRecipientModal();
    // });
</script>

@endsection