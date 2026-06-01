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

        /* Kill all unwanted animations on dashboard content */
        .dashboard-content,
        .modern-page-header,
        .modern-header-actions-wrap {
            animation: none !important;
            transition: none !important;
        }

        /* Override pseudo elements */
        .main-panel::before,
        .main-panel::after {
            display: none !important;
            content: none !important;
            background: none !important;
        }

        /* Kill ALL perfectScrollbar visuals from paper-dashboard */
        .main-panel,
        .main-panel:hover,
        .main-panel:focus,
        .main-panel:active,
        .ps-container,
        .ps-container:hover,
        .ps-container:focus {
            outline: 0 none !important;
            outline-style: none !important;
            border: none !important;
            box-shadow: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }
        .ps-scrollbar-x-rail,
        .ps-scrollbar-y-rail,
        .ps__rail-x,
        .ps__rail-y,
        .ps-container > .ps-scrollbar-x-rail,
        .ps-container > .ps-scrollbar-y-rail,
        .ps-container.ps-active-x > .ps-scrollbar-x-rail,
        .ps-container.ps-active-y > .ps-scrollbar-y-rail,
        .ps-container:hover > .ps-scrollbar-x-rail,
        .ps-container:hover > .ps-scrollbar-y-rail {
            display: none !important;
            opacity: 0 !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            background: transparent !important;
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
            cursor: pointer;
            touch-action: manipulation;
        }
        /* iOS Safari: ensure native picker opens reliably */
        #start_date {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 12px !important;
            padding-right: 36px !important;
            position: relative;
            z-index: 1;
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

        /* === ETD dropdown (mobile-friendly, with scroll buttons + dynamic positioning) === */
        .etd-dropdown { position:relative; display:inline-block; width:100%; }
        .etd-dropdown .dd-toggle { padding:0 28px 0 15px; font-size:14px; border:1px solid #e2e8f0; border-radius:10px; min-width:100%; width:100%; height:42px; background:#fff; cursor:pointer; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; position:relative; appearance:none; color:#475569; box-shadow:0 1px 2px rgba(0,0,0,0.05); box-sizing:border-box; }
        .etd-dropdown .dd-toggle::after { content:'\25BC'; position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:9px; color:#94a3b8; pointer-events:none; }
        .etd-dropdown .dd-toggle:hover { border-color:#1D8AC9; }
        .etd-dropdown .dd-menu { display:none; position:absolute; top:100%; left:0; z-index:9999; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.15); min-width:280px; width:100%; margin-top:4px; overflow:hidden; }
        .etd-dropdown .dd-menu.open { display:block; }
        .etd-dropdown .dd-list {
            max-height:300px;
            overflow-y:auto;
            overflow-x:hidden;
            padding:4px 4px 4px 0;
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
            position: relative;
        }
        .etd-dropdown .dd-list::-webkit-scrollbar { width:10px; }
        .etd-dropdown .dd-list::-webkit-scrollbar-track { background:#f1f5f9; border-radius:8px; margin:6px 2px; }
        .etd-dropdown .dd-list::-webkit-scrollbar-thumb { background:#94a3b8; border-radius:8px; border:2px solid #f1f5f9; background-clip: padding-box; min-height:32px; }
        .etd-dropdown .dd-list::-webkit-scrollbar-thumb:hover { background:#64748b; background-clip: padding-box; }
        .etd-dropdown .dd-item { padding:9px 16px; font-size:13px; cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; border-radius:6px; margin:1px 6px; }
        .etd-dropdown .dd-scroll-wrap { position: relative; z-index: 1; }
        .etd-dropdown .dd-scroll-btn {
            position: absolute; right: 6px; z-index: 10;
            width: 24px; height: 24px;
            border-radius: 6px; border: 1px solid #cbd5e1;
            background: #fff; color: #334155;
            cursor: pointer; padding: 0;
            display: none; align-items: center; justify-content: center;
            box-shadow: 0 2px 8px rgba(15,23,42,0.12);
            transition: background 0.15s, color 0.15s, border-color 0.15s, transform 0.1s, opacity 0.15s;
        }
        .etd-dropdown .dd-scroll-btn svg { width: 12px; height: 12px; }
        .etd-dropdown .dd-scroll-btn.show { display: flex; opacity: 1; }
        .etd-dropdown .dd-scroll-btn.is-disabled { opacity: 0.35; cursor: not-allowed; pointer-events: none; }
        .etd-dropdown .dd-scroll-btn:hover:not(.is-disabled) { background: #0ea5e9; color: #fff; border-color: #0ea5e9; box-shadow: 0 3px 10px rgba(14,165,233,0.35); }
        .etd-dropdown .dd-scroll-btn:active:not(.is-disabled) { transform: scale(0.92); }
        .etd-dropdown .dd-scroll-btn.up { top: 6px; }
        .etd-dropdown .dd-scroll-btn.down { bottom: 6px; }
        .etd-dropdown .dd-item:hover { background:#f0f7ff; }
        .etd-dropdown .dd-item.active { background:#1D8AC9; color:#fff; font-weight:600; }
        /* ซ่อนปุ่มลูกศรเลื่อนทุกขนาดจอ (เลื่อนด้วยเมาส์/นิ้วได้ปกติ) กันปุ่มทับรายการรอบ */
        .etd-dropdown .dd-scroll-btn,
        .etd-dropdown .dd-scroll-btn.show { display:none !important; }

        /* === Mobile: bottom-sheet สำหรับเลือกรอบปิดตู้ (ใช้งานง่ายบนมือถือ) === */
        /* ใช้ 991px ให้ตรงกับ breakpoint layout มือถือของหน้านี้ */
        .etd-dropdown .dd-sheet-head { display:none; }
        .etd-backdrop { display:none; }
        @media (max-width: 991px) {
            /* ใช้ selector อิง ID (#etdMenu/#etdBackdrop) เพราะ JS จะย้าย element ไปไว้ที่ body ตอนเปิด
               เพื่อหนี ancestor ที่สร้าง containing block ทำให้ fixed ครอบเต็มจอจริง ๆ */
            #etdBackdrop { display:block; position:fixed; inset:0; background:#0b1220; z-index:99998; opacity:0; visibility:hidden; pointer-events:none; transition:opacity .22s ease, visibility .22s ease; }
            #etdBackdrop.open { opacity:0.985; visibility:visible; pointer-events:auto; }
            #etdMenu {
                position:fixed !important; left:0 !important; right:0 !important; bottom:0 !important; top:auto !important;
                width:100% !important; min-width:0 !important; max-width:100% !important; margin:0 !important;
                max-height:88vh !important; display:flex !important; flex-direction:column !important;
                z-index:99999 !important; background:#fff !important; overflow:hidden !important;
                border-radius:22px 22px 0 0 !important; border:0 !important;
                box-shadow:0 -10px 34px rgba(0,0,0,0.28) !important;
                transform:translateY(100%); transition:transform .26s cubic-bezier(.32,.72,0,1);
                padding-bottom:env(safe-area-inset-bottom, 0px);
            }
            #etdMenu.open { transform:translateY(0); }
            #etdMenu .dd-sheet-head {
                flex:0 0 auto;
                display:flex; align-items:center; justify-content:space-between;
                padding:20px 20px 12px; border-bottom:1px solid #eef2f7;
                font-size:16px; font-weight:700; color:#0f172a;
                position:sticky; top:0; background:#fff; z-index:2;
            }
            #etdMenu .dd-scroll-wrap { flex:1 1 auto; min-height:0; display:flex; flex-direction:column; }
            #etdMenu .dd-sheet-head::before {
                content:''; position:absolute; top:8px; left:50%; transform:translateX(-50%);
                width:42px; height:4px; border-radius:99px; background:#cbd5e1;
            }
            #etdMenu .dd-sheet-close {
                border:0; background:#f1f5f9; color:#475569; width:34px; height:34px;
                border-radius:50%; font-size:16px; cursor:pointer; line-height:1; flex:0 0 auto;
            }
            #etdMenu .dd-scroll-btn { display:none !important; }
            #etdMenu .dd-list { flex:1 1 auto; min-height:0; max-height:none !important; overflow-y:auto; padding:6px 6px 14px; -webkit-overflow-scrolling:touch; }
            #etdMenu .dd-item {
                padding:15px 18px; font-size:16px; margin:3px 8px; border-radius:13px;
                display:flex; align-items:center; min-height:54px; white-space:normal;
            }
            #etdMenu .dd-item.active { box-shadow:0 4px 12px rgba(29,138,201,0.35); }
            #etdMenu .dd-item:active { background:#e0f2fe; }
            #etdMenu .dd-item.active { background:#1D8AC9; color:#fff; font-weight:600; }
            #etdMenu .dd-item:hover { background:#f0f7ff; }
        }

        /* === Recipient dropdown (admin-style: compact, clean, no scroll buttons) === */
        /* ใช้ !important + specific selector กัน mobile theme/body font override */
        .recipient-dropdown { position:relative !important; display:inline-block !important; width:100% !important; }
        .recipient-dropdown .dd-toggle { padding:0 28px 0 15px !important; font-size:14px !important; line-height:1.4 !important; border:1px solid #e2e8f0 !important; border-radius:10px !important; min-width:100% !important; width:100% !important; height:42px !important; min-height:42px !important; background:#fff !important; cursor:pointer !important; text-align:left !important; white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important; position:relative !important; appearance:none !important; box-sizing:border-box !important; color:#475569 !important; font-weight:400 !important; box-shadow:0 1px 2px rgba(0,0,0,0.05) !important; }
        .recipient-dropdown .dd-toggle::after { content:'\25BC' !important; position:absolute !important; right:12px !important; top:50% !important; transform:translateY(-50%) !important; font-size:9px !important; color:#94a3b8 !important; pointer-events:none !important; }
        .recipient-dropdown .dd-toggle:hover { border-color:#dc3545 !important; }
        .recipient-dropdown .dd-menu { display:none !important; position:absolute !important; top:100% !important; left:0 !important; z-index:9999 !important; background:#fff !important; border:1.5px solid #e2e8f0 !important; border-radius:10px !important; box-shadow:0 8px 24px rgba(0,0,0,0.15) !important; min-width:280px !important; width:max-content !important; max-width:calc(100vw - 32px) !important; margin-top:4px !important; overflow:hidden !important; }
        .recipient-dropdown .dd-menu.open { display:block !important; }
        .recipient-dropdown .dd-search { display:block !important; width:calc(100% - 16px) !important; margin:8px auto !important; padding:7px 12px !important; border:1.5px solid #e2e8f0 !important; border-radius:8px !important; font-size:13px !important; line-height:1.4 !important; outline:none !important; box-sizing:border-box !important; }
        .recipient-dropdown .dd-search:focus { border-color:#dc3545 !important; }
        .recipient-dropdown .dd-list { max-height:300px !important; overflow-y:auto !important; overflow-x:hidden !important; padding:4px 4px 4px 0 !important; scrollbar-gutter:stable !important; scrollbar-width:thin !important; scrollbar-color:#94a3b8 #f1f5f9 !important; position:relative !important; -webkit-overflow-scrolling:touch !important; }
        .recipient-dropdown .dd-list::-webkit-scrollbar { width:10px !important; -webkit-appearance:none !important; }
        .recipient-dropdown .dd-list::-webkit-scrollbar-track { background:#f1f5f9 !important; border-radius:8px !important; margin:6px 2px !important; }
        .recipient-dropdown .dd-list::-webkit-scrollbar-thumb { background:#94a3b8 !important; border-radius:8px !important; border:2px solid #f1f5f9 !important; background-clip:padding-box !important; min-height:32px !important; }
        .recipient-dropdown .dd-list::-webkit-scrollbar-thumb:hover { background:#64748b !important; background-clip:padding-box !important; }
        .recipient-dropdown .dd-item { padding:7px 14px !important; font-size:12px !important; line-height:1.4 !important; cursor:pointer !important; white-space:nowrap !important; overflow:hidden !important; text-overflow:ellipsis !important; margin:0 !important; border-radius:0 !important; color:#1a1a2e !important; font-weight:400 !important; background:transparent !important; border:none !important; min-height:auto !important; height:auto !important; }
        .recipient-dropdown .dd-item:hover { background:#f0f7ff !important; }
        .recipient-dropdown .dd-item.active { background:#0084FF !important; color:#fff !important; font-weight:600 !important; }

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
        .mobile-actions-toggle {
            display: none;
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
            
            .mobile-actions-toggle {
                display: flex !important;
                align-items: center;
                justify-content: center;
                gap: 8px;
                width: 100%;
                padding: 12px 16px;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                background: linear-gradient(135deg, #f8fafc, #f1f5f9);
                color: #475569;
                font-size: 0.95rem;
                font-weight: 700;
                font-family: inherit;
                cursor: pointer;
                margin-bottom: 0;
                transition: none !important;
                animation: none !important;
                transform: none !important;
                contain: layout style;
            }
            .mobile-actions-toggle * {
                transition: none !important;
                animation: none !important;
                transform: none !important;
            }
            .mobile-actions-toggle:active {
                background: #e2e8f0;
            }
            .mobile-actions-toggle .mobile-actions-arrow {
                margin-left: auto;
                font-size: 0.8rem;
            }
            .modern-header-actions {
                width: 100%;
                display: none !important;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
                margin-top: 0;
            }
            .modern-header-actions.mobile-open {
                display: grid !important;
                margin-top: 10px;
            }

            /* 2-column grid buttons (horizontal layout) */
            .modern-header-actions .btn,
            .modern-header-actions .btn-invoice-trigger,
            .modern-header-actions .btn-ab-trigger {
                width: 100%;
                justify-content: center;
                align-items: center;
                margin: 0;
                padding: 11px 8px;
                font-size: 0.82rem;
                line-height: 1.2;
                min-height: 44px;
                white-space: normal;
                word-break: break-word;
                text-align: center;
            }
            .modern-header-actions .btn i,
            .modern-header-actions .btn-invoice-trigger i,
            .modern-header-actions .btn-ab-trigger i {
                margin-right: 4px;
                font-size: 0.9rem;
            }
            /* Long-text buttons span full width for readability */
            .modern-header-actions #updateSelected {
                grid-column: 1 / -1;
            }
            /* Hide form (keeps grid clean) */
            .modern-header-actions form#updateForm {
                display: none !important;
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
            /* Hide page-length selector on mobile (กัน UI แน่น + ทับช่องค้นหา) — desktop ยังเห็นปกติ */
            #length-container { display: none !important; }
            #filter-container { flex: 4; }

            #recipient_filter {
                padding: 0 5px !important;
                font-size: 13px !important;
                height: 40px !important;
            }

            /* Mobile: recipient toggle ต้อง height/border-radius เท่ากับ ETD + SHOW + Search (uniform row) */
            .recipient-dropdown .dd-toggle {
                height: 42px !important;
                min-height: 42px !important;
                padding: 0 28px 0 12px !important;
                font-size: 13px !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 10px !important;
                color: #475569 !important;
                box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            }
            .recipient-dropdown .dd-toggle::after {
                right: 12px !important;
                font-size: 9px !important;
            }
            /* menu/items ใช้สไตล์เดียวกับ PC ทุกอย่าง (เลือก+เลื่อน เหมือนกัน) — ไม่ override ที่นี่ */
            
            /* Smaller inputs on mobile to fit */
            .dataTables_length select,
            .dataTables_filter input,
            #start_date {
                padding: 0 5px !important;
                font-size: 13px !important;
                height: 44px !important; /* Apple HIG min tap target */
                min-height: 44px !important;
                background-position: right 8px center !important;
            }
            /* Mobile: ensure start_date is tappable and on top */
            #start_date {
                padding-right: 26px !important;
                cursor: pointer;
                touch-action: manipulation;
                position: relative;
                z-index: 5;
                -webkit-tap-highlight-color: rgba(29,138,201,0.15);
            }
            #date-filter-group {
                position: relative;
                z-index: 3;
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
        /* Shared gradient icon — ใช้ CSS vars: --grad-from / --grad-to / --grad-shadow */
        .summary-card-icon.is-gradient {
            background: linear-gradient(135deg, var(--grad-from, #14b8a6) 0%, var(--grad-to, #0ea5e9) 100%);
            color: #fff;
            box-shadow: 0 6px 14px var(--grad-shadow, rgba(14,165,233,0.28)), inset 0 -2px 4px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
        }
        .summary-card-icon.is-gradient::after {
            content:''; position:absolute; inset:0;
            background: radial-gradient(circle at 30% 20%, rgba(255,255,255,0.35), transparent 60%);
            pointer-events:none;
        }
        .summary-card-icon.is-gradient svg { width:24px; height:24px; position:relative; z-index:1; }
        .summary-card:hover .summary-card-icon.is-gradient {
            transform: scale(1.04);
            transition: transform 0.25s;
        }
        /* Clickable summary card (e.g. Thai bill) */
        .summary-card.is-clickable { position: relative; cursor: pointer; }
        .summary-card.is-clickable::before {
            content:''; position:absolute; inset:0; border-radius:14px; pointer-events:none;
            border:1px solid transparent;
            background: linear-gradient(135deg, rgba(20,184,166,0.25), rgba(14,165,233,0.25)) border-box;
            -webkit-mask: linear-gradient(#000 0 0) padding-box, linear-gradient(#000 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
            opacity: 0; transition: opacity 0.25s;
        }
        .summary-card.is-clickable:hover::before { opacity: 1; }
        .summary-card.is-clickable:hover {
            box-shadow: 0 8px 24px rgba(14,165,233,0.16);
        }
        .summary-card .sc-arrow {
            margin-left:auto; color:#0ea5e9; font-size:0.85rem;
            opacity:0; transform: translateX(-4px);
            transition: opacity 0.25s, transform 0.25s;
            flex-shrink:0;
        }
        .summary-card.is-clickable:hover .sc-arrow { opacity:1; transform: translateX(0); }
        .summary-card .sc-click-hint {
            display:inline-flex; align-items:center; gap:3px;
            font-size:0.6rem; font-weight:700; color:#0ea5e9;
            background:#ecfeff; padding:2px 6px; border-radius:6px;
            margin-left:6px; vertical-align: middle;
        }
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
        .ab-fg.position-relative { position: relative; }
        #abForm .search-results {
            position: absolute; top: 100%; left: 0; right: 0;
            background: white; border: 1.5px solid #e2e8f0; border-radius: 8px;
            max-height: 180px; overflow-y: auto; z-index: 10001;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15); display: none;
        }
        #abForm .search-results .search-item {
            padding: 8px 12px; cursor: pointer; font-size: 0.82rem;
            border-bottom: 1px solid #f1f5f9;
        }
        #abForm .search-results .search-item:hover { background: #f0f9ff; }

        @media (max-width: 768px) {
            .summary-cards-grid {
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 6px !important;
                margin-bottom: 12px !important;
            }
            .summary-card {
                padding: 8px 10px !important;
                gap: 6px !important;
                border-radius: 10px !important;
                min-width: 0 !important;
                overflow: hidden !important;
            }
            .summary-card .sc-arrow { display: none !important; }
            .summary-card-icon.is-gradient svg { width: 16px !important; height: 16px !important; }
            .summary-card-icon {
                width: 28px !important;
                height: 28px !important;
                min-width: 28px !important;
                max-width: 28px !important;
                font-size: 0.7rem !important;
                flex-shrink: 0 !important;
                border-radius: 7px !important;
            }
            .summary-card-info {
                min-width: 0 !important;
                overflow: hidden !important;
                flex: 1 !important;
            }
            .summary-card-info .sc-label {
                font-size: 0.58rem !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
            }
            .summary-card-info .sc-value {
                font-size: 0.82rem !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                line-height: 1.2 !important;
            }
            .summary-card-info .sc-value small {
                font-size: 0.5rem !important;
            }
            .summary-card:hover {
                transform: none !important;
                box-shadow: 0 2px 10px rgba(0,0,0,0.04) !important;
            }
            .action-banner { flex-direction: column; align-items: flex-start; }
            .qv-modal { width: 98%; max-width: none; border-radius: 16px; }
            .inv-modal { width: 98%; max-width: none; border-radius: 16px; }
            .ab-modal { width: 98%; max-width: none; border-radius: 16px; }
            .ab-form-grid { grid-template-columns: 1fr; }
        }
        @media (max-width: 400px) {
            .summary-cards-grid {
                grid-template-columns: 1fr !important;
                gap: 6px !important;
            }
            .summary-card {
                padding: 10px 12px !important;
                gap: 10px !important;
            }
            .summary-card-icon {
                width: 32px !important;
                height: 32px !important;
                min-width: 32px !important;
                max-width: 32px !important;
                font-size: 0.8rem !important;
            }
            .summary-card-info .sc-label { font-size: 0.65rem !important; }
            .summary-card-info .sc-value { font-size: 0.95rem !important; }
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
                    <div class="modern-header-actions-wrap">
                        <button type="button" class="mobile-actions-toggle" id="mobileActionsToggle" onclick="toggleMobileActions()">
                            <span style="font-size:1rem;">&#9776;</span> <span id="mobileActionsLabel">เมนู</span>
                            <span class="mobile-actions-arrow" id="mobileActionsArrow">&#9662;</span>
                        </button>
                        <div class="modern-header-actions" id="headerActions">
                            <a href="#" id="data-export" class="btn btn-export-green" style="opacity:0.5;pointer-events:none;" onclick="return !!this.getAttribute('data-ready');">
                                <i class="fa fa-file-excel-o"></i> Export Excel
                            </a>
                            <a href="#" id="box-image-download" class="btn btn-export-green" style="background:linear-gradient(135deg,#3b82f6,#2563eb);border:none;opacity:0.5;pointer-events:none;" onclick="return !!this.getAttribute('data-ready');">
                                <i class="fa fa-picture-o"></i> ดาวน์โหลดรูปกล่อง
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
                            <button type="button" id="btn-invoice" class="btn-invoice-trigger" style="opacity:0.5;pointer-events:none;" onclick="openInvoiceModal()">
                                <i class="fa fa-qrcode"></i> ชำระเงิน
                            </button>
                            <button type="button" id="btn-addressbook" class="btn-ab-trigger" style="opacity:0.5;pointer-events:none;" onclick="openAddressBook()">
                                <i class="fa fa-address-book"></i> สมุดที่อยู่
                            </button>
                        </div>
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
                        <div class="summary-card-icon is-gradient" aria-hidden="true" style="--grad-from:#3b82f6;--grad-to:#6366f1;--grad-shadow:rgba(59,130,246,0.28);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 8 12 2 3 8v8l9 6 9-6V8z"/>
                                <path d="m3.3 7 8.7 5 8.7-5"/>
                                <path d="M12 22V12"/>
                            </svg>
                        </div>
                        <div class="summary-card-info">
                            <div class="sc-label" id="sc-round-label">รอบปิดตู้</div>
                            <div class="sc-value"><span id="sc-total">-</span> <small>รายการ</small></div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon is-gradient" aria-hidden="true" style="--grad-from:#10b981;--grad-to:#22c55e;--grad-shadow:rgba(16,185,129,0.28);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="6" width="20" height="12" rx="2"/>
                                <circle cx="12" cy="12" r="2.5"/>
                                <path d="M6 12h.5M17.5 12h.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div class="summary-card-info">
                            <div class="sc-label">ค่านำเข้ารวม</div>
                            <div class="sc-value"><span id="sc-import-cost">-</span> <small>฿</small></div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon is-gradient" aria-hidden="true" style="--grad-from:#a855f7;--grad-to:#ec4899;--grad-shadow:rgba(168,85,247,0.28);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m16 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                                <path d="m2 16 3-8 3 8c-.87.65-1.92 1-3 1s-2.13-.35-3-1Z"/>
                                <path d="M7 21h10"/>
                                <path d="M12 3v18"/>
                                <path d="M3 7h2c2 0 5-1 7-2 2 1 5 2 7 2h2"/>
                            </svg>
                        </div>
                        <div class="summary-card-info">
                            <div class="sc-label">น้ำหนักรวม</div>
                            <div class="sc-value"><span id="sc-weight">-</span> <small>kg</small></div>
                        </div>
                    </div>
                    <div class="summary-card">
                        <div class="summary-card-icon is-gradient" aria-hidden="true" style="--grad-from:#f97316;--grad-to:#f59e0b;--grad-shadow:rgba(249,115,22,0.28);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="4" y="2" width="16" height="20" rx="2.5"/>
                                <rect x="7" y="5" width="10" height="3.5" rx="0.6" fill="currentColor" opacity="0.18" stroke="none"/>
                                <line x1="7" y1="6.75" x2="17" y2="6.75"/>
                                <circle cx="8.5" cy="12" r="0.7" fill="currentColor"/>
                                <circle cx="12" cy="12" r="0.7" fill="currentColor"/>
                                <circle cx="15.5" cy="12" r="0.7" fill="currentColor"/>
                                <circle cx="8.5" cy="15" r="0.7" fill="currentColor"/>
                                <circle cx="12" cy="15" r="0.7" fill="currentColor"/>
                                <rect x="14.5" y="14.3" width="2" height="4" rx="0.4" fill="currentColor"/>
                                <circle cx="8.5" cy="18" r="0.7" fill="currentColor"/>
                                <circle cx="12" cy="18" r="0.7" fill="currentColor"/>
                            </svg>
                        </div>
                        <div class="summary-card-info">
                            <div class="sc-label">ค่า COD + ค่านำเข้า</div>
                            <div class="sc-value"><span id="sc-price-total">-</span> <small>฿</small></div>
                        </div>
                    </div>
                    <div class="summary-card is-clickable" id="thaiBillSummaryCard" style="display:none;" onclick="openThaiBillSummaryModal()" title="คลิกเพื่อดูบิลค่าส่งในไทยทั้งหมด">
                        <div class="summary-card-icon is-gradient" aria-hidden="true" style="--grad-from:#14b8a6;--grad-to:#0ea5e9;--grad-shadow:rgba(14,165,233,0.28);">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 18V7a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v10a1 1 0 0 0 1 1h2"/>
                                <path d="M14 9h3.45a1 1 0 0 1 .78.38l3.55 4.44a1 1 0 0 1 .22.62V17a1 1 0 0 1-1 1h-2"/>
                                <path d="M9 18h5"/>
                                <circle cx="7" cy="18" r="2.2"/>
                                <circle cx="17" cy="18" r="2.2"/>
                                <path d="M5 10h5M5 13h4" opacity="0.55"/>
                            </svg>
                        </div>
                        <div class="summary-card-info" style="min-width:0;flex:1;">
                            <div class="sc-label">
                                ค่าส่งในไทย
                                <small style="color:#94a3b8;font-weight:500;">
                                    (<span id="sc-thai-shipments">0</span> shipment / <span id="sc-thai-boxes">0</span> กล่อง<span id="sc-thai-extra-label" style="display:none;"> + <span id="sc-thai-extra-count">0</span> เพิ่ม</span>)
                                </small>
                            </div>
                            <div class="sc-value"><span id="sc-thai-total">-</span> <small>฿</small></div>
                        </div>
                        <span class="sc-arrow" aria-hidden="true"><i class="fa fa-chevron-right"></i></span>
                    </div>
                </div>

                <!-- Thai Bill Summary Modal -->
                <div class="qv-overlay" id="thaiBillOverlay">
                    <div class="qv-modal" style="max-width:680px;">
                        <div class="qv-header">
                            <h3><i class="fa fa-truck" style="color:#0ea5e9;margin-right:8px;"></i>สรุปบิลค่าส่งในไทย</h3>
                            <button class="qv-close" onclick="closeThaiBillSummaryModal()"><i class="fa fa-times"></i></button>
                        </div>
                        <div class="qv-body">
                            <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;padding:10px 14px;background:linear-gradient(135deg,#f0f9ff,#ecfeff);border:1px solid #bae6fd;border-radius:10px;margin-bottom:14px;">
                                <div style="font-size:12px;color:#475569;"><i class="fa fa-calendar"></i> <span id="tb-round-label">รอบปิดตู้</span></div>
                                <div style="display:flex;gap:14px;font-size:12px;color:#0c5e8e;font-weight:700;flex-wrap:wrap;">
                                    <span><i class="fa fa-truck"></i> <span id="tb-shipment-count">0</span> shipment</span>
                                    <span><i class="fa fa-cube"></i> <span id="tb-box-count">0</span> กล่อง</span>
                                    <span id="tb-extra-summary" style="display:none;color:#92400e;"><i class="fa fa-plus-circle"></i> <span id="tb-extra-count">0</span> ค่าบริการเพิ่ม</span>
                                    <span><i class="fa fa-money"></i> ฿ <span id="tb-total">0.00</span></span>
                                </div>
                            </div>
                            <div style="display:flex;justify-content:flex-end;margin-bottom:8px;">
                                <button type="button" class="btn btn-sm" onclick="copyAllThaiShipments()" style="background:#0ea5e9;color:#fff;border:0;padding:5px 14px;border-radius:8px;font-size:12px;font-weight:600;"><i class="fa fa-clone"></i> คัดลอกทั้งหมด (ส่งต่อลูกค้า)</button>
                            </div>
                            <div id="tb-list" style="max-height:400px;overflow-y:auto;"></div>
                            <div id="tb-extra-section" style="display:none;margin-top:16px;">
                                <div style="display:flex;align-items:center;gap:8px;padding:8px 12px;background:#fffbeb;border:1px solid #fcd34d;border-radius:8px;margin-bottom:8px;">
                                    <i class="fa fa-info-circle" style="color:#d97706;"></i>
                                    <strong style="color:#92400e;font-size:13px;">ค่าบริการเพิ่มเติม</strong>
                                </div>
                                <div id="tb-extra-list"></div>
                            </div>
                        </div>
                        <div class="qv-footer">
                            <button class="btn-qv btn-qv-close" onclick="closeThaiBillSummaryModal()">ปิด</button>
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
                                    <div class="ab-fg position-relative">
                                        <label>แขวง/ตำบล</label>
                                        <input type="text" id="ab_subdistrict" placeholder="พิมพ์เพื่อค้นหาตำบล">
                                        <div id="ab_subdistrict-results" class="search-results"></div>
                                    </div>
                                    <div class="ab-fg position-relative">
                                        <label>เขต/อำเภอ</label>
                                        <input type="text" id="ab_district" placeholder="พิมพ์เพื่อค้นหาอำเภอ">
                                        <div id="ab_district-results" class="search-results"></div>
                                    </div>
                                    <div class="ab-fg position-relative">
                                        <label>จังหวัด</label>
                                        <input type="text" id="ab_province" placeholder="พิมพ์เพื่อค้นหาจังหวัด">
                                        <div id="ab_province-results" class="search-results"></div>
                                    </div>
                                    <div class="ab-fg position-relative">
                                        <label>รหัสไปรษณีย์</label>
                                        <input type="text" id="ab_postcode" placeholder="พิมพ์รหัสไปรษณีย์">
                                        <div id="ab_postcode-results" class="search-results"></div>
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
                                @php
                                    $sessionDate = Session::get('startdate');
                                    $etdDates = \App\Http\Controllers\CustomerShippingViewController::getETD3Month(strtoupper(Auth::user()->customerno));
                                    $latestDate = $etdDates->keys()->first();
                                    $defaultEtd = $sessionDate ?: $latestDate;
                                    $defaultLabel = ($defaultEtd && isset($etdDates[$defaultEtd])) ? $etdDates[$defaultEtd] : 'สถานะทั้งหมด';
                                @endphp
                                <!-- Hidden source of truth (form submit + JS .val()) -->
                                <select id="start_date" name="start_date" style="display:none !important;" aria-hidden="true" tabindex="-1">
                                    <option value="">สถานะทั้งหมด</option>
                                    @foreach($etdDates as $value => $display)
                                        <option value="{{ $value }}" {{ $defaultEtd == $value ? 'selected' : '' }}>{{ $display }}</option>
                                    @endforeach
                                </select>
                                <!-- Custom mobile-friendly dropdown UI -->
                                <div class="etd-dropdown" id="etdDropdown">
                                    <button type="button" class="dd-toggle" id="etdToggle">{{ $defaultLabel }}</button>
                                    <div class="etd-backdrop" id="etdBackdrop"></div>
                                    <div class="dd-menu" id="etdMenu">
                                        <div class="dd-sheet-head"><span>📦 เลือกรอบปิดตู้</span><button type="button" class="dd-sheet-close" id="etdSheetClose" aria-label="ปิด">&#10005;</button></div>
                                        <div class="dd-scroll-wrap">
                                            <button type="button" class="dd-scroll-btn up" id="etdScrollUp" title="เลื่อนขึ้น" aria-label="เลื่อนขึ้น">
                                                <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7.5 6 4.5l3 3"/></svg>
                                            </button>
                                            <button type="button" class="dd-scroll-btn down" id="etdScrollDown" title="เลื่อนลง" aria-label="เลื่อนลง">
                                                <svg viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 4.5 6 7.5l3-3"/></svg>
                                            </button>
                                            <div class="dd-list" id="etdList">
                                                <!-- rendered by JS -->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <input type="date" id="end_date" class="form-control d-none">
                            </div>
                            
                            <!-- Show Entries -->
                            <div class="control-group" id="length-container">
                                <label class="control-label d-md-block d-none">SHOW:</label>
                                <select id="custom_page_length" class="unified-select">
                                    <option value="500" selected>500</option>
                                    <option value="600">600</option>
                                    <option value="700">700</option>
                                    <option value="800">800</option>
                                    <option value="900">900</option>
                                    <option value="1000">1000</option>
                                </select>
                            </div>
                            
                            <!-- Recipient Filter -->
                            <div class="control-group" id="recipient-filter-group">
                                <label class="control-label d-md-block d-none">RECIPIENT:</label>
                                <select id="recipient_filter" class="unified-select" style="display:none;">
                                    <option value="">ผู้รับทั้งหมด</option>
                                </select>
                                <div class="recipient-dropdown" id="recipientDropdown">
                                    <button type="button" class="dd-toggle" id="recipientToggle">ผู้รับทั้งหมด</button>
                                    <div class="dd-menu" id="recipientMenu">
                                        <input type="text" class="dd-search" id="recipientSearch" placeholder="ค้นหาชื่อผู้รับ...">
                                        <div class="dd-list" id="recipientList">
                                            <div class="dd-item active" data-value="">ผู้รับทั้งหมด</div>
                                        </div>
                                    </div>
                                </div>
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
                                        <th title="เลขอ้างอิงค่าส่งในไทย (Shippop)">อ้างอิงส่งไทย</th>
                                        <th title="ราคาค่าส่งในไทย (Shippop)">ค่าส่งไทย</th>
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

    /* Mobile actions toggle */
    function toggleMobileActions() {
        var actions = document.getElementById('headerActions');
        var label = document.getElementById('mobileActionsLabel');
        var isOpen = actions.classList.toggle('mobile-open');
        label.textContent = isOpen ? 'ซ่อนเมนู' : 'เมนู';
    }

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
    <script>
    // Destroy perfectScrollbar on .main-panel (paper-dashboard auto-inits on Windows)
    (function(){
        var mp = document.querySelector('.main-panel');
        if (mp) {
            if (typeof $ !== 'undefined' && $.fn.perfectScrollbar) {
                try { $('.main-panel').perfectScrollbar('destroy'); } catch(e) {}
                // Kill the plugin entirely so it can never re-init
                $.fn.perfectScrollbar = function() { return this; };
            }
            mp.classList.remove('ps', 'ps--active-x', 'ps--active-y', 'ps-container', 'ps-theme-default', 'ps-active-x', 'ps-active-y');
            var rails = mp.querySelectorAll('.ps__rail-x, .ps__rail-y, .ps-scrollbar-x-rail, .ps-scrollbar-y-rail, .ps__thumb-x, .ps__thumb-y, .ps-scrollbar-x, .ps-scrollbar-y');
            rails.forEach(function(r) { r.remove(); });
            mp.setAttribute('tabindex', '-1');
            mp.style.cssText += 'overflow:auto!important;outline:none!important;border:none!important;';
        }
        $('html').removeClass('perfect-scrollbar-on').addClass('perfect-scrollbar-off');
    })();
    </script>
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
                "pageLength": 500,
                "lengthMenu": [[500, 600, 700, 800, 900, 1000], [500, 600, 700, 800, 900, 1000]],
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
                    { "data": "thai_tracking_no", "orderable": false }, // 12
                    { "data": "thai_shipping_price", "orderable": false }, // 13
                    { "data": "etd" }, // 14
                    { "data": "shipping_method_label", "orderable": false }, // 15
                    { "data": "status", "orderable": false }, // 16
                    { "data": null, "orderable": false }, // 17
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
                                html += '<br><span style="color:#dc2626;font-size:10px;font-weight:600;"><i class="fa fa-user"></i> ' + escHtml(full.delivery_fullname) + '</span>';
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
                        "targets": 12, // เลขอ้างอิงค่าส่งไทย
                        "render": function (data, type, full) {
                            if (!data) return '<span style="color:#cbd5e1;">—</span>';
                            var boxes = full.thai_shipment_boxes || [];
                            var mainBox = full.thai_shipment_main_box;
                            var isMain = full.thai_is_main !== false; // default true if undefined
                            // ถ้าเป็นแถวรอง (ไม่ใช่ box หลัก) แสดง — + tooltip
                            if (!isMain && mainBox) {
                                return '<span class="thai-merged-sub" title="ค่าส่งและเลขอ้างอิงรวมอยู่ที่ Box.' + mainBox + ' (บิลเดียวกัน ' + boxes.length + ' กล่อง)" style="color:#94a3b8;font-size:11px;cursor:help;">'
                                    + '<i class="fa fa-link" style="font-size:9px;"></i> รวมกับ Box.' + mainBox
                                    + '</span>';
                            }
                            // Build copy text (เต็ม) — courier/ref/ผู้รับ/box/ราคา
                            var courier = full.thai_courier || '';
                            var recipient = full.delivery_fullname || '';
                            var price = parseFloat(full.thai_shipping_price) || 0;
                            var fwdLines = [];
                            if (courier) fwdLines.push(courier + ' เลขพัสดุ: ' + data);
                            else fwdLines.push('เลขพัสดุ: ' + data);
                            if (recipient) fwdLines.push('ผู้รับ: ' + recipient);
                            if (boxes.length > 1) fwdLines.push('Box: ' + boxes.join(', ') + ' (รวม ' + boxes.length + ' กล่องในบิลเดียว)');
                            else if (full.box_no) fwdLines.push('Box: ' + full.box_no);
                            if (price > 0) fwdLines.push('ค่าจัดส่ง: ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
                            var fwdText = fwdLines.join('\n').replace(/"/g, '&quot;');

                            var html = '<span class="thai-ref-cell" style="display:inline-flex;align-items:center;gap:4px;flex-wrap:wrap;">'
                                + '<code style="font-size:11px;background:#f1f5f9;color:#0c5e8e;padding:2px 6px;border-radius:4px;">' + data + '</code>'
                                + '<button type="button" class="thai-ref-copy" data-text="' + fwdText + '" title="คัดลอกข้อมูลครบ พร้อมส่งต่อลูกค้า" style="border:0;background:transparent;color:#64748b;padding:0 4px;cursor:pointer;"><i class="fa fa-clone"></i></button>';
                            // ถ้ามีหลาย box ในบิลเดียว แสดง badge บอกจำนวน
                            if (boxes.length > 1) {
                                var others = boxes.filter(function(b){ return String(b) !== String(full.box_no); }).join(', ');
                                html += '<span class="thai-shipment-multi" title="รวมในบิลเดียวกับ Box.' + others + '" style="display:inline-block;background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:1px 6px;border-radius:8px;cursor:help;">รวม ' + boxes.length + ' กล่อง</span>';
                            }
                            html += '</span>';
                            return html;
                        }
                    },
                    {
                        "targets": 13, // ค่าส่งไทย
                        "render": function (data, type, full) {
                            var n = parseFloat(data || 0);
                            if (!n || n <= 0) return '<span style="color:#cbd5e1;">—</span>';
                            var isMain = full.thai_is_main !== false;
                            var mainBox = full.thai_shipment_main_box;
                            var boxes = full.thai_shipment_boxes || [];
                            if (!isMain && mainBox) {
                                return '<span class="thai-price-sub" title="ค่าส่ง ฿' + n.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' รวมอยู่ที่ Box.' + mainBox + ' (บิลเดียวกัน ' + boxes.length + ' กล่อง)" style="color:#94a3b8;font-size:11px;cursor:help;">— (รวมที่ Box.' + mainBox + ')</span>';
                            }
                            var priceHtml = '<span style="color:#0c5e8e;font-weight:700;white-space:nowrap;">฿ ' + n.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + '</span>';
                            if (boxes.length > 1) {
                                priceHtml += '<div style="font-size:10px;color:#92400e;font-weight:600;margin-top:2px;white-space:nowrap;"><i class="fa fa-info-circle"></i> รวม ' + boxes.length + ' กล่องในบิลนี้</div>';
                            }
                            return priceHtml;
                        }
                    },
                    {
                        "targets": 15,
                        "render": function (data, type, full) {
                            var method = full.shipping_method || 1;
                            if (method == 2) {
                                return '<span style="display:inline-block;padding:3px 8px;border-radius:12px;background:#eff6ff;color:#2563eb;font-size:11px;font-weight:600;white-space:nowrap;">✈️ เครื่องบิน</span>';
                            }
                            return '<span style="display:inline-block;padding:3px 8px;border-radius:12px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:600;white-space:nowrap;">🚢 เรือ</span>';
                        }
                    },
                    {
                        "targets": 16,
                        "render": function (data) {
                            let displayText = data || 'อยู่ระหว่างขนส่ง';
                            let badgeClass = 'shipping';
                            if (displayText.includes('ถึง') || displayText.includes('arrived')) badgeClass = 'arrived';
                            else if (displayText.includes('สำเร็จ') || displayText.includes('รับ') || displayText.includes('received')) badgeClass = 'received';
                            return `<span class="status-badge ${badgeClass}">${displayText}</span>`;
                        }
                    },
                    {
                        "targets": 17,
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
                var startVal = $('#start_date').val();
                var exportBtn = document.getElementById('data-export');
                var boxBtn = document.getElementById('box-image-download');
                var invoiceBtn = document.getElementById('btn-invoice');
                var abBtn = document.getElementById('btn-addressbook');
                if (startVal) {
                    var cn = '{{\App\User::find(auth()->id())->customerno}}';
                    if (json.data_export_link) exportBtn.href = json.data_export_link;
                    [exportBtn, boxBtn, invoiceBtn, abBtn].forEach(function(b) {
                        b.style.opacity = '1';
                        b.style.pointerEvents = 'auto';
                        b.removeAttribute('title');
                    });
                    exportBtn.setAttribute('data-ready', '1');
                    boxBtn.href = '{{ url("download-box-images") }}/' + encodeURIComponent(cn) + '/' + startVal;
                    boxBtn.setAttribute('data-ready', '1');
                } else {
                    exportBtn.href = '#';
                    boxBtn.href = '#';
                    [exportBtn, boxBtn, invoiceBtn, abBtn].forEach(function(b) {
                        b.style.opacity = '0.5';
                        b.style.pointerEvents = 'none';
                        b.title = 'กรุณาเลือกรอบปิดตู้ก่อน';
                    });
                    exportBtn.removeAttribute('data-ready');
                    boxBtn.removeAttribute('data-ready');
                }
                // Update Summary Cards
                var roundWord = json.round_is_air ? 'รอบเครื่องบิน' : 'รอบปิดตู้';
                var etdLabel = json.start_date ? (roundWord + ' (' + json.start_date + ')') : (roundWord + ' (ทั้งหมด)');
                $('#sc-round-label').text(etdLabel);
                $('#sc-total').text(json.total_records || 0);
                $('#sc-import-cost').text(json.import_cost_total || '0');
                $('#sc-weight').text(json.weight_total || '0');
                $('#sc-price-total').text(json.price_total || '0');

                // Thai bill summary card (รวม shipment + extra charges)
                var ts = json.thai_shipping_summary || {};
                var hasShipments = (ts.shipment_count && ts.shipment_count > 0);
                var hasExtras = (ts.extra_count && ts.extra_count > 0);
                if (hasShipments || hasExtras) {
                    $('#sc-thai-shipments').text(ts.shipment_count || 0);
                    $('#sc-thai-boxes').text(ts.box_count || 0);
                    if (hasExtras) {
                        $('#sc-thai-extra-count').text(ts.extra_count);
                        $('#sc-thai-extra-label').show();
                    } else {
                        $('#sc-thai-extra-label').hide();
                    }
                    var grand = Number(ts.grand_total != null ? ts.grand_total : ts.total_price || 0);
                    $('#sc-thai-total').text(grand.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}));
                    $('#thaiBillSummaryCard').show();
                    window._thaiBillData = { roundLabel: etdLabel, summary: ts };
                } else {
                    $('#thaiBillSummaryCard').hide();
                    window._thaiBillData = { roundLabel: etdLabel, summary: { shipments: [], shipment_count: 0, box_count: 0, total_price: 0, extra_charges: [], extra_count: 0, extra_total: 0, grand_total: 0 } };
                }

                // Update invoice data
                if (typeof updateInvoiceData === 'function') updateInvoiceData(json);
            });

            // === Quick View: click on row (not checkbox, image, or action) ===
            $('#dt-mant-table-1 tbody').on('click', 'td', function(e) {
                // Skip if clicked on checkbox, image, link, or button
                if ($(e.target).closest('input, a, button, img, .btn-table-action, .custom-checkbox, .thai-ref-copy').length) return;
                var colIdx = dataTable.cell(this).index();
                if (!colIdx) return;
                // Skip columns: 0 (checkbox), 4 (box_image), 10 (product_image), 17 (action)
                if ([0, 4, 10, 17].indexOf(colIdx.column) !== -1) return;
                var rowData = dataTable.row($(this).closest('tr')).data();
                if (rowData) openQuickView(rowData);
            });

            // === Copy ข้อมูลบิลค่าส่งไทย (ในตาราง) — copy ครบ courier/ref/ผู้รับ/box/ราคา พร้อมส่งต่อลูกค้า ===
            $(document).on('click', '.thai-ref-copy', function(e) {
                e.preventDefault(); e.stopPropagation();
                var txt = $(this).data('text') || $(this).data('ref') || ''; // fallback compat
                if (!txt) return;
                copyTextToClipboard(String(txt));
                var $btn = $(this);
                var html = $btn.html();
                $btn.html('<i class="fa fa-check" style="color:#16a34a;"></i>');
                setTimeout(function(){ $btn.html(html); }, 1200);
                if (typeof _toast === 'function') _toast('คัดลอกข้อมูลครบแล้ว — พร้อมส่งต่อลูกค้า');
            });

            // Gallery: openColumnGallery อยู่ใน standalone script (content section)

        });

        // Move modal to body so position:fixed works correctly
        $(function() { $('#qvOverlay').appendTo('body'); });

        // Helper: Copy text to clipboard (รองรับ fallback สำหรับ HTTP/iOS)
        function copyTextToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).catch(function(){ _copyFallback(text); });
            } else {
                _copyFallback(text);
            }
        }
        function _copyFallback(text) {
            var ta = document.createElement('textarea');
            ta.value = text;
            ta.style.position = 'fixed';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.focus(); ta.select();
            try { document.execCommand('copy'); } catch(e){}
            document.body.removeChild(ta);
        }
        function _toast(msg, type) {
            type = type || 'success';
            var bg = type === 'success' ? '#16a34a' : (type === 'error' ? '#dc2626' : '#0c5e8e');
            var $t = $('<div style="position:fixed;top:20px;right:20px;background:' + bg + ';color:#fff;padding:10px 18px;border-radius:10px;font-weight:600;z-index:99999;box-shadow:0 6px 24px rgba(0,0,0,.18);font-size:13px;">' + msg + '</div>');
            $('body').append($t);
            setTimeout(function(){ $t.fadeOut(300, function(){ $t.remove(); }); }, 1800);
        }

        // === Quick View Modal Functions ===
        // escape HTML กัน XSS เวลาเอาข้อมูลจาก server (ชื่อผู้รับ/ที่อยู่/หมายเหตุ ฯลฯ) มาต่อเป็น HTML
        function escHtml(s) {
            return $('<div>').text(s == null ? '' : String(s)).html();
        }

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
                if (d.delivery_fullname) html += '<strong>' + escHtml(d.delivery_fullname) + '</strong><br>';
                if (d.delivery_mobile) html += '<i class="fa fa-phone" style="color:#94a3b8;"></i> ' + escHtml(d.delivery_mobile) + '<br>';
                if (d.delivery_address) html += escHtml(d.delivery_address) + '<br>';
                var addr2 = '';
                if (d.delivery_subdistrict) addr2 += (d.delivery_province === 'กรุงเทพมหานคร' ? 'แขวง' : 'ต.') + escHtml(d.delivery_subdistrict) + ' ';
                if (d.delivery_district) addr2 += (d.delivery_province === 'กรุงเทพมหานคร' ? 'เขต' : 'อ.') + escHtml(d.delivery_district) + ' ';
                if (addr2) html += addr2 + '<br>';
                if (d.delivery_province) html += escHtml(d.delivery_province) + ' ';
                if (d.delivery_postcode) html += escHtml(d.delivery_postcode);
                html += '</div></div>';
            }

            // === บิลค่าส่งไทย (Shippop) ===
            if (d.thai_tracking_no || (parseFloat(d.thai_shipping_price)||0) > 0) {
                var courier = d.thai_courier || '';
                var refNo = d.thai_tracking_no || '';
                var price = parseFloat(d.thai_shipping_price) || 0;
                var priceTxt = price > 0 ? ('฿ ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2})) : '—';
                var boxes = d.thai_shipment_boxes || [];
                var mainBox = d.thai_shipment_main_box;
                var isMain = d.thai_is_main !== false;
                var isMerged = boxes.length > 1;

                var recipient = d.delivery_fullname || '';

                // Build copy text
                var forwardLines = [];
                if (courier) forwardLines.push(courier + (refNo ? ' เลขพัสดุ: ' + refNo : ''));
                else if (refNo) forwardLines.push('เลขพัสดุ: ' + refNo);
                if (recipient) forwardLines.push('ผู้รับ: ' + recipient);
                if (isMerged) {
                    forwardLines.push('Box: ' + boxes.join(', ') + ' (รวม ' + boxes.length + ' กล่องในบิลเดียว)');
                } else {
                    if (d.box_no) forwardLines.push('Box: ' + d.box_no);
                }
                if (price > 0) {
                    var priceLabel = isMerged ? ('ค่าจัดส่ง (รวมทั้งบิล ' + boxes.length + ' กล่อง): ') : 'ค่าจัดส่ง: ';
                    forwardLines.push(priceLabel + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
                }
                var forwardText = forwardLines.join('\n');

                html += '<div style="margin-top:14px;padding:12px 14px;background:linear-gradient(135deg,#f0f9ff,#ecfeff);border:1px solid #bae6fd;border-radius:10px;">';
                html += '<div style="display:flex;align-items:center;justify-content:space-between;gap:8px;margin-bottom:8px;flex-wrap:wrap;">';
                html += '<span style="color:#0c5e8e;font-weight:700;font-size:0.9rem;"><i class="fa fa-truck"></i> บิลค่าส่งในไทย';
                if (isMerged) html += ' <span style="background:#fef3c7;color:#92400e;font-size:10px;font-weight:700;padding:2px 8px;border-radius:8px;margin-left:4px;">รวม ' + boxes.length + ' กล่อง</span>';
                html += '</span>';
                if (forwardText) {
                    html += '<button type="button" class="btn-qv-copy-ref" data-text="' + forwardText.replace(/"/g,'&quot;') + '" style="border:0;background:#0c5e8e;color:#fff;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;"><i class="fa fa-clone"></i> คัดลอกส่งต่อลูกค้า</button>';
                }
                html += '</div>';

                // Merged-bill info banner
                if (isMerged) {
                    var boxesHtml = boxes.map(function(b){
                        var isCurrent = String(b) === String(d.box_no);
                        var isMainBox = String(b) === String(mainBox);
                        var style = 'display:inline-block;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:600;margin:2px;';
                        if (isCurrent) {
                            style += 'background:#0c5e8e;color:#fff;';
                        } else if (isMainBox) {
                            style += 'background:#fef3c7;color:#92400e;border:1px solid #fcd34d;';
                        } else {
                            style += 'background:#fff;color:#475569;border:1px solid #e2e8f0;';
                        }
                        return '<span style="' + style + '">Box.' + b + (isMainBox ? ' ★' : '') + '</span>';
                    }).join('');
                    html += '<div style="background:#fffbeb;border:1px dashed #fcd34d;border-radius:8px;padding:8px 10px;margin-bottom:8px;">';
                    html += '<div style="font-size:11px;color:#92400e;font-weight:700;margin-bottom:4px;"><i class="fa fa-info-circle"></i> บิลนี้รวม ' + boxes.length + ' กล่องในเลขอ้างอิงเดียว — ค่าส่ง ' + priceTxt + ' เป็นยอดรวมของทั้งบิล</div>';
                    html += '<div>' + boxesHtml + '</div>';
                    html += '<div style="font-size:10px;color:#a16207;margin-top:4px;">★ = กล่องหลักที่ระบบเก็บค่าส่ง</div>';
                    html += '</div>';
                }

                if (courier) html += '<div class="qv-row" style="border:0;padding:3px 0;"><span class="qv-label">ผู้ขนส่ง</span><span class="qv-value">' + escHtml(courier) + '</span></div>';
                if (refNo) html += '<div class="qv-row" style="border:0;padding:3px 0;"><span class="qv-label">เลขอ้างอิง</span><span class="qv-value" style="font-family:monospace;color:#0c5e8e;font-weight:700;">' + escHtml(refNo) + '</span></div>';
                if (recipient) html += '<div class="qv-row" style="border:0;padding:3px 0;"><span class="qv-label">ผู้รับ</span><span class="qv-value"><i class="fa fa-user" style="color:#94a3b8;font-size:11px;"></i> ' + escHtml(recipient) + '</span></div>';
                if (price > 0) {
                    var priceLine = priceTxt;
                    if (isMerged) priceLine += ' <span style="font-size:10px;color:#92400e;font-weight:600;">(ยอดรวมทั้งบิล)</span>';
                    html += '<div class="qv-row" style="border:0;padding:3px 0;"><span class="qv-label">ค่าส่งไทย</span><span class="qv-value" style="color:#0c5e8e;font-weight:800;">' + priceLine + '</span></div>';
                }
                html += '</div>';
            }

            // Note
            if (d.note) {
                html += '<div style="margin-top:12px;"><span class="qv-label">หมายเหตุ</span>';
                html += '<div style="margin-top:4px;padding:10px 14px;background:#fffbeb;border-radius:8px;font-size:0.85rem;color:#92400e;">' + escHtml(d.note) + '</div></div>';
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

        // Copy ข้อความ "ส่งต่อลูกค้า" ใน Quick View
        $(document).on('click', '.btn-qv-copy-ref', function(e) {
            e.preventDefault(); e.stopPropagation();
            var txt = $(this).data('text') || '';
            if (!txt) return;
            copyTextToClipboard(String(txt));
            _toast('คัดลอกข้อความแล้ว — พร้อมส่งต่อลูกค้า');
        });

        // === Thai Bill Summary Modal ===
        $(function(){ $('#thaiBillOverlay').appendTo('body'); });

        function openThaiBillSummaryModal() {
            var d = window._thaiBillData || { roundLabel:'-', summary:{shipments:[],shipment_count:0,box_count:0,total_price:0,extra_charges:[],extra_count:0,extra_total:0,grand_total:0} };
            var s = d.summary || {};
            $('#tb-round-label').text(d.roundLabel || '-');
            $('#tb-shipment-count').text(s.shipment_count || 0);
            $('#tb-box-count').text(s.box_count || 0);
            var grand = Number(s.grand_total != null ? s.grand_total : s.total_price || 0);
            $('#tb-total').text(grand.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}));
            // Extra charges (Repack ฯลฯ)
            var extras = s.extra_charges || [];
            if (extras.length > 0) {
                $('#tb-extra-count').text(extras.length);
                $('#tb-extra-summary').show();
                renderExtraChargesList(extras);
                $('#tb-extra-section').show();
            } else {
                $('#tb-extra-summary').hide();
                $('#tb-extra-section').hide();
            }

            var html = '';
            var ships = s.shipments || [];
            if (ships.length === 0 && extras.length === 0) {
                html = '<div style="padding:24px;text-align:center;color:#94a3b8;"><i class="fa fa-inbox" style="font-size:32px;"></i><br><br>ยังไม่มีข้อมูลค่าส่งในไทยในรอบนี้</div>';
            } else if (ships.length === 0) {
                html = '<div style="padding:14px;text-align:center;color:#94a3b8;font-size:12px;">ไม่มีค่าส่งผูกกล่อง — ดูค่าบริการเพิ่มเติมด้านล่าง</div>';
            } else {
                html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
                html += '<thead style="background:#f1f5f9;"><tr>';
                html += '<th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;font-size:11px;color:#475569;">#</th>';
                html += '<th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;font-size:11px;color:#475569;">Courier / เลขอ้างอิง / ผู้รับ</th>';
                html += '<th style="padding:8px;text-align:left;border-bottom:1px solid #e2e8f0;font-size:11px;color:#475569;">Box</th>';
                html += '<th style="padding:8px;text-align:right;border-bottom:1px solid #e2e8f0;font-size:11px;color:#475569;">ราคา</th>';
                html += '<th style="padding:8px;text-align:center;border-bottom:1px solid #e2e8f0;font-size:11px;color:#475569;">Copy</th>';
                html += '</tr></thead><tbody>';
                ships.forEach(function(sh, i){
                    var courier = sh.courier || '-';
                    var ref = sh.refNo || '';
                    var recipient = sh.recipient_name || '';
                    var boxesStr = (sh.boxes || []).join(', ');
                    var price = parseFloat(sh.price) || 0;
                    var priceTxt = price > 0 ? ('฿ ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2})) : '—';
                    var fwd = [];
                    if (courier && courier !== '-') fwd.push(courier + (ref ? ' เลขพัสดุ: ' + ref : ''));
                    else if (ref) fwd.push('เลขพัสดุ: ' + ref);
                    if (recipient) fwd.push('ผู้รับ: ' + recipient);
                    if (boxesStr) fwd.push('Box: ' + boxesStr);
                    if (price > 0) fwd.push('ค่าจัดส่ง: ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
                    var fwdText = fwd.join('\n').replace(/"/g, '&quot;');
                    html += '<tr style="border-bottom:1px solid #f1f5f9;">';
                    html += '<td style="padding:8px;color:#64748b;font-weight:600;vertical-align:top;">' + (i+1) + '</td>';
                    html += '<td style="padding:8px;">';
                    html +=   '<div style="font-weight:700;color:#0c5e8e;">' + courier + '</div>';
                    html +=   '<div style="font-family:monospace;font-size:11px;color:#475569;">' + ref + '</div>';
                    if (recipient) html += '<div style="font-size:11px;color:#475569;margin-top:2px;"><i class="fa fa-user" style="color:#94a3b8;font-size:10px;"></i> ' + recipient + '</div>';
                    html += '</td>';
                    html += '<td style="padding:8px;font-family:monospace;font-size:11px;color:#475569;vertical-align:top;">' + (boxesStr || '—') + '</td>';
                    html += '<td style="padding:8px;text-align:right;color:#0c5e8e;font-weight:700;white-space:nowrap;vertical-align:top;">' + priceTxt + '</td>';
                    html += '<td style="padding:8px;text-align:center;vertical-align:top;"><button type="button" class="tb-row-copy" data-text="' + fwdText + '" style="border:0;background:#f1f5f9;color:#0c5e8e;padding:4px 8px;border-radius:6px;cursor:pointer;" title="คัดลอก"><i class="fa fa-clone"></i></button></td>';
                    html += '</tr>';
                });
                html += '</tbody></table>';
            }
            $('#tb-list').html(html);

            $('#thaiBillOverlay').addClass('active');
            $('body').css('overflow', 'hidden');
        }
        function closeThaiBillSummaryModal() {
            $('#thaiBillOverlay').removeClass('active');
            $('body').css('overflow', '');
        }
        $('#thaiBillOverlay').on('click', function(e) {
            if (e.target === this) closeThaiBillSummaryModal();
        });
        $(document).on('click', '.tb-row-copy', function(e){
            e.preventDefault(); e.stopPropagation();
            var t = $(this).data('text') || '';
            if (!t) return;
            copyTextToClipboard(String(t));
            _toast('คัดลอก shipment นี้แล้ว');
        });

        function renderExtraChargesList(extras) {
            var html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
            html += '<thead style="background:#fef3c7;"><tr>';
            html += '<th style="padding:8px;text-align:left;border-bottom:1px solid #fcd34d;font-size:11px;color:#92400e;">#</th>';
            html += '<th style="padding:8px;text-align:left;border-bottom:1px solid #fcd34d;font-size:11px;color:#92400e;">รายละเอียด</th>';
            html += '<th style="padding:8px;text-align:right;border-bottom:1px solid #fcd34d;font-size:11px;color:#92400e;">ราคา</th>';
            html += '<th style="padding:8px;text-align:center;border-bottom:1px solid #fcd34d;font-size:11px;color:#92400e;">Copy</th>';
            html += '</tr></thead><tbody>';
            extras.forEach(function(ec, i){
                var price = parseFloat(ec.price) || 0;
                var priceTxt = price > 0 ? ('฿ ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2})) : '—';
                var courier = ec.courier || '';
                var ref = ec.refNo || '';
                var recipient = ec.recipient_name || '';
                var desc = ec.description || '';
                var detailParts = [];
                if (courier) detailParts.push('<strong style="color:#92400e;">' + courier + '</strong>');
                if (ref) detailParts.push('<span style="font-family:monospace;font-size:11px;color:#475569;">' + ref + '</span>');
                if (recipient) detailParts.push('<span style="color:#475569;">' + recipient + '</span>');
                if (desc) detailParts.push('<span style="color:#a16207;font-size:11px;font-style:italic;">' + desc + '</span>');
                var detailHtml = detailParts.length ? detailParts.join('<br>') : '<span style="color:#94a3b8;">ค่าบริการเพิ่มเติม</span>';
                var fwd = [];
                if (courier) fwd.push(courier + (ref ? ' เลขพัสดุ: ' + ref : ''));
                else if (ref) fwd.push('เลขพัสดุ: ' + ref);
                if (recipient) fwd.push('ผู้รับ: ' + recipient);
                if (desc) fwd.push(desc);
                if (price > 0) fwd.push('ค่าจัดส่ง: ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
                var fwdText = fwd.join('\n').replace(/"/g, '&quot;');
                html += '<tr style="border-bottom:1px solid #fef3c7;">';
                html += '<td style="padding:8px;color:#92400e;font-weight:600;">' + (i+1) + '</td>';
                html += '<td style="padding:8px;">' + detailHtml + '</td>';
                html += '<td style="padding:8px;text-align:right;color:#92400e;font-weight:700;white-space:nowrap;">' + priceTxt + '</td>';
                html += '<td style="padding:8px;text-align:center;"><button type="button" class="tb-row-copy" data-text="' + fwdText + '" style="border:0;background:#fef3c7;color:#92400e;padding:4px 8px;border-radius:6px;cursor:pointer;" title="คัดลอก"><i class="fa fa-clone"></i></button></td>';
                html += '</tr>';
            });
            html += '</tbody></table>';
            $('#tb-extra-list').html(html);
        }

        function copyAllThaiShipments() {
            var d = window._thaiBillData || {};
            var s = d.summary || {};
            var ships = s.shipments || [];
            var extras = s.extra_charges || [];
            if (ships.length === 0 && extras.length === 0) { _toast('ไม่มีข้อมูลให้คัดลอก', 'error'); return; }
            var lines = ['📦 บิลค่าส่งในไทย — ' + (d.roundLabel || ''), ''];
            ships.forEach(function(sh, i){
                var courier = sh.courier || '-';
                var ref = sh.refNo || '';
                var recipient = sh.recipient_name || '';
                var boxesStr = (sh.boxes || []).join(', ');
                var price = parseFloat(sh.price) || 0;
                lines.push((i+1) + ') ' + courier + (ref ? ' เลขพัสดุ: ' + ref : ''));
                if (recipient) lines.push('   ผู้รับ: ' + recipient);
                if (boxesStr) lines.push('   Box: ' + boxesStr);
                if (price > 0) lines.push('   ค่าจัดส่ง: ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
                lines.push('');
            });
            if (extras.length > 0) {
                lines.push('— ค่าบริการเพิ่มเติม —');
                extras.forEach(function(ec, i){
                    var idx = ships.length + i + 1;
                    var courier = ec.courier || '';
                    var ref = ec.refNo || '';
                    var recipient = ec.recipient_name || '';
                    var desc = ec.description || 'ค่าบริการเพิ่มเติม';
                    var price = parseFloat(ec.price) || 0;
                    var header = courier ? (courier + (ref ? ' เลขพัสดุ: ' + ref : '')) : (ref ? ('เลขพัสดุ: ' + ref) : desc);
                    lines.push(idx + ') ' + header);
                    if (recipient) lines.push('   ผู้รับ: ' + recipient);
                    if (courier && desc && !ref) lines.push('   ' + desc);
                    if (price > 0) lines.push('   ค่าจัดส่ง: ' + price.toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
                    lines.push('');
                });
            }
            var grand = s.grand_total != null ? s.grand_total : s.total_price;
            if ((grand || 0) > 0) {
                lines.push('รวมทั้งหมด: ' + Number(grand).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2}) + ' บาท');
            }
            copyTextToClipboard(lines.join('\n'));
            _toast('คัดลอกทั้งหมด ' + (ships.length + extras.length) + ' รายการ พร้อมส่งต่อ');
        }

        // === Invoice Summary + QR PromptPay ===
        var _invData = { total: 0, importCost: '0', codCost: '0', priceTotal: '0', roundLabel: '', priceTotalRaw: 0 };

        function updateInvoiceData(json) {
            _invData.total = json.total_records || 0;
            _invData.importCost = json.import_cost_total || '0';
            _invData.codCost = json.cod_total || '0';
            _invData.priceTotal = json.price_total || '0';
            var _invRoundWord = json.round_is_air ? 'รอบเครื่องบิน' : 'รอบปิดตู้';
            _invData.roundLabel = json.start_date ? (_invRoundWord + ' (' + json.start_date + ')') : (_invRoundWord + ' (ทั้งหมด)');
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
                    data: { etd: $('#start_date').val(), _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        if (res.success && res.qr_url) {
                            $('#inv-qr-img').attr('src', res.qr_url).show();
                            $('#inv-qr-loading').hide();
                            // แสดงยอดที่ server คำนวณจริง (กันยอดบนจอกับยอดใน QR ไม่ตรงกัน)
                            if (res.formatted_amount) $('#inv-qr-amount').text('฿ ' + res.formatted_amount);
                        } else if (res.message) {
                            $('#inv-qr-loading').html('<span style="color:#94a3b8;">' + res.message + '</span>');
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
            $('#ab-label, #ab-fullname, #ab-mobile, #ab-address, #ab_subdistrict, #ab_district, #ab_province, #ab_postcode').val('');
            $('#abForm .search-results').hide();
            $('#abForm').addClass('active');
        }
        function abCancelForm() {
            $('#abForm').removeClass('active');
            $('#ab-edit-id').val('');
            $('#abForm .search-results').hide();
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
                subdistrict: $('#ab_subdistrict').val().trim(),
                district: $('#ab_district').val().trim(),
                province: $('#ab_province').val().trim(),
                postcode: $('#ab_postcode').val().trim(),
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
                $('#ab_subdistrict').val(a.subdistrict || '');
                $('#ab_district').val(a.district || '');
                $('#ab_province').val(a.province || '');
                $('#ab_postcode').val(a.postcode || '');
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
    // === ETD Custom Dropdown ===
    function rebuildEtdDropdown() {
        var list = $('#etdList');
        var currentVal = $('#start_date').val() || '';
        var html = '';
        $('#start_date option').each(function() {
            var $opt = $(this);
            var val = $opt.attr('value') || '';
            var label = $opt.text();
            var isActive = (String(val) === String(currentVal)) ? ' active' : '';
            html += '<div class="dd-item' + isActive + '" data-value="' + $('<div>').text(val).html() + '">' + $('<div>').text(label).html() + '</div>';
        });
        list.html(html);
    }
    function updateEtdScrollHint() {
        var list = document.getElementById('etdList');
        var upBtn = document.getElementById('etdScrollUp');
        var downBtn = document.getElementById('etdScrollDown');
        if (!list) return;
        var isScrollable = list.scrollHeight - list.clientHeight > 4;
        var atTop = list.scrollTop <= 4;
        var atBottom = list.scrollHeight - list.clientHeight - list.scrollTop <= 4;
        if (upBtn) {
            upBtn.classList.toggle('show', isScrollable);
            upBtn.classList.toggle('is-disabled', atTop);
        }
        if (downBtn) {
            downBtn.classList.toggle('show', isScrollable);
            downBtn.classList.toggle('is-disabled', atBottom);
        }
    }
    function positionEtdMenu() {
        var toggle = document.getElementById('etdToggle');
        var menu = document.getElementById('etdMenu');
        var list = document.getElementById('etdList');
        if (!toggle || !menu || !list) return;
        if (window.innerWidth <= 991) {
            // โหมดมือถือ: เป็น bottom-sheet ให้ CSS จัดตำแหน่งเอง (ล้าง inline style)
            menu.style.top = ''; menu.style.bottom = ''; menu.style.marginTop = ''; menu.style.marginBottom = '';
            list.style.maxHeight = '';
            return;
        }
        var rect = toggle.getBoundingClientRect();
        var vh = window.innerHeight;
        var pad = 16, minH = 160, prefMax = 360;
        var spaceBelow = vh - rect.bottom - pad;
        var spaceAbove = rect.top - pad;
        if (spaceBelow >= Math.min(prefMax, minH) || spaceBelow >= spaceAbove) {
            menu.style.top = '100%'; menu.style.bottom = 'auto';
            menu.style.marginTop = '4px'; menu.style.marginBottom = '';
            list.style.maxHeight = Math.max(minH, Math.min(prefMax, spaceBelow - 20)) + 'px';
        } else {
            menu.style.top = 'auto'; menu.style.bottom = '100%';
            menu.style.marginTop = ''; menu.style.marginBottom = '4px';
            list.style.maxHeight = Math.max(minH, Math.min(prefMax, spaceAbove - 20)) + 'px';
        }
    }
    function openEtdMenu() {
        var menuEl = document.getElementById('etdMenu');
        var bdEl = document.getElementById('etdBackdrop');
        var ddEl = document.getElementById('etdDropdown');
        if (window.innerWidth <= 991) {
            // มือถือ: ย้ายแผง+ฉากหลังไปไว้ที่ body เพื่อให้ fixed ครอบเต็มจอจริง (หนี containing block)
            if (bdEl && bdEl.parentNode !== document.body) document.body.appendChild(bdEl);
            if (menuEl && menuEl.parentNode !== document.body) document.body.appendChild(menuEl);
        } else if (ddEl) {
            // เดสก์ท็อป: ย้ายกลับเข้า #etdDropdown เพื่อให้ตำแหน่ง dropdown ปกติ
            if (bdEl && bdEl.parentNode !== ddEl) ddEl.appendChild(bdEl);
            if (menuEl && menuEl.parentNode !== ddEl) ddEl.appendChild(menuEl);
        }
        var menu = $('#etdMenu');
        rebuildEtdDropdown();
        menu.addClass('open');
        $('#etdBackdrop').addClass('open');
        positionEtdMenu();
        // เปิดมาให้เห็นรอบล่าสุด (บนสุด) เสมอ ทุกอุปกรณ์ — บังคับซ้ำหลัง layout/animation นิ่ง (iOS)
        var _le = document.getElementById('etdList');
        if (_le) {
            _le.scrollTop = 0;
            requestAnimationFrame(function(){ _le.scrollTop = 0; });
            setTimeout(function(){ _le.scrollTop = 0; }, 60);
            setTimeout(function(){ _le.scrollTop = 0; updateEtdScrollHint(); }, 260);
        }
        updateEtdScrollHint();
    }
    function closeEtdMenu() { $('#etdMenu').removeClass('open'); $('#etdBackdrop').removeClass('open'); }
    // ปิด bottom-sheet เมื่อแตะปุ่มปิด หรือฉากหลังมืด (มือถือ)
    $(document).on('click touchend', '#etdSheetClose, #etdBackdrop', function(e) {
        e.preventDefault(); e.stopPropagation();
        closeEtdMenu();
    });

    // Toggle button — รองรับทั้ง click และ touch (LIFF/iOS webview)
    $(document).on('click touchend', '#etdToggle', function(e) {
        e.preventDefault(); e.stopPropagation();
        if ($('#etdMenu').hasClass('open')) closeEtdMenu(); else openEtdMenu();
    });
    // Item select — แยก "แตะเพื่อเลือก" ออกจาก "ลากเพื่อเลื่อน"
    function selectEtdItem($item) {
        var val = $item.attr('data-value') || '';
        var label = $item.text();
        $('#start_date').val(val).trigger('change');
        $('#etdToggle').text(label);
        $('#etdList .dd-item').removeClass('active');
        $item.addClass('active');
        closeEtdMenu();
    }
    var _etdTap = { x:0, y:0, t:0, moved:false };
    $(document).on('touchstart', '#etdList', function(e) {
        var t = (e.originalEvent.touches && e.originalEvent.touches[0]) || null;
        if (!t) return;
        _etdTap = { x:t.clientX, y:t.clientY, t:Date.now(), moved:false };
    });
    $(document).on('touchmove', '#etdList', function(e) {
        var t = (e.originalEvent.touches && e.originalEvent.touches[0]) || null;
        if (!t) return;
        if (Math.abs(t.clientY - _etdTap.y) > 10 || Math.abs(t.clientX - _etdTap.x) > 10) _etdTap.moved = true;
    });
    $(document).on('touchend', '#etdList .dd-item', function(e) {
        // ถ้านิ้วเลื่อน (scroll) หรือกดค้างนานเกินไป = ไม่ใช่การแตะเลือก
        if (_etdTap.moved || (Date.now() - _etdTap.t) > 700) return;
        e.preventDefault(); e.stopPropagation();
        selectEtdItem($(this));
    });
    $(document).on('click', '#etdList .dd-item', function(e) {
        e.preventDefault(); e.stopPropagation();
        selectEtdItem($(this));
    });
    // Sync UI when select changes externally
    $(document).on('change', '#start_date', function() {
        var val = $(this).val();
        var label = $(this).find('option:selected').text();
        $('#etdToggle').text(label);
        $('#etdList .dd-item').removeClass('active');
        $('#etdList .dd-item').filter(function(){ return String($(this).attr('data-value')) === String(val); }).addClass('active');
    });
    // Close on outside click
    $(document).on('click touchend', function(e) {
        if (!$(e.target).closest('#etdDropdown, #etdMenu').length) closeEtdMenu();
    });
    // Scroll buttons for ETD
    (function setupEtdScrollButtons(){
        var holdTimer = null;
        function startScroll(dir) {
            var list = document.getElementById('etdList');
            if (!list) return;
            list.scrollTop += dir * 40;
            updateEtdScrollHint();
        }
        function bind(id, dir) {
            $(document).on('mousedown touchstart', '#' + id, function(e) {
                e.preventDefault();
                startScroll(dir);
                if (holdTimer) clearInterval(holdTimer);
                holdTimer = setInterval(function(){ startScroll(dir); }, 120);
            });
            $(document).on('mouseup mouseleave touchend touchcancel', '#' + id, function() {
                if (holdTimer) { clearInterval(holdTimer); holdTimer = null; }
            });
            $(document).on('click', '#' + id, function(e){ e.stopPropagation(); });
        }
        bind('etdScrollUp', -1);
        bind('etdScrollDown', 1);
    })();
    $('#etdList').on('scroll', updateEtdScrollHint);
    var _etdReposTimer = null;
    $(window).on('resize scroll', function() {
        if (!$('#etdMenu').hasClass('open')) return;
        if (_etdReposTimer) return;
        _etdReposTimer = setTimeout(function() {
            _etdReposTimer = null;
            if ($('#etdMenu').hasClass('open')) positionEtdMenu();
        }, 80);
    });

    // === Recipient Filter Logic (admin-style) ===
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
                var list = $('#recipientList');
                list.find('.dd-item:not(:first)').remove();
                if (res.recipients && res.recipients.length > 0) {
                    res.recipients.forEach(function(r) {
                        var v = escHtml(r.value), l = escHtml(r.label), c = escHtml(r.count);
                        sel.append('<option value="' + v + '">' + l + ' (' + c + ')</option>');
                        list.append('<div class="dd-item" data-value="' + v + '">' + l + ' (' + c + ')</div>');
                    });
                }
                if (currentVal) {
                    sel.val(currentVal);
                    syncRecipientDropdown(currentVal);
                }
            }
        });
    }

    // === Custom Recipient Dropdown (UX เหมือน ETD: เลือก+เลื่อน เหมือนกันทั้ง PC/มือถือ) ===
    function scrollRecipientToActive() {
        var $list = $('#recipientList');
        if (!$list.length) return;
        var $active = $list.find('.dd-item.active').first();
        if ($active.length) {
            var listEl = $list[0], activeEl = $active[0];
            var targetTop = activeEl.offsetTop - (listEl.clientHeight / 2) + (activeEl.offsetHeight / 2);
            listEl.scrollTop = Math.max(0, targetTop);
        } else {
            $list[0].scrollTop = 0;
        }
    }
    function openRecipientMenu() {
        var menu = $('#recipientMenu');
        menu.addClass('open');
        $('#recipientSearch').val('').trigger('input');
        // เลื่อนไปยังรายการที่เลือกไว้ให้อยู่กลางจอ (เหมือน ETD)
        scrollRecipientToActive();
        setTimeout(function(){ $('#recipientSearch').focus(); scrollRecipientToActive(); }, 50);
    }
    function closeRecipientMenu() { $('#recipientMenu').removeClass('open'); }

    // Toggle — รองรับทั้ง click และ touch (LIFF/iOS webview)
    $(document).on('click touchend', '#recipientToggle', function(e) {
        e.preventDefault(); e.stopPropagation();
        if ($('#recipientMenu').hasClass('open')) closeRecipientMenu(); else openRecipientMenu();
    });

    $(document).on('click touchend', function(e) {
        if (!$(e.target).closest('#recipientDropdown').length) {
            closeRecipientMenu();
        }
    });

    $('#recipientSearch').on('input', function() {
        var q = $(this).val().toLowerCase();
        $('#recipientList .dd-item').each(function() {
            var text = $(this).text().toLowerCase();
            $(this).toggle(text.indexOf(q) !== -1);
        });
    });

    $(document).on('click touchend', '#recipientList .dd-item', function(e) {
        e.preventDefault(); e.stopPropagation();
        var val = $(this).data('value');
        var label = $(this).text();
        $('#recipient_filter').val(val === undefined ? '' : val).trigger('change');
        $('#recipientToggle').text(label);
        $('#recipientList .dd-item').removeClass('active');
        $(this).addClass('active');
        closeRecipientMenu();
    });

    function syncRecipientDropdown(val) {
        $('#recipientList .dd-item').removeClass('active');
        $('#recipientList .dd-item').filter(function() {
            return String($(this).data('value')) === String(val);
        }).addClass('active');
        var activeItem = $('#recipientList .dd-item.active');
        if (activeItem.length) $('#recipientToggle').text(activeItem.text());
    }

    // Custom page length handler (static select - no bounce)
    $('#custom_page_length').on('change', function() {
        var newLen = parseInt($(this).val());
        $('#dt-mant-table-1').DataTable().page.len(newLen).draw();
    });

    $(document).on('change', '#recipient_filter', function() {
        syncRecipientDropdown($(this).val());
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
                $.get("{{ route('search.customer.address') }}", { term: query, field: 'delivery_fullname' }, function(data) {
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
                $.get("{{ route('search.customer.address') }}", { term: query, field: 'delivery_mobile' }, function(data) {
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

    // Thai address search for address book modal
    if (typeof initThaiAddressSearch === 'function') {
        initThaiAddressSearch({
            formId: '#abForm',
            provinceField: '#ab_province',
            amphoeField: '#ab_district',
            tambonField: '#ab_subdistrict',
            zipcodeField: '#ab_postcode'
        });
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