@extends('layouts.app')

@section('template_title')
    Customershipping
@endsection
@section('extra-css')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Flatpickr ship icon on Mondays */
        .flatpickr-day.is-monday { position: relative; }
        .flatpickr-day.is-monday::after {
            content: '\1F6A2';
            position: absolute;
            bottom: -2px;
            right: -2px;
            font-size: 9px;
            line-height: 1;
            pointer-events: none;
        }
        .flatpickr-calendar { font-family: inherit; }
        .table td, .table th {
            white-space: nowrap; /* ปรับให้ข้อมูลในตารางไม่ขึ้นบรรทัดใหม่ */
        }
        input[type="checkbox"] {
            accent-color: #dc3545;
        }
        th,td {
            text-align: center;
        }
        .dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
        }
        
        /* ปรับ container ของ dot ให้อยู่กึ่งกลาง */
        .status-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }
        
        .bg-danger {
            background-color: #dc3545;
        }
        
        .bg-warning {
            background-color: #ffc107;
        }
        
        .bg-success {
            background-color: #28a745;
        }
        
        .bg-secondary {
            background-color: #6c757d;
        }
        
        .d-flex {
            display: flex !important;
        }
        
        .align-items-center {
            align-items: center !important;
        }
        
        .mr-2 {
            margin-right: 0.5rem !important;
        }
        
        /* ปรับ border และพื้นหลังวันที่เริ่มต้นให้เป็นสีแดง */
        .fp-start-date {
            border: 2px solid #dc3545 !important;
            background-color: #dc3545 !important;
            color: #ffffff !important;
            cursor: pointer !important;
        }
        .fp-start-date:focus {
            border-color: #dc3545 !important;
            background-color: #dc3545 !important;
            color: #ffffff !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
        .fp-start-date::placeholder { color: rgba(255,255,255,0.7) !important; }
        input.flatpickr-input[readonly] { cursor: pointer !important; }
        input[type='submit'].disabled { opacity: 0.5; pointer-events: none; }

        /* === Floating Box Search Popup === */
        .box-search-toggle { position:fixed; right:20px; bottom:100px; z-index:1050; background:#dc3545; color:#fff; border:none; border-radius:50%; width:56px; height:56px; font-size:22px; cursor:pointer; box-shadow:0 4px 16px rgba(220,53,69,0.4); transition:all 0.3s; display:flex; align-items:center; justify-content:center; }
        .box-search-toggle:hover { background:#c82333; transform:scale(1.1); }
        .box-search-panel { position:fixed; right:20px; bottom:170px; z-index:1051; background:#fff; border-radius:12px; box-shadow:0 8px 32px rgba(0,0,0,0.25); width:300px; display:none; overflow:hidden; }
        .box-search-panel.open { display:block; }
        .box-search-panel .panel-header { background:#dc3545; color:#fff; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; font-weight:600; font-size:14px; }
        .box-search-panel .panel-header .close-btn { background:none; border:none; color:#fff; font-size:22px; cursor:pointer; padding:0 4px; line-height:1; }
        .box-search-panel .panel-body { padding:16px; }
        .box-search-panel .panel-body input { width:100%; padding:10px 12px; border:2px solid #e2e8f0; border-radius:8px; font-size:16px; font-family:inherit; }
        .box-search-panel .panel-body input:focus { outline:none; border-color:#dc3545; }
        .box-search-panel .hint { font-size:12px; color:#718096; margin-top:8px; }
        .box-search-panel .result-info { font-size:13px; color:#2d3748; margin-top:8px; font-weight:600; }
        .box-search-panel .btn-clear-box { margin-top:10px; width:100%; padding:8px; background:#f7fafc; border:1px solid #e2e8f0; border-radius:6px; cursor:pointer; font-family:inherit; font-size:13px; color:#718096; }
        .box-search-panel .btn-clear-box:hover { background:#edf2f7; color:#2d3748; }

        /* === Box Image Gallery === */
        .gallery-overlay { display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.92); z-index:10000; align-items:center; justify-content:center; flex-direction:column; }
        .gallery-overlay.open { display:flex; }
        .gallery-overlay .gallery-close { position:absolute; top:16px; right:20px; background:none; border:none; color:#fff; font-size:40px; cursor:pointer; z-index:10001; }
        .gallery-overlay .gallery-close:hover { color:#fc8181; }
        .gallery-overlay .gallery-img { max-width:90%; max-height:75vh; object-fit:contain; border-radius:8px; user-select:none; }
        .gallery-overlay .gallery-nav { position:absolute; top:50%; transform:translateY(-50%); background:rgba(255,255,255,0.15); border:none; color:#fff; font-size:36px; cursor:pointer; padding:12px 18px; border-radius:8px; }
        .gallery-overlay .gallery-nav:hover { background:rgba(255,255,255,0.3); }
        .gallery-overlay .gallery-prev { left:16px; }
        .gallery-overlay .gallery-next { right:16px; }
        .gallery-overlay .gallery-counter { color:#fff; font-size:16px; margin-top:16px; font-weight:600; }
        .gallery-overlay .gallery-label { color:rgba(255,255,255,0.8); font-size:14px; margin-top:6px; }

        /* === Red Scrollbar === */
        ::-webkit-scrollbar { width:14px; height:14px; }
        ::-webkit-scrollbar-track { background:#f8f8f8; }
        ::-webkit-scrollbar-thumb { background:linear-gradient(180deg,#dc3545,#c82333); border-radius:7px; border:2px solid #f8f8f8; }
        ::-webkit-scrollbar-thumb:hover { background:linear-gradient(180deg,#c82333,#a71d2a); }
        * { scrollbar-width:auto; scrollbar-color:#dc3545 #f8f8f8; }

        /* === Modern Admin UI === */
        .card { border:none; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08); overflow:visible; }
        .card-header { background:#fff !important; border-bottom:1px solid #f0f0f0; padding:12px 20px !important; border-radius:12px 12px 0 0 !important; }
        .card-body { padding:16px 20px !important; }

        /* Toolbar rows */
        .toolbar-row { display:flex; flex-wrap:wrap; gap:8px; align-items:center; padding:4px 0; }
        .toolbar-row + .toolbar-row { border-top:1px solid #f0f0f0; margin-top:6px; padding-top:10px; }
        .toolbar-group { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
        .toolbar-group-label { font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-right:2px; white-space:nowrap; }
        .toolbar-spacer { flex:1; }

        /* Modern buttons */
        .btn-modern { border-radius:8px !important; font-size:12px !important; font-weight:600 !important; padding:6px 14px !important; border:none !important; transition:all 0.2s !important; display:inline-flex !important; align-items:center !important; gap:5px !important; white-space:nowrap !important; }
        .btn-modern:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,0.15) !important; }
        .btn-modern i { font-size:13px; }
        .btn-modern.btn-red { background:linear-gradient(135deg,#dc3545,#c82333) !important; color:#fff !important; }
        .btn-modern.btn-blue { background:linear-gradient(135deg,#0084FF,#0066cc) !important; color:#fff !important; }
        .btn-modern.btn-green { background:linear-gradient(135deg,#28a745,#1e7e34) !important; color:#fff !important; }
        .btn-modern.btn-orange { background:linear-gradient(135deg,#fd7e14,#e8690a) !important; color:#fff !important; }
        .btn-modern.btn-pink { background:linear-gradient(135deg,#e91e63,#c2185b) !important; color:#fff !important; }
        .btn-modern.btn-line { background:linear-gradient(135deg,#06C755,#05a648) !important; color:#fff !important; }
        .btn-modern.btn-dark { background:linear-gradient(135deg,#343a40,#23272b) !important; color:#fff !important; }
        .btn-modern.btn-outline { background:#fff !important; border:2px solid #dee2e6 !important; color:#495057 !important; }
        .btn-modern.btn-outline:hover { border-color:#dc3545 !important; color:#dc3545 !important; background:#fff8f8 !important; }
        .btn-modern.disabled, .btn-modern[disabled] { opacity:0.45 !important; pointer-events:none !important; transform:none !important; box-shadow:none !important; }

        /* Date filter area */
        .date-filter-bar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; padding:12px 16px; background:#f8fafc; border-radius:10px; margin-bottom:16px; border:1px solid #e2e8f0; }
        .date-filter-bar label { font-size:12px; font-weight:700; color:#475569; margin:0; white-space:nowrap; }
        .date-filter-bar input { border-radius:8px; border:2px solid #e2e8f0; padding:6px 12px; font-size:13px; max-width:180px; transition:border-color 0.2s; }
        .date-filter-bar input:focus { border-color:#dc3545; outline:none; box-shadow:0 0 0 3px rgba(220,53,69,0.1); }

        /* Summary stat cards */
        .stats-row { display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin:16px 0; }
        .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 20px; text-align:center; min-width:130px; flex:1; max-width:200px; transition:all 0.2s; }
        .stat-card:hover { border-color:#dc3545; box-shadow:0 4px 12px rgba(220,53,69,0.1); }
        .stat-card .stat-label { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; }
        .stat-card .stat-value { font-size:20px; font-weight:800; color:#1e293b; margin-top:2px; }
        .stat-card .stat-unit { font-size:12px; color:#64748b; font-weight:500; }
        .stat-card.stat-highlight { border-left:4px solid #dc3545; }

        /* Better table header */
        .table thead th { background:#f8fafc !important; color:#475569 !important; font-size:11px !important; font-weight:700 !important; text-transform:uppercase !important; letter-spacing:0.3px !important; border-bottom:2px solid #e2e8f0 !important; padding:10px 18px 10px 8px !important; }
        .table tbody td { font-size:12px; padding:8px !important; vertical-align:middle !important; }
        .table-striped tbody tr:nth-of-type(odd) { background-color:rgba(248,250,252,0.7) !important; }
        .table-hover tbody tr:hover { background-color:rgba(220,53,69,0.04) !important; }
        /* ปิด DataTables blue row highlight ไม่ให้เด้ง */
        table.dataTable tbody tr.selected, table.dataTable tbody tr.selected td,
        table.dataTable tbody tr.active, table.dataTable tbody tr.active td,
        table.dataTable tbody tr:focus, table.dataTable tbody tr:focus td { background-color: inherit !important; outline: none !important; box-shadow: none !important; }
        table.dataTable tbody tr { outline: none !important; }
        .table tbody tr:focus-within { background: inherit !important; }

        /* Custom recipient dropdown */
        .recipient-dropdown { position:relative; display:inline-block; }
        .recipient-dropdown .dd-toggle { padding:4px 28px 4px 10px; font-size:12px; border:1.5px solid #e2e8f0; border-radius:8px; min-width:180px; height:34px; background:#fff; cursor:pointer; text-align:left; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; position:relative; appearance:none; }
        .recipient-dropdown .dd-toggle::after { content:'\25BC'; position:absolute; right:8px; top:50%; transform:translateY(-50%); font-size:9px; color:#94a3b8; pointer-events:none; }
        .recipient-dropdown .dd-toggle:hover { border-color:#dc3545; }
        .recipient-dropdown .dd-menu { display:none; position:absolute; top:100%; left:0; z-index:9999; background:#fff; border:1.5px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 24px rgba(0,0,0,0.15); min-width:280px; margin-top:4px; overflow:hidden; }
        .recipient-dropdown .dd-menu.open { display:block; }
        .recipient-dropdown .dd-search { display:block; width:calc(100% - 16px); margin:8px auto; padding:7px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; }
        .recipient-dropdown .dd-search:focus { border-color:#dc3545; }
        .recipient-dropdown .dd-list { max-height:300px; overflow-y:auto; padding:4px 0; }
        .recipient-dropdown .dd-list::-webkit-scrollbar { width:6px; }
        .recipient-dropdown .dd-list::-webkit-scrollbar-track { background:#f1f1f1; }
        .recipient-dropdown .dd-list::-webkit-scrollbar-thumb { background:#c1c1c1; border-radius:3px; }
        .recipient-dropdown .dd-list::-webkit-scrollbar-thumb:hover { background:#999; }
        .recipient-dropdown .dd-item { padding:7px 14px; font-size:12px; cursor:pointer; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        .recipient-dropdown .dd-item:hover { background:#f0f7ff; }
        .recipient-dropdown .dd-item.active { background:#0084FF; color:#fff; font-weight:600; }

        /* Page title */
        .page-title { font-size:18px; font-weight:800; color:#1e293b; display:flex; align-items:center; gap:8px; }
        .page-title i { color:#dc3545; }

        /* === Modern Action Buttons === */
        .action-btns { display:flex; gap:6px; margin-top:6px; }
        .btn-act { display:inline-flex; align-items:center; gap:4px; padding:5px 12px; border-radius:6px; font-size:11px; font-weight:600; text-decoration:none; border:none; cursor:pointer; transition:all 0.2s; }
        .btn-act-edit { background:#eef2ff; color:#4f46e5; border:1px solid #c7d2fe; }
        .btn-act-edit:hover { background:#4f46e5; color:#fff; text-decoration:none; box-shadow:0 2px 6px rgba(79,70,229,0.3); }
        .btn-act-del { background:#fef2f2; color:#dc2626; border:1px solid #fecaca; }
        .btn-act-del:hover { background:#dc2626; color:#fff; box-shadow:0 2px 6px rgba(220,38,38,0.3); }

        /* === Mobile Responsive === */
        @media (max-width: 768px) {
            .container-fluid { padding:8px !important; }
            .card { border-radius:8px; }
            .card-header { padding:10px 12px !important; }
            .card-body { padding:10px 12px !important; }

            /* Toolbar: stack vertically */
            .toolbar-row { flex-direction:column; align-items:stretch; gap:6px; }
            .toolbar-row + .toolbar-row { margin-top:4px; padding-top:8px; }
            .toolbar-spacer { display:none; }
            .toolbar-group { flex-wrap:wrap; justify-content:flex-start; gap:4px; }
            .toolbar-group-label { width:100%; margin-bottom:2px; font-size:10px; }
            .page-title { font-size:15px; }

            /* Buttons: smaller on mobile */
            .btn-modern { font-size:11px !important; padding:5px 10px !important; border-radius:6px !important; }

            /* Date filter: stack */
            .date-filter-bar { flex-direction:column; align-items:stretch; gap:6px; padding:10px 12px; }
            .date-filter-bar input { max-width:100%; width:100%; }
            .date-filter-bar label { font-size:11px; }

            /* Stat cards: 2 columns */
            .stats-row { gap:8px; margin:10px 0; }
            .stat-card { min-width:0; padding:8px 10px; flex:1 1 calc(50% - 8px); max-width:none; }
            .stat-card .stat-label { font-size:9px; }
            .stat-card .stat-value { font-size:16px; }
            .stat-card .stat-unit { font-size:10px; }

            /* Table: smaller font */
            .table thead th { font-size:9px !important; padding:6px 4px !important; }
            .table tbody td { font-size:11px !important; padding:6px 4px !important; }

            /* Floating box search */
            .box-search-toggle { width:44px; height:44px; font-size:18px; right:12px; bottom:80px; }
            .box-search-panel { width:calc(100vw - 24px); right:12px; bottom:140px; }

            /* Gallery: hide arrows on mobile, use swipe instead */
            .gallery-overlay .gallery-nav { display:none !important; }
            .gallery-overlay .gallery-img { max-width:95%; }
            #gPrev, #gNext { display:none !important; }
            #gImg { max-width:95% !important; }
        }

        @media (max-width: 480px) {
            .toolbar-group { gap:3px; }
            .btn-modern { font-size:10px !important; padding:4px 8px !important; gap:3px !important; }
            .btn-modern i { font-size:11px; }
            .page-title { font-size:13px; gap:5px; }
            .stat-card { flex:1 1 100%; }
            .date-filter-bar { padding:8px 10px; gap:4px; }
        }

    </style>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <!-- Row 1: Title + Status Actions -->
                        <div class="toolbar-row">
                            <span class="page-title"><i class="fa fa-ship"></i> My Shipping</span>
                            <div class="toolbar-spacer"></div>
                            <div class="toolbar-group">
                                <span class="toolbar-group-label">อัพเดทสถานะ:</span>
                                <form method="POST" action="{{ route('update-status-shipping2') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                                    <input type="submit" class="btn-modern btn-red disabled" id="updateSelected" value="📦 สินค้าถึงไทยแล้ว">
                                </form>
                                <form method="POST" action="{{ route('update-status-received2') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids3" id="trackIdsInput3" value="">
                                    <input type="submit" class="btn-modern btn-orange disabled" id="updateSelected3" value="✅ สำเร็จ">
                                </form>
                                <form method="POST" action="{{ route('update-status-pay2') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids2" id="trackIdsInput2" value="">
                                    <input type="submit" class="btn-modern btn-blue disabled" id="updateSelected2" value="💰 ชำระเงินแล้ว">
                                </form>
                                <form method="POST" action="{{ route('update-thai-bill-paid') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids4" id="trackIdsInput4" value="">
                                    <input type="submit" class="btn-modern disabled" id="updateSelected4" value="🚚 ชำระค่าส่งไทยแล้ว" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4) !important; color:#fff !important;">
                                </form>
                            </div>
                        </div>
                        <!-- Row 2: Navigation + Features -->
                        <div class="toolbar-row">
                            <div class="toolbar-group">
                                <a href="{{ route('welcome') }}" class="btn-modern btn-dark"><i class="fa fa-dashboard"></i> Dashboard</a>
                                <a href="{{ route('customershippings.create') }}" class="btn-modern btn-blue"><i class="fa fa-plus"></i> Create New</a>
                                <button id="invoiceBtn" class="btn-modern btn-red disabled"><i class="fa fa-file-text-o"></i> Invoice</button>
                            </div>
                            <div class="toolbar-spacer"></div>
                            <div class="toolbar-group">
                                <span class="toolbar-group-label">ส่งออก:</span>
                                <button id="shipping-export" class="btn-modern btn-green disabled"><i class="fa fa-file-excel-o"></i> Shipping Excel</button>
                                <a href="{{url('customershippingsexport2')}}" id="data-export" class="btn-modern btn-blue"><i class="fa fa-database"></i> Data Excel</a>
                                <button id="btn-export-labels" class="btn-modern btn-pink"><i class="fa fa-tags"></i> PDF Label</button>
                                <button id="btn-pile-labels" class="btn-modern btn-pink" style="background:linear-gradient(135deg,#7c3aed,#9333ea) !important;"><i class="fa fa-cubes"></i> Label กอง ANW-820</button>
                                <a href="{{url('customershippingsimport')}}" class="btn-modern btn-orange"><i class="fa fa-upload"></i> Import</a>
                            </div>
                            <div class="toolbar-group" style="margin-left:4px;">
                                <span class="toolbar-group-label">แชท:</span>
                                <button id="btn-send-invoice-chat" class="btn-modern btn-blue"><i class="fa fa-paper-plane"></i> แจ้งค่านำเข้า</button>
                                <button id="btn-line-notify" class="btn-modern btn-line"><i class="fa fa-commenting"></i> LINE</button>
                                <button id="btn-thai-shipping-notify" class="btn-modern btn-modern" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4) !important; color:#fff !important;"><i class="fa fa-truck"></i> แจ้งค่าส่งไทย</button>
                                <button id="btn-thai-remind" class="btn-modern btn-modern" style="background:linear-gradient(135deg,#ef4444,#f97316) !important; color:#fff !important;"><i class="fa fa-bell"></i> แจ้งเตือนค้างจ่าย</button>
                                <button id="btn-broadcast-etd" class="btn-modern btn-modern" style="background:linear-gradient(135deg,#f59e0b,#d97706) !important; color:#fff !important;" title="ส่งข้อความบรอดแคสให้ลูกค้าทั้งหมดในรอบปิดตู้ที่เลือก"><i class="fa fa-bullhorn"></i> บรอดแคสรอบนี้</button>
                            </div>
                        </div>
                    </div>
                    {{-- SweetAlert2 success popup (replaces old green bar) --}}
                    @if ($message = Session::get('success'))
                        <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            Swal.fire({
                                icon: 'success',
                                title: @json($message),
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                showConfirmButton: false,
                                timerProgressBar: true,
                                showClass: { popup: 'swal2-show' },
                                hideClass: { popup: 'swal2-hide' }
                            });
                        });
                        </script>
                    @endif

                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="date-filter-bar" id="dateFilters">
                                <i class="fa fa-calendar" style="color:#dc3545;font-size:16px;"></i>
                                <label for="start_date">วันที่ปิดตู้ (เริ่มต้น)</label>
                                @if ($date = Session::get('startdate'))
                                    <input type="text" id="start_date" value="{{$date}}" placeholder="เลือกวันที่" readonly style="cursor:pointer;">
                                    @php  session()->forget('startdate'); @endphp
                                @else
                                    <input type="text" id="start_date" placeholder="เลือกวันที่" readonly style="cursor:pointer;">
                                @endif
                                <label for="end_date">ถึง</label>
                                <input type="text" id="end_date" placeholder="วว/ดด/ปปปป" readonly style="cursor:pointer;">
                                <span style="margin-left:12px; font-size:12px; font-weight:600; color:#64748b;">ผู้รับ</span>
                                <select id="recipient_filter" style="display:none;">
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

                            <div class="text-center" style="margin-bottom:8px;">
                                <span style="font-size:13px;font-weight:700;color:#64748b;">📊 สรุปยอดรอบจัดส่ง <span id="etd_show" style="color:#dc3545;"></span></span>
                            </div>
                            <div class="stats-row">
                                <div class="stat-card">
                                    <div class="stat-label">📦 รวมทั้งหมด</div>
                                    <div class="stat-value"><span id="total_records">-</span></div>
                                    <div class="stat-unit">ชิ้น</div>
                                </div>
                                <div class="stat-card {{Session::get('hide')?'':'d-none'}}" id="weight_total_section">
                                    <div class="stat-label">⚖️ น้ำหนักรวม</div>
                                    <div class="stat-value"><span id="weight_total">-</span></div>
                                    <div class="stat-unit">kg</div>
                                </div>
                                <div class="stat-card">
                                    <div class="stat-label">💵 ค่า COD</div>
                                    <div class="stat-value"><span id="cod_total">-</span></div>
                                    <div class="stat-unit">บาท</div>
                                </div>
                                <div class="stat-card d-none">
                                    <div class="stat-label">📥 ค่านำเข้า</div>
                                    <div class="stat-value"><span id="import_cost_total">-</span></div>
                                    <div class="stat-unit">บาท</div>
                                </div>
                                <div class="stat-card stat-highlight {{Session::get('hide')?'':'d-none'}}" id="price_total_section">
                                    <div class="stat-label">💰 ยอดสุทธิ</div>
                                    <div class="stat-value"><span id="price_total">-</span></div>
                                    <div class="stat-unit">บาท</div>
                                </div>
                            </div>

                            <!-- Thai Shipping Summary Panel -->
                            <div id="thaiShipSummaryPanel" style="display:none; margin:12px 0; border:1.5px solid #e2e8f0; border-radius:12px; background:#fff; overflow:hidden;">
                                <div style="padding:14px 18px; background:linear-gradient(135deg,#f0f9ff,#eff6ff); border-bottom:1px solid #e2e8f0; cursor:pointer; display:flex; align-items:center; justify-content:space-between;" onclick="$('#thaiShipBody').slideToggle(200); $(this).find('.ts-chevron').toggleClass('fa-chevron-down fa-chevron-up');">
                                    <div style="display:flex; align-items:center; gap:10px;">
                                        <span style="font-size:15px;">🚚</span>
                                        <span style="font-weight:700; color:#0c4a6e; font-size:13px;">สรุปสถานะส่งในไทย</span>
                                        <span id="tsSummaryBadges" style="display:inline-flex; gap:6px;"></span>
                                    </div>
                                    <i class="fa fa-chevron-down ts-chevron" style="color:#94a3b8; font-size:12px;"></i>
                                </div>
                                <div id="thaiShipBody" style="padding:14px 18px; display:none;">
                                    <!-- Progress bar -->
                                    <div id="tsProgressBar" style="display:flex; height:8px; border-radius:8px; overflow:hidden; background:#f1f5f9; margin-bottom:14px;"></div>
                                    <!-- Customer chips -->
                                    <div id="tsCustomerList" style="display:flex; flex-wrap:wrap; gap:6px;"></div>
                                </div>
                            </div>

                            <input type="hidden" id="sessionSearch" />
                            <table class="table table-striped table-hover" id="dt-mant-table-1">
                                <thead class="thead">
                                    <tr>
                                        <th><input type="checkbox" id="checkAll"></th>
                                        <th>No</th>
                                        <th>วันที่</th>
                                        <th>รูปหน้ากล่อง</th>
                                        <th>รหัสลูกค้า</th>
                                        <th>เลขพัสดุ</th>
                                        <th>COD</th>
                                        <th>น้ำหนัก</th>
                                        <th>หน่วยละ</th>
                                        <th>ค่านำเข้า</th>
                                        <th>รูปสินค้า</th>
                                        <th>เลขกล่อง</th>
                                        <th>โกดัง</th>
                                        <th>วันที่ปิดตู้/เที่ยวบิน</th>
                                        <th>ประเภท</th>
                                        <th>สถานะ</th>
                                        <th>การจัดส่ง</th>
                                        <th>สถานะชำระเงิน</th>
                                        <th>บิลค่าส่งไทย</th>
                                        <th>หมายเหตุ (ลูกค้า)</th>
                                        <th>Note Admin</th>
                                        <th></th>
                                        <th>Items</th>
                                    </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
{{--                {!! $customershippings->links() !!}--}}
            </div>
        </div>
    </div>

    <!-- Floating Box Search Toggle -->
    <button class="box-search-toggle" id="boxSearchToggle" title="ค้นหาเลขกล่อง">
        <i class="fa fa-cube"></i>
    </button>

    <!-- Floating Box Search Panel -->
    <div class="box-search-panel" id="boxSearchPanel">
        <div class="panel-header">
            <span><i class="fa fa-cube"></i> ค้นหาเลขกล่อง</span>
            <button class="close-btn" id="boxSearchClose">&times;</button>
        </div>
        <div class="panel-body">
            <input type="text" id="boxNoSearch" placeholder="พิมพ์เลขกล่อง..." autocomplete="off">
            <div class="hint">ค้นหาเฉพาะเลขกล่องของรอบปิดตู้ที่เลือก</div>
            <div class="result-info" id="boxSearchResult"></div>
            <button class="btn-clear-box" id="boxSearchClear"><i class="fa fa-times"></i> ล้างการค้นหาเลขกล่อง</button>
        </div>
    </div>

    <!-- Box Image Gallery Overlay -->
    <div class="gallery-overlay" id="boxGallery">
        <button class="gallery-close" id="galleryClose">&times;</button>
        <button class="gallery-nav gallery-prev" id="galleryPrev">&#10094;</button>
        <img class="gallery-img" id="galleryImg" src="" alt="">
        <button class="gallery-nav gallery-next" id="galleryNext">&#10095;</button>
        <div class="gallery-counter" id="galleryCounter"></div>
        <div class="gallery-label" id="galleryLabel"></div>
    </div>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/th.js"></script>
    <script>
        function escHtml(s) { if (!s) return ''; var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
        $(function () {
            // ฟังก์ชันหาวันจันทร์ในอาทิตย์นั้นๆ
            function getMondayInWeek(dateString) {
                const date = new Date(dateString);
                const day = date.getDay();
                const daysSinceMonday = (day + 6) % 7;
                
                date.setDate(date.getDate() - daysSinceMonday);
                return date.toISOString().split('T')[0];
            }

            // Flatpickr common config: add ship icon on Mondays + footer buttons
            var flatpickrConfig = {
                locale: 'th',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'd/m/Y',
                disableMobile: true,
                onDayCreate: function(dObj, dStr, fp, dayElem) {
                    if (dayElem.dateObj.getDay() === 1) {
                        dayElem.classList.add('is-monday');
                    }
                },
                onReady: function(selectedDates, dateStr, fp) {
                    var footer = document.createElement('div');
                    footer.style.cssText = 'display:flex;justify-content:space-between;padding:8px 12px;border-top:1px solid #e2e8f0;';
                    var btnClear = document.createElement('button');
                    btnClear.type = 'button';
                    btnClear.textContent = 'ล้าง';
                    btnClear.style.cssText = 'background:none;border:none;color:#3b82f6;cursor:pointer;font-size:14px;font-family:inherit;padding:4px 8px;';
                    btnClear.addEventListener('click', function() {
                        fp.clear();
                        fp.close();
                        $('#shipping-export').addClass('disabled');
                        setTimeout(function() { dataTable.ajax.reload(null, false); }, 100);
                    });
                    var btnToday = document.createElement('button');
                    btnToday.type = 'button';
                    btnToday.textContent = 'วันนี้';
                    btnToday.style.cssText = 'background:none;border:none;color:#3b82f6;cursor:pointer;font-size:14px;font-family:inherit;padding:4px 8px;';
                    btnToday.addEventListener('click', function() { fp.setDate(new Date(), true); });
                    footer.appendChild(btnClear);
                    footer.appendChild(btnToday);
                    fp.calendarContainer.appendChild(footer);
                }
            };

            // Initialize Flatpickr for start_date (with red styling class)
            var startPicker = flatpickr('#start_date', Object.assign({}, flatpickrConfig, {
                altInputClass: 'form-control col-6 col-md-2 col-lg-2 fp-start-date flatpickr-input',
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        localStorage.setItem('shippingStartDate', $('#start_date').val());
                        $('#shipping-export').addClass('disabled');
                        $('#recipient_filter').val('');
                        loadAdminRecipients();
                        setTimeout(function() { dataTable.ajax.reload(null, false); }, 100);
                    }
                }
            }));

            // Initialize Flatpickr for end_date
            var endPicker = flatpickr('#end_date', Object.assign({}, flatpickrConfig, {
                onChange: function(selectedDates, dateStr) {
                    if (dateStr) {
                        $('#shipping-export').addClass('disabled');
                        setTimeout(function() { dataTable.ajax.reload(null, false); }, 100);
                    }
                }
            }));

            // ตั้งค่า default ให้แสดงวันจันทร์ล่าสุดในอาทิตย์นั้นๆ หรือวันที่ที่เลือกไว้เดิม
            function setDefaultMonday() {
                const savedDate = localStorage.getItem('shippingStartDate');
                if (savedDate) {
                    startPicker.setDate(savedDate, false);
                } else {
                    const today = new Date();
                    const mondayInWeek = getMondayInWeek(today.toISOString().split('T')[0]);
                    startPicker.setDate(mondayInWeek, false);
                }
            }
            
            setDefaultMonday();
            
            var searchTimeout;
            var lastSentSearch = null;
            $(document).on('input search', 'input[type="search"]', function() {
                $('#shipping-export').addClass('disabled');
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    var val = $.trim($('input[type="search"]').val() || '');
                    lastSentSearch = val;
                    dataTable.search(val).draw();
                }, 800);
            });
            
            var dataTable = $('#dt-mant-table-1').DataTable({
                "processing": true,
                "serverSide": true,
                "searchDelay": 99999999,
                "language": {
                    "processing": "กำลังโหลด..."
                },
                "ajax": {
                    "url": "{{ route('fetch.customershippings') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d){
                        var searchValue = $("input[type='search']").val();
                        d.search = searchValue ? $.trim(searchValue) : '';
                        lastSentSearch = d.search;
                        
                        d.status = $("select.status").val();
                        d.delivery_type_id = $("select.delivery_type_id").val();
                        d.pay_status = $("select.pay_status").val();
                        d.shipping_method = $("select.shipping_method").val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.recipient_filter = $('#recipient_filter').val();
                        d._token = "{{ csrf_token() }}";
                        d.customerno='';
                        d.box_no = $('#boxNoSearch').val() ? $.trim($('#boxNoSearch').val()) : '';
                        
                        // Log สำหรับ debug (ป้องกัน race condition)
                        // console.log('Request sent - search:', d.search);

                        // จัดการการซ่อน/แสดงคอลัมน์
                        if (d.search && d.search.toLowerCase() === 'แสดง') {
                            d.hide = 'true';
                        } else if (d.search && d.search.toLowerCase() === 'ซ่อน') {
                            d.hide = 'false';
                        }

                        // console.log('start:'+$('#start_date').val()+' end:'+$('#end_date').val())
                        // console.log('search value:', d.search);
                    }
                },
                "initComplete":function(){
                    this.api().columns([14]).every(function () {
                        var column = this;
                        var select = $('<select class="shipping_method"><option value="">ประเภท(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);
                            });
                        select.append('<option value="1">🚢 ทางเรือ</option>')
                        select.append('<option value="2">✈️ ทางเครื่องบิน</option>')
                    });

                    this.api().columns([15]).every(function () {
                        var column = this;
                        var select = $('<select class="status"><option value="">สถานะ(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                            select.append('<option value="2">อยู่ระหว่างขนส่ง</option>')
                        select.append('<option value="3">สินค้าถึงไทยแล้ว</option>')
                        select.append('<option value="4">สำเร็จ</option>')
                        // });
                    });

                    this.api().columns([16]).every(function () {
                        var column = this;
                        var select = $('<select class="delivery_type_id"><option value="">การจัดส่ง(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                        select.append('<option value="1">รับเอง</option>')
                        select.append('<option value="2">ที่อยู่ปัจจุบัน</option>')
                        select.append('<option value="3">เพิ่มที่อยู่เอง</option>')
                        select.append('<option value="2,3">ที่อยู่ปัจจุบัน + เพิ่มที่อยู่เอง</option>')
                        // });
                    });


                    this.api().columns([17]).every(function () {
                        var column = this;
                        var select = $('<select class="pay_status"><option value="">สถานะชำระเงิน(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยน filter
                                $('#shipping-export').addClass('disabled');
                                dataTable.ajax.reload(null, false);

                            });

                        select.append('<option value="1">ยังไม่ชำระเงิน</option>')
                        select.append('<option value="5">รอโอน</option>')
                        select.append('<option value="2">ชำระเงินแล้ว</option>')
                        // });
                    });

                    // โหลดค่าการค้นหาจาก session และตั้งค่าให้กับฟิลด์การค้นหา
                    console.log('test:', @json(Session::get('search') ?? ''))


                },
                "columnDefs": [
                    { "targets": 0, "data": null,"orderable": false, "render": function (data, type, full, meta) {

                                return `<input type="checkbox" value="${full.id}">`;

                        }
                        },
                    { "targets": 8, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // หน่วยละ
                    { "targets": 9, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // ค่านำเข้า
                    { "targets": 12, "visible": false }, // โกดัง (ซ่อนถาวร)
                    { "targets": 1, "data": null,title:"No","orderable": false, "render": function (data, type, full, meta) {
                            return meta.row + 1;
                        } },
                    { "targets": 2, "data": "ship_date",
                        "render": function (data, type, full, meta) {

                            return `
                            <div>${data}</div>
            <form action="${full.action_del}" method="POST" style="margin:0;">
                <div class="action-btns">
                    <a class="btn-act btn-act-edit" href="${full.edit_url}"><i class="fa fa-pencil"></i> แก้ไข</a>
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-act btn-act-del" onclick="return confirm('{{ __('คุณแน่ใจว่าต้องการจะลบข้อมูลรายการนี้?') }}')"><i class="fa fa-trash-o"></i> ลบ</button>
                </div>
            </form>
        `;
                        }}, // คอลัมน์ที่ 1
                    { "targets": 3, "data": "box_image", "render": function (data, type, full, meta) {
                            if (!data || data.trim() === '-') {
                                return '-';
                            } else {
                                return '<img src="' + data + '" class="img-thumbnail box-img" width="50" height="50" data-boxno="' + (full.box_no || '') + '" data-customer="' + (full.customerno || '') + '" onclick="openBoxGallery(this)" alt="" style="cursor: pointer;" onerror="this.onerror=null;this.src=\'/img/error-icon.png\';this.alt=\'-\';">';
                            }
                        } }, // คอลัมน์ที่ 2
                    // เพิ่มคอลัมน์ที่ต้องการให้แสดงในตารางตามลำดับที่เป็นไปตามลิสต์ของคุณ
                    { "targets": 4, "data": "customerno", "render": function(data, type, row) {
                            return '<span class="customer-name">' + data + '</span><span class="channel-icon" data-customerno="' + data + '"></span>';
                        }
                    },
                    { "targets": 5, "data": "track_no" },
                    { "targets": 6, "data": "cod" },
                    { "targets": 7, "data": "weight" },
                    // { "targets": 8, "data": "unit_price" },
                    { "targets": 8, "data": "unit_price", "render": function (data, type, full, meta) {
                            // if (!data || (data > 180.00||data===0||data===0.00) ) {
                            if (full.iswholeprice===1) {
                                return 'ราคาเหมา';
                            } else {
                                return data;
                            }
                        } },
                    { "targets": 9, "data": "import_cost" },
                    { "targets": 10, "data": "product_image", "render": function (data, type, full, meta) {
                            if (!data || data.trim() === '-') {
                                return '-';
                            } else {
                                return '<img src="' + data + '" class="img-thumbnail" width="50" height="50" onclick="showImage(\'' + data + '\')" alt="" style="cursor: pointer;" onerror="this.onerror=null;this.src=\'/img/error-icon.png\';this.alt=\'-\';">';
                            }
                        } },
                    { "targets": 11, "data": "box_no" },
                    { "targets": 12, "data": "warehouse" },
                    { "targets": 13, "data": "etd" },
                    { "targets": 14, "data": "shipping_method_label", "orderable": false, "render": function(data, type, row) {
                            var method = row.shipping_method || 1;
                            if (method == 2) {
                                return '<span style="display:inline-block;padding:4px 10px;border-radius:20px;background:#eff6ff;color:#2563eb;font-size:11px;font-weight:700;white-space:nowrap;">✈️ เครื่องบิน</span>';
                            }
                            return '<span style="display:inline-block;padding:4px 10px;border-radius:20px;background:#f0fdf4;color:#16a34a;font-size:11px;font-weight:700;white-space:nowrap;">🚢 เรือ</span>';
                        }
                    },
                    { "targets": 15, "data": "status","orderable": false, "render": function(data, type, row) {
                            if (!data) return '-';
                            var colors = {
                                'รอดำเนินการ': {bg:'#f1f5f9',color:'#64748b'},
                                'อยู่ระหว่างขนส่ง': {bg:'#fef2f2',color:'#dc2626'},
                                'สินค้าถึงไทยแล้ว': {bg:'#dcfce7',color:'#16a34a'},
                                'สำเร็จ': {bg:'#fdf2f8',color:'#ec4899'}
                            };
                            var c = colors[data] || {bg:'#f1f5f9',color:'#64748b'};
                            return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;background:'+c.bg+';color:'+c.color+';font-size:11px;font-weight:700;white-space:nowrap;">'+data+'</span>';
                        }
                    },
                    { "targets": 16, "data": "delivery_type_name", orderable:false, "render": function(data, type, row) {
                        var html = escHtml(data);
                        if (row.delivery_type_id != 1 && row.delivery_fullname && row.delivery_fullname.trim()) {
                            html += '<br><span style="color:#dc2626;font-size:10px;font-weight:600;"><i class="fa fa-user"></i> ' + escHtml(row.delivery_fullname) + '</span>';
                        }
                        return html;
                    }},
                    {
                        "targets": 17,
                        "data": "pay_status",
                        "orderable": false,
                        "render": function(data, type, row) {
                            let statusClass = '';
                            switch(data) {
                                case 'ยังไม่ชำระเงิน':
                                    statusClass = 'danger';
                                    break;
                                case 'รอโอน':
                                    statusClass = 'warning';
                                    break;
                                case 'ชำระเงินแล้ว':
                                    statusClass = 'success';
                                    break;
                            }

                            var amt = '';
                            if (data !== 'ยังไม่ชำระเงิน' && row.invoice_group_total && row.invoice_group_total !== '-') {
                                amt = '<div style="font-size:10px;color:#555;margin-top:2px;">฿' + row.invoice_group_total + '</div>';
                            }

                            return '<div class="status-container" style="text-align:center;">' +
                                        '<span title="' + data + '" class="dot bg-' + statusClass + '"></span>' +
                                        amt +
                                    '</div>';
                        }
                    },
                    {
                        "targets": 18,
                        "data": "thai_bill_status",
                        "orderable": false,
                        "render": function(data, type, row) {
                            if (!data || data === '-') return '<span style="color:#ccc;">-</span>';
                            var colors = {
                                'รอโอน': {bg:'#FFF3E0',color:'#E65100',border:'#FFB74D'},
                                'โอนแล้ว': {bg:'#E8F5E9',color:'#2E7D32',border:'#81C784'}
                            };
                            var c = colors[data] || {bg:'#eee',color:'#666',border:'#ccc'};
                            var amt = row.thai_bill_amount_display && row.thai_bill_amount_display !== '-' ? '<br><small>฿'+row.thai_bill_amount_display+'</small>' : '';
                            return '<span style="display:inline-block;padding:3px 10px;border-radius:12px;background:'+c.bg+';color:'+c.color+';border:1px solid '+c.border+';font-size:11px;font-weight:600;text-align:center;">'+data+amt+'</span>';
                        }
                    },
                    { "targets": 19, "data": "note" },
                    { "targets": 20, "data": "note_admin" },
                    {
                        "targets": 21,
                        "data": null,
                        "orderable": false,visible:false,
                        "render": function (data, type, full, meta) {

                            return `
            <form action="${full.action_del}" method="POST">
                <a class="btn btn-sm btn-success" href="${full.edit_url}"><i class="fa fa-fw fa-edit"></i> {{ __('Edit') }}</a>
                @csrf

                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('{{ __('คุณแน่ใจว่าต้องการจะลบข้อมูลรายการนี้?') }}')" ><i class="fa fa-fw fa-trash"></i> {{ __('Delete') }}</button>
            </form>
        `;
                        }
                    },

                    { "targets": 22, "data": "itemno" },

                ],
                "lengthMenu": [100, 200, 300, 500, 1000, 1500],
                "pageLength": 100,
                "order": [
                    // [13, 'desc'], // เรียงลำดับตาม etd ล่าสุด (desc)
                    // [4, 'asc']    // เรียงลำดับตาม customerno (asc)
                ]
            });

            @if ($search = Session::get('search'))
            var initSearch = @json($search);
            @else
            var initSearch = 'ANW-';
            @endif
            $("input[type='search']").val(initSearch);
            $("#sessionSearch").val(initSearch);
            lastSentSearch = $.trim(initSearch);
            dataTable.search(initSearch).draw();

            // (date change handled by Flatpickr onChange above)
            // สร้างตัวแปรเพื่อเก็บสถานะการโหลดครั้งแรกและค่าการค้นหาก่อนหน้า
            var initialLoad = true;
            var previousSearchValue = ''; // เก็บค่าการค้นหาก่อนหน้า
            var previousStartDate = '';
            
            // เพิ่มตัวแปรเพื่อป้องกัน race condition
            var pendingRequestId = null; // เก็บ request ID ที่กำลังรอ response (สำรองไว้)


            dataTable.on('xhr.dt', function(e, settings, json, xhr) {
                var currentSearch = $.trim($("input[type='search']").val());
                if (lastSentSearch !== null && lastSentSearch !== currentSearch) {
                    return;
                }
                $('#data-export').attr('href',json.data_export_link);
                $('#shipping-export').attr('href',json.shipping_export_link);
                pendingRequestId = null; // clear pending request
                
                // อัพเดทยอดรวมทุกครั้งที่ AJAX response กลับมา (เฉพาะ response ล่าสุด)
                console.log('Response Data:', json);
                $('#etd_show').text(json.start_date);
                $('#cod_total').text(json.cod_total);
                $('#weight_total').text(json.weight_total);
                $('#total_records').text(json.total_records);
                $('#import_cost_total').text(json.import_cost_total);
                $('#price_total').text(json.price_total);
                
                if (initialLoad || settings.oPreviousSearch.sSearch !== previousSearchValue || $('#start_date').val() !== previousStartDate) {
                    initialLoad = false;
                    previousSearchValue = settings.oPreviousSearch.sSearch; // อัปเดตค่าการค้นหาก่อนหน้า
                    previousStartDate = $('#start_date').val(); // อัปเดตค่า start_date ก่อนหน้า

                    // $('#sessionSearch').val('');
                    initialLoad = false; // ตั้งค่า initialLoad เป็น false หลังจากการโหลดครั้งแรก
                    console.log('initialSearch:', initialLoad);
                }
                
                // เปิดใช้งานปุ่ม Export เมื่อข้อมูลโหลดเสร็จ (ทุกครั้ง)
                updateExportButtonState();

                // โหลดสรุปสถานะส่งในไทย เมื่อเลือกรอบปิดตู้
                var etdVal = $('#start_date').val();
                if (etdVal) {
                    loadThaiShippingSummary(etdVal);
                    loadAdminRecipients();
                } else {
                    $('#thaiShipSummaryPanel').hide();
                }
                
                // ไม่ต้องอัพเดท total_records จาก DataTable เพราะใช้ server-side processing
                // ค่าจาก server (json.total_records) ถูกต้องอยู่แล้ว
                // อัพเดท total_records ให้ตรงกับจำนวนแถวที่แสดงจริงในตาราง
                // setTimeout(function() {
                //     var visibleRows = dataTable.rows({page: 'current'}).count();
                //     var totalFilteredRows = dataTable.rows({search: 'applied'}).count();
                //     $('#total_records').text(totalFilteredRows);
                // }, 100);
                
                var preservedSearch = sessionStorage.getItem('preservedSearch');
                if (preservedSearch) {
                    $("input[type='search']").val(preservedSearch);
                    lastSentSearch = $.trim(preservedSearch);
                    dataTable.search(preservedSearch).draw();
                    sessionStorage.removeItem('preservedSearch');
                }

            });

            // === ดึง icon ช่องทาง จาก SKJ Chat API หลัง DataTable วาดเสร็จ ===
            var channelCache = {}; // cache: { 'ANW-500': { connected: true, name: '...' }, ... }
            var chatIconLine = '<svg title="LINE" width="15" height="15" viewBox="0 0 24 24" style="vertical-align:middle;margin-left:4px;cursor:help;"><path fill="#06C755" d="M12 2C6.48 2 2 5.83 2 10.5c0 4.08 3.63 7.49 8.53 8.14.33.07.78.22.89.5.1.26.07.66.03.92l-.14.87c-.04.26-.2 1.02.89.56s5.93-3.5 8.09-5.99C22.17 13.46 22 11.97 22 10.5 22 5.83 17.52 2 12 2z"/></svg>';
            var chatIconFB = '<svg title="Facebook" width="15" height="15" viewBox="0 0 24 24" style="vertical-align:middle;margin-left:4px;cursor:help;"><path fill="#1877F2" d="M24 12.07C24 5.41 18.63 0 12 0S0 5.41 0 12.07c0 6.02 4.39 11.01 10.13 11.93v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.8-4.69 4.54-4.69 1.31 0 2.68.23 2.68.23v2.97h-1.51c-1.49 0-1.95.93-1.95 1.88v2.27h3.33l-.53 3.49h-2.8v8.44C19.61 23.08 24 18.09 24 12.07z"/></svg>';

            function applyChannelIcons() {
                $('.channel-icon').each(function() {
                    var cn = $(this).data('customerno');
                    if (!cn) return;
                    var key = cn.toString().toUpperCase();
                    var info = channelCache[key];
                    if (info && info.connected) {
                        var icon = (info.platform === 'facebook') ? chatIconFB : chatIconLine;
                        $(this).html('<span title="' + (info.name || '') + '">' + icon + '</span>');
                    }
                });
            }

            dataTable.on('draw.dt', function() {
                var uncached = [];
                $('.channel-icon').each(function() {
                    var cn = $(this).data('customerno');
                    if (!cn) return;
                    var key = cn.toString().toUpperCase();
                    if (channelCache[key] === undefined && uncached.indexOf(key) === -1) {
                        uncached.push(key);
                    }
                });
                // ใส่ icon จาก cache ก่อน
                applyChannelIcons();
                // เรียก SKJ Chat API สำหรับรายการที่ยังไม่ cache
                if (uncached.length > 0) {
                    $.ajax({
                        url: "{{ route('check.chat.connection') }}",
                        type: 'POST',
                        data: { customer_nos: uncached, _token: "{{ csrf_token() }}" },
                        timeout: 10000,
                        success: function(resp) {
                            var results = resp.results || {};
                            uncached.forEach(function(cn) {
                                var info = results[cn] || results[cn.toLowerCase()] || null;
                                if (info && info.connected) {
                                    channelCache[cn] = { connected: true, name: info.contactName || '', platform: info.platform || 'line' };
                                } else {
                                    channelCache[cn] = { connected: false };
                                }
                            });
                            applyChannelIcons();
                        },
                        error: function() { /* silent fail */ }
                    });
                }
            });

            // อัพเดท total_records หลังจาก DataTable ทำการค้นหาเสร็จ
            dataTable.on('search.dt', function() {
                // ไม่ต้องอัพเดท total_records เพราะใช้ server-side processing
                // ค่าจาก server จะถูกอัพเดทใน xhr.dt event
                // var filteredData = dataTable.rows({search: 'applied'}).data();
                // $('#total_records').text(filteredData.length);
                
                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการค้นหาใหม่
                $('#shipping-export').addClass('disabled');
                
                // ตรวจสอบการพิมพ์ "แสดง" หรือ "ซ่อน"
                var searchValue = $("input[type='search']").val().toLowerCase();
                if (searchValue === 'แสดง' || searchValue === 'ซ่อน') {
                    // รีเฟรชหน้าเพื่อให้ session ถูกอัพเดท
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                }
            });

            // อัพเดท total_records เมื่อมีการเปลี่ยนหน้า
            dataTable.on('page.dt', function() {
                // ไม่ต้องอัพเดท total_records เพราะใช้ server-side processing
                // ค่าจาก server จะถูกอัพเดทใน xhr.dt event
                // var filteredData = dataTable.rows({search: 'applied'}).data();
                // $('#total_records').text(filteredData.length);
                
                // ปิดปุ่ม SHIPPING EXPORT EXCEL เมื่อมีการเปลี่ยนหน้า
                $('#shipping-export').addClass('disabled');
            });


            // // เพิ่มฟีเจอร์ Check All
            // $('#checkAllButton').on('click', function() {
            //     $(':checkbox', dataTable.rows().nodes()).prop('checked', true);
            // });
            //
            // $('#uncheckAllButton').on('click', function() {
            //     $(':checkbox', dataTable.rows().nodes()).prop('checked', false);
            // });
            //
            $('#checkAll').on('change', function() {
                $(':checkbox', dataTable.rows().nodes()).prop('checked', $(this).prop('checked'));
            });

            // หากมีการเลือก checkbox ใดๆ, ตรวจสอบว่าควรเปิดหรือปิดปุ่ม Check All
            $('#dt-mant-table-1 tbody').on('change', ':checkbox', function() {
                var allChecked = $(':checkbox', dataTable.rows().nodes()).length === $(':checkbox:checked', dataTable.rows().nodes()).length;
                $('#checkAll').prop('checked', allChecked);
            });


            $('#updateSelected,#updateSelected2,#updateSelected3,#updateSelected4').on('click', function(e) {

                var selectedRows = $('tbody').find(':checkbox:checked');
                console.log(selectedRows.length);
                if (selectedRows.length > 0) {
                    var selectedIds = [];
                    selectedRows.each(function() {
                        selectedIds.push($(this).val());
                    });
                    $('#trackIdsInput').val(selectedIds.join(','));
                    $('#trackIdsInput2').val(selectedIds.join(','));
                    $('#trackIdsInput3').val(selectedIds.join(','));
                    $('#trackIdsInput4').val(selectedIds.join(','));
                    
                    // เก็บค่าการค้นหาปัจจุบันไว้
                    var currentSearch = $("input[type='search']").val();
                    if (currentSearch) {
                        // เก็บค่าการค้นหาไว้ใน session storage
                        sessionStorage.setItem('preservedSearch', currentSearch);
                    }

                } else {
                    e.preventDefault();
                    alert("กรุณาเลือกรายการที่ต้องการอัพเดท");
                }
            });

            // เพิ่มฟังก์ชันสำหรับตรวจสอบและอัพเดทสถานะปุ่ม Invoice
            function updateInvoiceButtonState() {
                var $invoiceBtn = $('#invoiceBtn');
                var $btn1 = $('#updateSelected');
                var $btn2 = $('#updateSelected2');
                var $btn3 = $('#updateSelected3');
                var $btn4 = $('#updateSelected4');
                var selectedRows = $('tbody').find(':checkbox:checked');
                
                if (selectedRows.length > 0) {
                    $invoiceBtn.removeClass('disabled');
                    $btn1.removeClass('disabled');
                    $btn2.removeClass('disabled');
                    $btn3.removeClass('disabled');
                    $btn4.removeClass('disabled');
                } else {
                    $invoiceBtn.addClass('disabled');
                    $btn1.addClass('disabled');
                    $btn2.addClass('disabled');
                    $btn3.addClass('disabled');
                    $btn4.addClass('disabled');
                }
            }

            // เพิ่ม event listeners
            $(document).on('change', ':checkbox', updateInvoiceButtonState);
            $("select.status").on('change', updateInvoiceButtonState);
            $("select.delivery_type_id").on('change', updateInvoiceButtonState);
            $("select.pay_status").on('change', updateInvoiceButtonState);
            
            // เรียกใช้ฟังก์ชันครั้งแรกเมื่อโหลดหน้า
            updateInvoiceButtonState();
            
            // ฟังก์ชันอัพเดทสถานะปุ่ม SHIPPING EXPORT EXCEL
            function updateExportButtonState() {
                var $shippingExportBtn = $('#shipping-export');
                
                // เปิดใช้งานปุ่มเมื่อข้อมูลโหลดเสร็จ
                $shippingExportBtn.removeClass('disabled');
            }

            // === โหลดรายชื่อผู้รับสำหรับ filter dropdown ===
            var _lastRecipientEtd = null;
            var _lastRecipientSearch = null;
            function loadAdminRecipients(force) {
                var etd = $('#start_date').val();
                var search = $.trim($("input[type='search']").val());
                if (!force && etd === _lastRecipientEtd && search === _lastRecipientSearch) return;
                _lastRecipientEtd = etd;
                _lastRecipientSearch = search;
                $.ajax({
                    url: "{{ route('fetch.admin.recipients') }}",
                    type: "POST",
                    data: { etd: etd, search: search, _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        var sel = $('#recipient_filter');
                        var currentVal = sel.val();
                        sel.find('option:not(:first)').remove();
                        var list = $('#recipientList');
                        list.find('.dd-item:not(:first)').remove();
                        if (res.recipients && res.recipients.length > 0) {
                            res.recipients.forEach(function(r) {
                                sel.append('<option value="' + r.value + '">' + r.label + ' (' + r.count + ')</option>');
                                list.append('<div class="dd-item" data-value="' + r.value + '">' + r.label + ' (' + r.count + ')</div>');
                            });
                        }
                        if (currentVal) {
                            sel.val(currentVal);
                            syncDropdownHighlight(currentVal);
                        }
                    }
                });
            }

            // === Custom Recipient Dropdown ===
            $('#recipientToggle').on('click', function(e) {
                e.stopPropagation();
                var menu = $('#recipientMenu');
                menu.toggleClass('open');
                if (menu.hasClass('open')) {
                    $('#recipientSearch').val('').trigger('input');
                    setTimeout(function(){ $('#recipientSearch').focus(); }, 50);
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('#recipientDropdown').length) {
                    $('#recipientMenu').removeClass('open');
                }
            });

            $('#recipientSearch').on('input', function() {
                var q = $(this).val().toLowerCase();
                $('#recipientList .dd-item').each(function() {
                    var text = $(this).text().toLowerCase();
                    $(this).toggle(text.indexOf(q) !== -1);
                });
            });

            $(document).on('click', '#recipientList .dd-item', function() {
                var val = $(this).data('value');
                var label = $(this).text();
                $('#recipient_filter').val(val === undefined ? '' : val).trigger('change');
                $('#recipientToggle').text(label);
                $('#recipientList .dd-item').removeClass('active');
                $(this).addClass('active');
                $('#recipientMenu').removeClass('open');
            });

            function syncDropdownHighlight(val) {
                $('#recipientList .dd-item').removeClass('active');
                $('#recipientList .dd-item').filter(function() {
                    return String($(this).data('value')) === String(val);
                }).addClass('active');
                var activeItem = $('#recipientList .dd-item.active');
                if (activeItem.length) {
                    $('#recipientToggle').text(activeItem.text());
                }
            }

            $(document).on('change', '#recipient_filter', function() {
                $('#shipping-export').addClass('disabled');
                syncDropdownHighlight($(this).val());
                dataTable.ajax.reload(null, false);
            });

            // ป้องกันเลื่อนเม้าส์เปลี่ยนค่า dropdown
            $(document).on('wheel', '#recipient_filter', function(e) { e.preventDefault(); });

            // === สรุปสถานะส่งในไทย ===
            function loadThaiShippingSummary(etd) {
                $.ajax({
                    url: "{{ route('fetch.thai.shipping.summary') }}",
                    type: "GET",
                    data: { etd: etd },
                    success: function(res) {
                        if (!res.success || !res.customers || res.customers.length === 0) {
                            $('#thaiShipSummaryPanel').hide();
                            return;
                        }
                        var s = res.summary;
                        // Summary badges
                        var badges = '';
                        if (s.pending > 0) badges += '<span style="background:#fef2f2;color:#dc2626;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;">⏳ ยังไม่ส่ง ' + s.pending + '</span>';
                        if (s.partial > 0) badges += '<span style="background:#fffbeb;color:#d97706;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;">🔶 ส่งบางส่วน ' + s.partial + '</span>';
                        if (s.done > 0) badges += '<span style="background:#f0fdf4;color:#16a34a;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;">✅ เสร็จแล้ว ' + s.done + '</span>';
                        $('#tsSummaryBadges').html(badges);

                        // Progress bar
                        var shipTotal = (s.done + s.partial + s.pending) || 1;
                        var pctDone = Math.round(s.done / shipTotal * 100);
                        var pctPartial = Math.round(s.partial / shipTotal * 100);
                        var pctPending = 100 - pctDone - pctPartial;
                        if (s.done + s.partial + s.pending === 0) { pctDone = 0; pctPartial = 0; pctPending = 0; }
                        $('#tsProgressBar').html(
                            '<div style="width:' + pctDone + '%;background:#22c55e;transition:width 0.3s;"></div>' +
                            '<div style="width:' + pctPartial + '%;background:#f59e0b;transition:width 0.3s;"></div>' +
                            '<div style="width:' + pctPending + '%;background:#ef4444;transition:width 0.3s;"></div>'
                        );

                        // Customer chips
                        var html = '';
                        res.customers.forEach(function(c) {
                            var bg, color, icon, border, detail;
                            // สร้าง detail แยกแต่ละส่วน
                            var parts = [];
                            if (c.need_ship > 0) parts.push('ส่งแล้ว ' + c.billed + '/' + c.need_ship);
                            if (c.pickup_done > 0) parts.push('รับแล้ว ' + c.pickup_done);
                            if (c.pickup_wait > 0) parts.push('รอรับ ' + c.pickup_wait);

                            if (c.status === 'done') {
                                bg = '#f0fdf4'; color = '#16a34a'; icon = '✅'; border = '#bbf7d0';
                                detail = parts.join(' | ') || 'เสร็จแล้ว';
                            } else if (c.status === 'partial') {
                                bg = '#fffbeb'; color = '#d97706'; icon = '🔶'; border = '#fde68a';
                                detail = parts.join(' | ');
                            } else {
                                bg = '#fef2f2'; color = '#dc2626'; icon = '⏳'; border = '#fecaca';
                                detail = parts.join(' | ') || 'ยังไม่ดำเนินการ';
                            }
                            html += '<div style="display:inline-flex;align-items:center;gap:4px;padding:6px 12px;border-radius:8px;background:' + bg + ';border:1px solid ' + border + ';font-size:12px;font-weight:600;color:' + color + ';">';
                            html += icon + ' ' + c.customerno;
                            html += '<span style="font-weight:400;font-size:11px;margin-left:2px;">(' + c.total + ' ชิ้น | ' + detail + ')</span>';
                            html += '</div>';
                        });
                        $('#tsCustomerList').html(html);
                        $('#thaiShipSummaryPanel').show();
                    },
                    error: function() {
                        $('#thaiShipSummaryPanel').hide();
                    }
                });
            }

            // เพิ่ม event handler สำหรับปุ่ม Invoice
            $('#invoiceBtn').on('click', function(e) {
                e.preventDefault();
                
                var selectedRows = $('tbody').find(':checkbox:checked');
                
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการพิมพ์ Invoice ก่อน');
                    return false;
                }
                
                var selectedIds = [];
                var customerno = '';
                selectedRows.each(function() {
                    selectedIds.push($(this).val());
                    if (!customerno) {
                        var row = dataTable.row($(this).closest('tr'));
                        var data = row.data();
                        if (data && data.customerno) customerno = data.customerno;
                    }
                });

                if (!customerno) {
                    customerno = $.trim($("input[type='search']").val());
                }
                if (!customerno) {
                    alert('ไม่พบรหัสลูกค้า กรุณาเลือกรายการหรือค้นหาลูกค้าก่อน');
                    return false;
                }
                
                var startDate = $('#start_date').val();
                
                var url = "{{ route('invoice.generate', ['etd' => ':etd', 'customerno' => ':customerno', 'shipping_ids' => ':shipping_ids']) }}";
                url = url.replace(':etd', startDate)
                         .replace(':customerno', customerno)
                         .replace(':shipping_ids', selectedIds.join(','));
                         
                console.log('Invoice URL:', url);
                window.open(url, '_blank');
            });
            
            // เพิ่ม event handler สำหรับปุ่ม SHIPPING EXPORT EXCEL
            $('#shipping-export').on('click', function(e) {
                e.preventDefault();
                
                if ($(this).hasClass('disabled')) {
                    alert('กรุณารอให้ข้อมูลโหลดเสร็จก่อน');
                    return false;
                }
               
                // สร้าง URL ใหม่จากค่าปัจจุบันในช่องค้นหาเสมอ เพื่อความถูกต้อง
                var searchVal = $.trim($("input[type='search']").val()) || '';
                var startDate = $('#start_date').val() || '';
                
                // สร้าง URL ใหม่โดยไม่พึ่งพา href ที่มีอยู่ เพื่อให้แน่ใจว่าใช้ค่าปัจจุบัน
                var baseUrl = "{{ url('customershippingsexport') }}";
                var exportUrl = baseUrl + '/' + startDate + (searchVal ? '?customerno=' + encodeURIComponent(searchVal) : '');
                
                console.log('Export URL:', exportUrl, 'Search Value:', searchVal);
                
                if (exportUrl) {
                    window.open(exportUrl, '_blank');
                    // $('input[type="search"]').val('');
                }
            });

            // Export PDF LABEL
            $('#btn-export-labels').on('click', function(e) {
                e.preventDefault();
                var startDate = $('#start_date').val();
                if (!startDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }
                var url = "{{ url('customershippings-labels') }}/" + startDate;
                window.open(url, '_blank');
            });

            // Export Pile Labels (ANW-820)
            $('#btn-pile-labels').on('click', function(e) {
                e.preventDefault();
                var startDate = $('#start_date').val();
                if (!startDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }
                var url = "{{ url('customershippings-pile-labels') }}/" + startDate;
                window.open(url, '_blank');
            });

            // LINE Notification
            $('#btn-line-notify').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                // เก็บ customerno จากแถวที่ติ๊กถูก
                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการแจ้งเตือนก่อน (ติ๊กถูกด้านซ้าย)');
                    return;
                }

                // ดึง customerno ที่ไม่ซ้ำจากแถวที่เลือก
                var customerNos = [];
                selectedRows.each(function() {
                    var row = dataTable.row($(this).closest('tr'));
                    var data = row.data();
                    if (data && data.customerno && customerNos.indexOf(data.customerno) === -1) {
                        customerNos.push(data.customerno);
                    }
                });

                // แปลงวันที่เป็น dd/mm/yyyy สำหรับแสดง
                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();

                if (!confirm('ต้องการส่ง LINE แจ้งเตือนลูกค้า ' + customerNos.length + ' ราย\n(' + customerNos.join(', ') + ')\nรอบปิดตู้วันที่ ' + displayDate + ' ใช่หรือไม่?')) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่ง...');

                $.ajax({
                    url: "{{ route('send.line.notification') }}",
                    type: 'POST',
                    data: {
                        etd: etdDate,
                        customer_nos: customerNos,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-commenting"></i> LINE แจ้งเตือน');

                        if (response.results && response.results.details) {
                            var msg = response.message + '\n\n--- รายละเอียด ---\n';
                            response.results.details.forEach(function(d) {
                                var icon = d.status === 'success' ? '✅' : (d.status === 'already_sent' ? '⏭️' : '❌');
                                msg += icon + ' ' + d.customerno + ': ' + d.message + '\n';
                            });
                            alert(msg);
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-commenting"></i> LINE แจ้งเตือน');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        alert(errMsg);
                    }
                });
            });

            // === Invoice Chat helpers ===
            // คลิก variable chip → แทรกตัวแปรเข้า textarea
            $(document).on('click', '.ic-var', function() {
                var v = $(this).data('var');
                var el = document.getElementById('invoiceChatMessageTemplate');
                if (!el) return;
                var s = el.selectionStart, e = el.selectionEnd;
                var val = el.value;
                el.value = val.substring(0, s) + v + val.substring(e);
                el.selectionStart = el.selectionEnd = s + String(v).length;
                el.focus();
                autoGrowIcMessage();
            });

            // ค้นหารายชื่อใน list
            $(document).on('input', '#invoiceChatSearch', function() {
                var q = $(this).val().toLowerCase().trim();
                $('#invoiceChatCustomerList .ic-row').each(function() {
                    var txt = $(this).text().toLowerCase();
                    $(this).toggle(!q || txt.indexOf(q) !== -1);
                });
            });

            // auto-grow textarea (เหมือน broadcast)
            function autoGrowIcMessage() {
                var el = document.getElementById('invoiceChatMessageTemplate');
                if (!el) return;
                el.style.height = 'auto';
                var newH = Math.min(Math.max(el.scrollHeight + 2, 260), 520);
                el.style.height = newH + 'px';
            }
            $(document).on('input', '#invoiceChatMessageTemplate', autoGrowIcMessage);
            $('#invoiceChatModal').on('shown.bs.modal', autoGrowIcMessage);

            // === Invoice Result Modal (Swal style with filter chips + search) ===
            window.showInvoiceResultModal = function(summary, details, counts) {
                function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }
                var order = { failed: 0, not_found: 1, partial: 2, success: 3, paid: 4, unknown: 5 };
                var sorted = (details || []).slice().sort(function(a, b) {
                    var oa = order[a.status] != null ? order[a.status] : 6;
                    var ob = order[b.status] != null ? order[b.status] : 6;
                    if (oa !== ob) return oa - ob;
                    return String(a.customerno || '').localeCompare(String(b.customerno || ''), undefined, { numeric: true });
                });

                function rowHtml(d) {
                    var icon, color, label;
                    if (d.status === 'success')        { icon='✅'; color='#10b981'; label='สำเร็จ'; }
                    else if (d.status === 'partial')   { icon='⚠️'; color='#f59e0b'; label='บางส่วน'; }
                    else if (d.status === 'not_found') { icon='🔍'; color='#d97706'; label='ไม่พบในแชท'; }
                    else if (d.status === 'paid')      { icon='💰'; color='#0e7490'; label='ชำระแล้ว'; }
                    else                                { icon='❌'; color='#ef4444'; label='ไม่สำเร็จ'; }
                    return '<div class="ic-result-row" data-status="'+d.status+'" '
                        +'style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-bottom:1px solid #f1f5f9; font-size:13px;">'
                        +'<span style="flex:0 0 28px;">'+icon+'</span>'
                        +'<span style="flex:0 0 110px; font-family:\'SF Mono\',monospace; font-weight:600; color:#0c5e8e;">'+esc(d.customerno)+'</span>'
                        +'<span style="flex:0 0 110px; color:'+color+'; font-weight:600;">'+label+'</span>'
                        +'<span style="flex:1; color:#475569; word-break:break-word;">'+esc(d.message || '')+'</span>'
                        +'</div>';
                }

                var total = (counts.success||0) + (counts.partial||0) + (counts.not_found||0) + (counts.failed||0);
                var hasError = (counts.failed||0) > 0 || (counts.not_found||0) > 0 || (counts.partial||0) > 0;

                function chip(label, status, count, color) {
                    return '<button type="button" class="ic-filter-chip" data-filter="'+status+'" '
                        + 'style="background:#fff; color:'+color+'; border:1px solid '+color+'; border-radius:18px; padding:5px 14px; font-size:13px; font-weight:600; cursor:pointer;">'+label+' ('+count+')</button>';
                }

                var chipsHtml = '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px;">'
                    + '<button type="button" class="ic-filter-chip" data-filter="all" style="background:#0084FF; color:#fff; border:0; border-radius:18px; padding:5px 14px; font-size:13px; font-weight:600; cursor:pointer;">ทั้งหมด ('+total+')</button>'
                    + chip('✅ สำเร็จ', 'success', counts.success||0, '#10b981')
                    + (counts.partial   ? chip('⚠️ บางส่วน',    'partial',   counts.partial,   '#f59e0b') : '')
                    + (counts.not_found ? chip('🔍 ไม่พบในแชท', 'not_found', counts.not_found, '#d97706') : '')
                    + (counts.failed    ? chip('❌ ไม่สำเร็จ',   'failed',    counts.failed,    '#ef4444') : '')
                    + '</div>';

                var searchHtml = '<input type="text" id="icResultSearch" placeholder="🔍 ค้นหา ANW-xxx..." '
                    + 'style="width:100%; padding:6px 12px; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:10px; font-size:13px;">';

                var headerHtml = '<div style="color:#475569; font-size:13px; margin-bottom:10px; padding:10px 12px; background:#f8fafc; border-radius:8px;">'
                    + esc(summary) + '</div>';

                var listHtml = '<div id="icResultList" style="max-height:380px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px; background:#fff;">' + sorted.map(rowHtml).join('') + '</div>';

                Swal.fire({
                    icon: hasError ? 'warning' : 'success',
                    title: hasError ? 'ส่งบิลเสร็จสิ้น (มีบางรายไม่สำเร็จ)' : 'ส่งบิลสำเร็จ',
                    html: headerHtml + chipsHtml + searchHtml + listHtml,
                    width: '760px',
                    confirmButtonColor: '#0084FF',
                    confirmButtonText: 'ปิด',
                    didOpen: function() {
                        $(document).off('click.icfilter').on('click.icfilter', '.ic-filter-chip', function() {
                            var f = $(this).data('filter');
                            $('.ic-filter-chip').each(function() {
                                var fx = $(this).data('filter');
                                var color = fx === 'success' ? '#10b981' : (fx === 'partial' ? '#f59e0b' : (fx === 'not_found' ? '#d97706' : (fx === 'failed' ? '#ef4444' : '#0084FF')));
                                if (fx === f) { $(this).css({ background: color, color: '#fff', borderColor: color }); }
                                else { $(this).css({ background: '#fff', color: color, borderColor: color, border: fx === 'all' ? '0' : '1px solid '+color }); }
                            });
                            $('.ic-result-row').each(function() {
                                var st = $(this).data('status');
                                $(this).toggle(f === 'all' || st === f);
                            });
                        });
                        $(document).off('input.icsearch').on('input.icsearch', '#icResultSearch', function() {
                            var q = $(this).val().toLowerCase().trim();
                            $('.ic-result-row').each(function() {
                                var txt = $(this).text().toLowerCase();
                                $(this).toggle(!q || txt.indexOf(q) !== -1);
                            });
                        });
                    }
                });
            };

            // === ส่งบิลผ่านแชท (SKJ Chat) ===
            $('#btn-send-invoice-chat').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการส่งบิลก่อน (ติ๊กถูกด้านซ้าย)');
                    return;
                }

                // ดึง customerno + shipping_ids ที่เลือก
                var customerMap = {}; // { customerno: [id1, id2, ...] }
                selectedRows.each(function() {
                    var row = dataTable.row($(this).closest('tr'));
                    var data = row.data();
                    if (data && data.customerno) {
                        var cn = data.customerno;
                        if (!customerMap[cn]) customerMap[cn] = [];
                        if (data.id) customerMap[cn].push(data.id);
                    }
                });

                var customerNos = Object.keys(customerMap);
                if (customerNos.length === 0) {
                    alert('ไม่พบรหัสลูกค้าจากรายการที่เลือก');
                    return;
                }

                // เรียงรหัสลูกค้าจากน้อย → มาก แบบ natural sort (ANW-9 ก่อน ANW-10)
                customerNos.sort(function(a, b) {
                    return String(a).localeCompare(String(b), undefined, { numeric: true, sensitivity: 'base' });
                });

                // เก็บ customerMap ไว้ใน modal data
                $('#invoiceChatModal').data('customerMap', customerMap);

                // แปลงวันที่
                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();

                // แสดงรายชื่อในรายการ modal พร้อมจำนวนชิ้น (loading state ก่อน)
                var listHtml = '';
                customerNos.forEach(function(cn) {
                    var count = customerMap[cn].length;
                    var safeId = cn.replace(/[^a-zA-Z0-9-]/g, '_');
                    listHtml += '<label class="ic-row" id="chat-row-' + safeId + '" data-cn="' + cn + '" '
                        + 'style="display:flex; align-items:center; gap:8px; padding:7px 10px; cursor:pointer; '
                        + 'background:#fff; border:1px solid #e2e8f0; border-radius:10px; margin:0 0 5px; transition:.15s; white-space:nowrap; overflow:hidden;">'
                        + '<input type="checkbox" class="chat-invoice-check" value="' + cn + '" checked '
                        +   'style="width:16px; height:16px; flex-shrink:0; cursor:pointer; accent-color:#0084FF;">'
                        + '<span style="font-family:\'SF Mono\',monospace; font-weight:700; color:#0c5e8e; font-size:13px; flex-shrink:0;">' + cn.toUpperCase() + '</span>'
                        + '<span style="background:#dbeafe; color:#1e40af; padding:1px 7px; border-radius:10px; font-size:10px; font-weight:600; flex-shrink:0;">' + count + ' ชิ้น</span>'
                        + '<span class="chat-status-badge" data-cn="' + cn + '" style="font-size:10px; flex-shrink:0;"><i class="fa fa-spinner fa-spin" style="color:#94a3b8;"></i></span>'
                        + '<span class="chat-send-result" data-cn="' + cn + '" style="font-size:10px; margin-left:auto; flex-shrink:0; text-align:right;"></span>'
                        + '</label>';
                });

                $('#invoiceChatEtdDisplay').text(displayDate);
                $('#invoiceChatCustomerList').html(listHtml);
                $('#invoiceChatCustomerCount').text(customerNos.length);

                // แปลง etd เป็น dd/mm/yyyy ให้ตรงกับที่เก็บใน DB
                var etdParts = etdDate.split('-');
                var etdFormatted = etdParts.length === 3 ? etdParts[2] + '/' + etdParts[1] + '/' + etdParts[0] : etdDate;

                // เช็คสถานะเชื่อมต่อแชท + สถานะส่งบิล จาก SKJ Chat API
                function refreshInvoiceStatus() {
                    var allCns = [];
                    $('.chat-invoice-check').each(function() { allCns.push($(this).val()); });
                    if (allCns.length === 0) return;
                    $.ajax({
                        url: "{{ route('send.invoice.chat') }}",
                        type: 'POST',
                        data: {
                            check_only: true,
                            customer_nos: allCns,
                            etd: etdFormatted,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(res) {
                            if (res.success && res.results) {
                                var connectedCount = 0;
                                var notConnectedCount = 0;
                                var invoiceSentCount = 0;
                                allCns.forEach(function(cn) {
                                    var info = res.results[cn];
                                    var badge = $('.chat-status-badge[data-cn="' + cn + '"]');
                                    if (info && info.connected) {
                                        connectedCount++;
                                        badge.html('<span style="background:#dcfce7; color:#15803d; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;">✓ เชื่อมต่อแล้ว</span>');
                                        if (info.invoiceSent) {
                                            invoiceSentCount++;
                                            var sendBadge = $('.chat-send-result[data-cn="' + cn + '"]');
                                            if (info.invoiceStatus === 'paid') {
                                                sendBadge.html('<span title="ชำระแล้ว" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:#cffafe; color:#0e7490; border-radius:50%; font-size:12px; font-weight:700;">💰</span>');
                                            } else {
                                                sendBadge.html('<span title="ส่งบิลแล้ว" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:#dcfce7; color:#15803d; border-radius:50%; font-size:12px; font-weight:700;">✅</span> <span title="รอโอน" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:#fed7aa; color:#9a3412; border-radius:50%; font-size:12px; font-weight:700;">🟠</span>');
                                            }
                                        }
                                    } else {
                                        notConnectedCount++;
                                        badge.html('<span style="background:#fee2e2; color:#b91c1c; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:600;">✗ ยังไม่เชื่อมต่อ</span>');
                                        badge.closest('label').find('.chat-invoice-check').prop('checked', false);
                                        badge.closest('label').css({ 'opacity':'0.55', 'background':'#fafafa' });
                                    }
                                });
                                var chipsHtml =
                                    '<span style="background:#dcfce7; color:#15803d; padding:5px 12px; border-radius:18px; font-size:12px; font-weight:700;">🟢 เชื่อมต่อ ' + connectedCount + ' ราย</span>'
                                    + '<span style="background:#fee2e2; color:#b91c1c; padding:5px 12px; border-radius:18px; font-size:12px; font-weight:700;">🔴 ยังไม่เชื่อมต่อ ' + notConnectedCount + ' ราย</span>'
                                    + (invoiceSentCount > 0 ? '<span style="background:#fef3c7; color:#92400e; padding:5px 12px; border-radius:18px; font-size:12px; font-weight:700;">📨 ส่งบิลแล้ว ' + invoiceSentCount + ' ราย</span>' : '');
                                $('#invoiceChatConnectionChips').html(chipsHtml);
                            }
                        },
                        error: function() {
                            $('.chat-status-badge').html('<span style="background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:10px; font-size:11px;">? ไม่ทราบ</span>');
                        }
                    });
                }
                refreshInvoiceStatus();

                // Auto-generate message template with Thai date
                var thaiDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
                var thaiMonths = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
                var now = new Date();
                var thaiDate = thaiDays[now.getDay()] + 'ที่ ' + ('0' + now.getDate()).slice(-2) + ' ' + thaiMonths[now.getMonth()];

                var messageTemplate = '✨ขออนุญาตแจ้งยอดนะครับ\n'
                    + '🚢ค่านำเข้า (รอบปิดตู้ ' + displayDate + ')\n'
                    + '📌จำนวน: ' + String.fromCharCode(123,123) + 'จำนวน' + String.fromCharCode(125,125) + ' ชิ้น\n'
                    + 'รวม: ' + String.fromCharCode(123,123) + 'รวม' + String.fromCharCode(125,125) + ' บาท\n'
                    + '\n'
                    + '📍พร้อมให้เข้ารับเอง/เรียกแมส ได้วัน' + thaiDate + ' ตั้งแต่ เวลา 09.30-18.00น.\n'
                    + '\n'
                    + '📍จัดส่งในไทยแจ้งที่อยู่จัดส่งผ่านระบบได้เลยครับ\n'
                    + '\n'
                    + '*‼️ลูกค้าที่ต้องการส่งในไทย รบกวนชำระค่านำเข้าแยกกับค่าส่งในไทยนะครับ🙏\n'
                    + '\n'
                    + '🙏🏻 ขอบคุณครับผม 🙏';
                $('#invoiceChatMessageTemplate').val(messageTemplate);
                $('#invoiceChatMessengerFee').val('');
                $('#invoiceChatResult').html('').hide();
                $('#invoiceChatSendBtn').prop('disabled', false).text('📩 แจ้งค่านำเข้า');

                $('#invoiceChatModal').modal('show');
            });

            // ปุ่มส่งใน modal
            $(document).on('click', '#invoiceChatSendBtn', function() {
                var etdDate = $('#start_date').val();
                var messageTemplate = $('#invoiceChatMessageTemplate').val();
                var messengerFee = parseFloat($('#invoiceChatMessengerFee').val()) || 0;

                // เก็บ customerno ที่ติ๊ก
                var selectedCustomers = [];
                $('.chat-invoice-check:checked').each(function() {
                    selectedCustomers.push($(this).val());
                });

                if (selectedCustomers.length === 0) {
                    alert('กรุณาเลือกลูกค้าอย่างน้อย 1 ราย');
                    return;
                }

                // เรียงจากน้อย → มาก แบบ natural sort (ANW-500 → ANW-501 → ANW-502 ...)
                selectedCustomers.sort(function(a, b) {
                    return String(a).localeCompare(String(b), undefined, { numeric: true, sensitivity: 'base' });
                });

                var btn = $(this);
                btn.prop('disabled', true).text('⏳ กำลังส่ง...');
                $('#invoiceChatResult').html('<div class="alert alert-info"><i class="fa fa-spinner fa-spin"></i> กำลังส่งบิล ' + selectedCustomers.length + ' ราย กรุณารอ...</div>').show();

                // แสดง spinner ข้างๆ แต่ละราย
                selectedCustomers.forEach(function(cn) {
                    $('.chat-send-result[data-cn="' + cn + '"]').html('<i class="fa fa-spinner fa-spin" style="color:#17a2b8;"></i>');
                });

                // รวม shipping_ids ของลูกค้าที่เลือก
                var customerMap = $('#invoiceChatModal').data('customerMap') || {};
                var shippingIdsMap = {};
                selectedCustomers.forEach(function(cn) {
                    if (customerMap[cn]) shippingIdsMap[cn] = customerMap[cn];
                });

                // ส่งทีละรหัสแบบ sequential เพื่อให้ UI แต่ละแถวอัปเดตทันทีเมื่อส่งสำเร็จ
                var successCount = 0, partialCount = 0, notFoundCount = 0, failedCount = 0;
                var totalCount = selectedCustomers.length;
                var idx = 0;

                function updateProgressBanner() {
                    var done = successCount + partialCount + notFoundCount + failedCount;
                    var html = '<div class="alert alert-info" style="padding:8px 12px;margin-top:8px;">'
                        + '<i class="fa fa-spinner fa-spin"></i> กำลังส่งบิล <b>' + Math.min(idx + 1, totalCount) + '/' + totalCount + '</b>'
                        + (done > 0 ? ' &nbsp;|&nbsp; ✅ ' + successCount + (partialCount > 0 ? ' &nbsp;⚠️ ' + partialCount : '') + (notFoundCount > 0 ? ' &nbsp;🔍 ' + notFoundCount : '') + (failedCount > 0 ? ' &nbsp;❌ ' + failedCount : '') : '')
                        + '</div>';
                    $('#invoiceChatResult').html(html).show();
                }

                function finalizeSummary() {
                    btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> แจ้งค่านำเข้า');

                    // เก็บผลลัพธ์จาก badges
                    var details = [];
                    $('.chat-invoice-check').each(function() {
                        var cn = $(this).val();
                        var resultEl = $('.chat-send-result[data-cn="' + cn + '"]');
                        var txt = resultEl.text() || '';
                        var status = 'unknown';
                        if (txt.indexOf('✅') >= 0) status = 'success';
                        else if (txt.indexOf('⚠️') >= 0) status = 'partial';
                        else if (txt.indexOf('🔍') >= 0) status = 'not_found';
                        else if (txt.indexOf('❌') >= 0) status = 'failed';
                        else if (txt.indexOf('💰') >= 0) status = 'paid';
                        details.push({ customerno: cn, status: status, message: txt.trim() });
                    });

                    var totalSent = successCount + partialCount + notFoundCount + failedCount;
                    var summary = 'ส่งบิลเสร็จสิ้น: สำเร็จ ' + successCount + ' ราย'
                        + (partialCount > 0 ? ', บางส่วน ' + partialCount + ' ราย' : '')
                        + (notFoundCount > 0 ? ', ไม่พบในแชท ' + notFoundCount + ' ราย' : '')
                        + (failedCount > 0 ? ', ไม่สำเร็จ ' + failedCount + ' ราย' : '');

                    var alertClass = (failedCount > 0 || notFoundCount > 0) ? 'alert-warning' : 'alert-success';
                    $('#invoiceChatResult').html('<div class="alert ' + alertClass + '" style="padding:8px 12px;margin-top:8px;"><b>' + summary + '</b></div>').show();

                    if (typeof showInvoiceResultModal === 'function') {
                        showInvoiceResultModal(summary, details, {
                            success: successCount, partial: partialCount, not_found: notFoundCount, failed: failedCount
                        });
                    }
                    setTimeout(function() { refreshInvoiceStatus(); }, 1000);
                }

                function sendNextInvoice() {
                    if (idx >= totalCount) {
                        finalizeSummary();
                        return;
                    }

                    var cn = selectedCustomers[idx];
                    var singleMap = {};
                    if (shippingIdsMap[cn]) singleMap[cn] = shippingIdsMap[cn];

                    updateProgressBanner();

                    $.ajax({
                        url: "{{ route('send.invoice.chat') }}",
                        type: 'POST',
                        data: {
                            etd: etdDate,
                            customer_nos: [cn],
                            shipping_ids_map: singleMap,
                            message_template: messageTemplate,
                            messenger_fee: messengerFee,
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            var badge = $('.chat-send-result[data-cn="' + cn + '"]');
                            var d = (response.results && response.results.details && response.results.details[0])
                                ? response.results.details[0] : null;

                            // ปั้น icon-only badge (โค้งกลม) พร้อม tooltip
                            var pi = function(bg, color, icon, tip){
                                return '<span title="'+tip+'" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:'+bg+'; color:'+color+'; border-radius:50%; font-size:12px; font-weight:700;">'+icon+'</span>';
                            };
                            if (!d) {
                                failedCount++;
                                badge.html(pi('#fee2e2','#b91c1c','❌','ส่งไม่สำเร็จ'));
                            } else if (d.status === 'success') {
                                successCount++;
                                badge.html(pi('#dcfce7','#15803d','✅','ส่งสำเร็จ') + ' ' + pi('#fed7aa','#9a3412','🟠','รอโอน'));
                            } else if (d.status === 'partial') {
                                partialCount++;
                                var shortMsg = (d.message || '').replace(/.*→.*\)/, '').trim();
                                badge.html(pi('#fed7aa','#9a3412','⚠️','ส่งได้บางส่วน' + (shortMsg ? ' — ' + shortMsg : '')) + ' ' + pi('#fed7aa','#9a3412','🟠','รอโอน'));
                            } else if (d.status === 'not_found') {
                                notFoundCount++;
                                badge.html(pi('#fef3c7','#92400e','🔍','ไม่พบในแชท'));
                            } else {
                                failedCount++;
                                var errMsg = d.message || 'ส่งไม่สำเร็จ';
                                if (errMsg.indexOf('24') >= 0 || errMsg.indexOf('window') >= 0) {
                                    errMsg = 'FB เกิน 24 ชม.';
                                }
                                badge.html(pi('#fee2e2','#b91c1c','❌', errMsg.substring(0, 80)));
                            }

                            idx++;
                            // เว้น 500ms ระหว่างรายเพื่อลดความเสี่ยง rate limit (LINE/FB)
                            setTimeout(sendNextInvoice, 500);
                        },
                        error: function(xhr) {
                            failedCount++;
                            var badge = $('.chat-send-result[data-cn="' + cn + '"]');
                            var errMsg = 'เชื่อมต่อไม่ได้';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errMsg = xhr.responseJSON.message;
                            }
                            badge.html('<span title="' + String(errMsg).substring(0, 80) + '" style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; background:#fee2e2; color:#b91c1c; border-radius:50%; font-size:12px; font-weight:700;">❌</span>');
                            idx++;
                            setTimeout(sendNextInvoice, 500);
                        }
                    });
                }

                sendNextInvoice();
            });

            // เลือกทั้งหมด / ยกเลิกทั้งหมด
            // ปุ่มเตือนชำระเงินใน modal
            $(document).on('click', '#invoiceChatRemindBtn', function() {
                var selectedCustomers = [];
                $('.chat-invoice-check:checked').each(function() {
                    selectedCustomers.push($(this).val());
                });
                if (selectedCustomers.length === 0) {
                    alert('กรุณาเลือกลูกค้าอย่างน้อย 1 ราย');
                    return;
                }
                if (!confirm('ต้องการส่งเตือนชำระเงินให้ ' + selectedCustomers.length + ' ราย?')) {
                    return;
                }
                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่ง...');
                $.ajax({
                    url: "{{ route('remind.payment') }}",
                    type: 'POST',
                    data: {
                        customer_nos: selectedCustomers,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-bell"></i> เตือนชำระเงิน');
                        var resultHtml = '<div class="alert alert-info" style="padding:8px 12px;margin-top:8px;"><b>' + (response.message || 'ส่งเตือนเรียบร้อย') + '</b></div>';
                        if (response.results) {
                            response.results.forEach(function(d) {
                                var cn = d.customerno;
                                var badge = $('.chat-send-result[data-cn="' + cn + '"]');
                                if (d.status === 'success') {
                                    badge.html('<span class="badge" style="background:#17a2b8;color:#fff;font-size:10px;">🔔 เตือนแล้ว</span>');
                                } else {
                                    badge.html('<span class="badge" style="background:#dc3545;color:#fff;font-size:10px;">❌ เตือนไม่สำเร็จ</span>');
                                }
                            });
                        }
                        $('#invoiceChatResult').html(resultHtml).show();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-bell"></i> เตือนชำระเงิน');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) errMsg = xhr.responseJSON.message;
                        $('#invoiceChatResult').html('<div class="alert alert-danger">' + errMsg + '</div>').show();
                    }
                });
            });

            $(document).on('click', '#invoiceChatSelectAll', function() {
                var allChecked = $('.chat-invoice-check').length === $('.chat-invoice-check:checked').length;
                $('.chat-invoice-check').prop('checked', !allChecked);
            });

            // === jQuery delegation for box image gallery (backup) ===
            $('#dt-mant-table-1 tbody').on('click', 'img.box-img', function(e) {
                e.stopPropagation();
                if (typeof openBoxGallery === 'function') {
                    openBoxGallery(this);
                }
            });

            // === Box Search Popup Handlers ===
            $('#boxSearchToggle').on('click', function() {
                $('#boxSearchPanel').toggleClass('open');
                if ($('#boxSearchPanel').hasClass('open')) {
                    setTimeout(function(){ $('#boxNoSearch').focus(); }, 100);
                }
            });
            $('#boxSearchClose').on('click', function() {
                $('#boxSearchPanel').removeClass('open');
            });

            // Box search — ค้นผ่าน server (รองรับ pagination)
            var _boxSearchTimer = null;

            $('#boxNoSearch').on('input', function() {
                var val = $(this).val().trim();
                var startDate = $('#start_date').val();
                clearTimeout(_boxSearchTimer);

                // ต้องเลือกรอบปิดตู้ก่อนถึงจะค้นได้
                if (!startDate) {
                    $('#boxSearchResult').html('<span style="color:#e67e22">กรุณาเลือกรอบปิดตู้ก่อน</span>');
                    return;
                }

                if (val.length >= 1) {
                    $('#boxSearchResult').html('<i class="fa fa-spinner fa-spin"></i> กำลังค้นหา...');
                    _boxSearchTimer = setTimeout(function() {
                        dataTable.ajax.reload(function() {
                            var info = dataTable.page.info();
                            if (info.recordsDisplay > 0) {
                                $('#boxSearchResult').html('พบ <b>' + info.recordsDisplay + '</b> รายการ ในรอบปิดตู้ ' + startDate);
                                // ไฮไลท์แถวที่ตรง
                                $('#dt-mant-table-1 tbody tr').each(function() {
                                    var boxCell = $(this).find('td').eq(11);
                                    var text = boxCell.text().trim().toLowerCase();
                                    if (text && text.indexOf(val.toLowerCase()) !== -1) {
                                        $(this).css('background', 'rgba(220,53,69,0.15)');
                                    }
                                });
                            } else {
                                $('#boxSearchResult').html('<span style="color:#dc3545">ไม่พบ "' + val + '" ในรอบปิดตู้ ' + startDate + '</span>');
                            }
                        }, false);
                    }, 400);
                } else {
                    $('#boxSearchResult').html('');
                    dataTable.ajax.reload(null, false);
                }
            });

            $('#boxSearchClear').on('click', function() {
                $('#boxNoSearch').val('');
                $('#dt-mant-table-1 tbody tr').css('background', '');
                $('#boxSearchResult').html('');
                dataTable.ajax.reload(null, false);
            });

            // === 📢 บรอดแคสรอบปิดตู้ ===
            $('#btn-broadcast-etd').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();
                $('#bcEtdDisplay').text(displayDate);

                // นับจำนวนลูกค้าในรอบ (จาก dataTable ปัจจุบัน)
                var customerSet = {};
                dataTable.rows().every(function() {
                    var data = this.data();
                    if (data && data.customerno) {
                        customerSet[data.customerno] = true;
                    }
                });
                $('#bcCustomerCount').text(Object.keys(customerSet).length);

                // reset form ถ้ายังไม่เคยกรอก
                if (!$('#bcMessage').val()) {
                    $('.bc-preset-btn[data-title="🕐 แจ้งสินค้าล่าช้ากว่ากำหนด"]').first().click();
                }

                $('#broadcastEtdModal').modal('show');
            });

            // preset click → fill title + message + color
            $(document).on('click', '.bc-preset-btn', function() {
                var $b = $(this);
                $('#bcTitle').val($b.data('title') || '');
                $('#bcMessage').val($b.data('message') || '');
                var color = $b.data('color') || '#f59e0b';
                $('#bcHeaderColor').val(color);
                $('.bc-color-btn').each(function(){
                    $(this).css('box-shadow', $(this).data('color').toLowerCase() === color.toLowerCase()
                        ? '0 0 0 3px ' + color : '0 0 0 2px #e5e7eb');
                });
                updateBcPreview();
            });

            // color swatch click
            $(document).on('click', '.bc-color-btn', function() {
                var color = $(this).data('color');
                $('#bcHeaderColor').val(color);
                $('.bc-color-btn').each(function(){
                    $(this).css('box-shadow', $(this).data('color').toLowerCase() === color.toLowerCase()
                        ? '0 0 0 3px ' + color : '0 0 0 2px #e5e7eb');
                });
                var names = {'#f59e0b':'ส้ม','#10b981':'เขียว','#0ea5e9':'ฟ้า','#ef4444':'แดง','#1D8AC9':'น้ำเงิน'};
                $('#bcColorName').text(names[color.toLowerCase()] || color).css('color', color);
            });

            // textarea counter + preview update + auto-grow
            function autoGrowBcMessage() {
                var el = document.getElementById('bcMessage');
                if (!el) return;
                el.style.height = 'auto';
                var newH = Math.min(Math.max(el.scrollHeight + 2, 180), 520);
                el.style.height = newH + 'px';
            }

            $('#bcMessage, #bcTitle').on('input', function() {
                var remain = 700 - ($('#bcMessage').val() || '').length;
                $('#bcCharLeft').text(remain);
                updateBcPreview();
                if (this.id === 'bcMessage') autoGrowBcMessage();
            });

            // grow ตอน modal เปิด (รวมถึงตอน preset auto-fill ที่กด click ภายนอก)
            $('#broadcastEtdModal').on('shown.bs.modal', autoGrowBcMessage);
            $(document).on('click', '.bc-preset-btn', function() { setTimeout(autoGrowBcMessage, 30); });

            function updateBcPreview() {
                var title = $('#bcTitle').val() || 'หัวข้อ';
                var etd = $('#bcEtdDisplay').text() || 'dd/mm/yyyy';
                $('#bcAltText').text('📢 ' + title + ' (รอบ ' + etd + ')');
            }

            // ส่งบรอดแคส
            $('#bcSendBtn').on('click', function() {
                var etdDate = $('#start_date').val();
                var title = $.trim($('#bcTitle').val());
                var message = $.trim($('#bcMessage').val());
                var headerColor = $('#bcHeaderColor').val();
                var onlySelected = $('#bcOnlySelected').is(':checked');

                if (!title) { alert('กรุณากรอกหัวข้อ'); return; }
                if (!message) { alert('กรุณากรอกข้อความ'); return; }

                var customerNos = [];
                if (onlySelected) {
                    var checked = $('tbody').find(':checkbox:checked');
                    if (checked.length === 0) {
                        alert('คุณเลือก "ส่งเฉพาะที่ติ๊ก" แต่ยังไม่ได้ติ๊กแถวใดในตาราง');
                        return;
                    }
                    checked.each(function() {
                        var row = dataTable.row($(this).closest('tr'));
                        var data = row.data();
                        if (data && data.customerno && customerNos.indexOf(data.customerno) === -1) {
                            customerNos.push(data.customerno);
                        }
                    });
                }

                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();
                var targetCount = onlySelected ? customerNos.length : $('#bcCustomerCount').text();

                if (!confirm('ส่งบรอดแคสให้ลูกค้า ' + targetCount + ' ราย\nรอบปิดตู้: ' + displayDate + '\nหัวข้อ: ' + title + '\n\nยืนยันการส่ง?')) {
                    return;
                }

                var btn = $(this);
                var orig = btn.html();
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่ง...');

                $.ajax({
                    url: "{{ route('broadcast.etd.message') }}",
                    type: 'POST',
                    data: {
                        etd: etdDate,
                        title: title,
                        message: message,
                        header_color: headerColor,
                        customer_nos: customerNos,
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        btn.prop('disabled', false).html(orig);
                        var summary = response.message || '';
                        var details = (response.results && response.results.details) ? response.results.details : [];

                        if (!response.success) {
                            Swal.fire({ icon:'error', title:'ส่งบรอดแคสไม่สำเร็จ', text: summary });
                            return;
                        }

                        // --- สรุปจำนวนแต่ละ status ---
                        var counts = { success: 0, failed: 0, no_contact: 0 };
                        details.forEach(function(d) { if (counts.hasOwnProperty(d.status)) counts[d.status]++; });

                        function esc(s) { return String(s == null ? '' : s).replace(/[&<>"']/g, function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c];}); }

                        // sort: failed → no_contact → success
                        var order = { failed: 0, no_contact: 1, success: 2 };
                        var sorted = details.slice().sort(function(a, b) {
                            var oa = order[a.status] != null ? order[a.status] : 3;
                            var ob = order[b.status] != null ? order[b.status] : 3;
                            if (oa !== ob) return oa - ob;
                            return String(a.customerno || '').localeCompare(String(b.customerno || ''));
                        });

                        function rowHtml(d) {
                            var icon, color, label;
                            if (d.status === 'success')    { icon='✅'; color='#10b981'; label='สำเร็จ'; }
                            else if (d.status === 'no_contact') { icon='⚠️'; color='#f59e0b'; label='ไม่พบในแชท'; }
                            else                            { icon='❌'; color='#ef4444'; label='ไม่สำเร็จ'; }
                            return '<div class="bc-result-row" data-status="'+d.status+'" '
                                +'style="display:flex; align-items:center; gap:10px; padding:8px 12px; border-bottom:1px solid #f1f5f9; font-size:13px;">'
                                +'<span style="flex:0 0 28px;">'+icon+'</span>'
                                +'<span style="flex:0 0 110px; font-family:\'SF Mono\',monospace; font-weight:600; color:#1D8AC9;">'+esc(d.customerno)+'</span>'
                                +'<span style="flex:0 0 90px; color:'+color+'; font-weight:600;">'+label+'</span>'
                                +'<span style="flex:1; color:#475569;">'+esc(d.message)+'</span>'
                                +'</div>';
                        }

                        var rowsHtml = sorted.map(rowHtml).join('');

                        var chipsHtml =
                            '<div style="display:flex; flex-wrap:wrap; gap:6px; margin-bottom:10px;">'
                            +'<button type="button" class="bc-filter-chip" data-filter="all" '
                            +'style="background:#1D8AC9; color:#fff; border:0; border-radius:18px; padding:5px 14px; font-size:13px; font-weight:600; cursor:pointer;">ทั้งหมด ('+details.length+')</button>'
                            +'<button type="button" class="bc-filter-chip" data-filter="success" '
                            +'style="background:#fff; color:#10b981; border:1px solid #10b981; border-radius:18px; padding:5px 14px; font-size:13px; font-weight:600; cursor:pointer;">✅ สำเร็จ ('+counts.success+')</button>'
                            +'<button type="button" class="bc-filter-chip" data-filter="no_contact" '
                            +'style="background:#fff; color:#f59e0b; border:1px solid #f59e0b; border-radius:18px; padding:5px 14px; font-size:13px; font-weight:600; cursor:pointer;">⚠️ ไม่พบในแชท ('+counts.no_contact+')</button>'
                            +'<button type="button" class="bc-filter-chip" data-filter="failed" '
                            +'style="background:#fff; color:#ef4444; border:1px solid #ef4444; border-radius:18px; padding:5px 14px; font-size:13px; font-weight:600; cursor:pointer;">❌ ไม่สำเร็จ ('+counts.failed+')</button>'
                            +'</div>';

                        var searchHtml = '<input type="text" id="bcResultSearch" placeholder="🔍 ค้นหา ANW-xxx..." '
                            +'style="width:100%; padding:6px 12px; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:10px; font-size:13px;">';

                        var headerHtml = '<div style="color:#475569; font-size:13px; margin-bottom:10px; padding:10px 12px; background:#f8fafc; border-radius:8px;">'
                            + esc(summary) + '</div>';

                        var listHtml = '<div id="bcResultList" style="max-height:380px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px; background:#fff;">' + rowsHtml + '</div>';

                        $('#broadcastEtdModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: 'บรอดแคสสำเร็จ',
                            html: headerHtml + chipsHtml + searchHtml + listHtml,
                            width: '720px',
                            confirmButtonColor: '#f59e0b',
                            confirmButtonText: 'ปิด',
                            didOpen: function() {
                                // filter chip
                                $(document).off('click.bcfilter').on('click.bcfilter', '.bc-filter-chip', function() {
                                    var f = $(this).data('filter');
                                    $('.bc-filter-chip').each(function() {
                                        var fx = $(this).data('filter');
                                        var color = fx === 'success' ? '#10b981' : (fx === 'no_contact' ? '#f59e0b' : (fx === 'failed' ? '#ef4444' : '#1D8AC9'));
                                        if (fx === f) {
                                            $(this).css({ background: color, color: '#fff', borderColor: color });
                                        } else {
                                            $(this).css({ background: '#fff', color: color, borderColor: color });
                                        }
                                    });
                                    $('.bc-result-row').each(function() {
                                        var st = $(this).data('status');
                                        $(this).toggle(f === 'all' || st === f);
                                    });
                                });
                                // search
                                $(document).off('input.bcsearch').on('input.bcsearch', '#bcResultSearch', function() {
                                    var q = $(this).val().toLowerCase().trim();
                                    $('.bc-result-row').each(function() {
                                        var txt = $(this).text().toLowerCase();
                                        $(this).toggle(!q || txt.indexOf(q) !== -1);
                                    });
                                });
                            }
                        });
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html(orig);
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) errMsg = xhr.responseJSON.message;
                        Swal.fire({ icon:'error', title:'Error', text: errMsg });
                    }
                });
            });

            // === แจ้งค่าส่งไทย LINE (ส่งบิล Shippop) ===
            // Preview รูปบิลที่อัพโหลด (รองรับหลายไฟล์)
            $(document).on('change', '#tsInvoiceFile', function() {
                var files = this.files;
                if (files && files.length > 0) {
                    var html = '<div style="display:flex;flex-wrap:wrap;gap:8px;">';
                    for (var i = 0; i < files.length; i++) {
                        var f = files[i];
                        var sizeKB = (f.size / 1024).toFixed(0);
                        if (f.type.startsWith('image/')) {
                            html += '<div style="text-align:center;padding:6px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;max-width:120px;">'
                                + '<img src="" class="ts-preview-thumb" data-index="' + i + '" style="max-width:100px;max-height:80px;border-radius:4px;">'
                                + '<br><small style="font-size:10px;word-break:break-all;">' + f.name + '</small>'
                                + '<br><small class="text-muted" style="font-size:9px;">' + sizeKB + ' KB</small></div>';
                        } else {
                            html += '<div style="text-align:center;padding:6px;background:#fff;border:1px solid #e2e8f0;border-radius:8px;max-width:120px;">'
                                + '<i class="fa fa-file-pdf-o" style="font-size:30px;color:#dc3545;"></i>'
                                + '<br><small style="font-size:10px;word-break:break-all;">' + f.name + '</small>'
                                + '<br><small class="text-muted" style="font-size:9px;">' + sizeKB + ' KB</small></div>';
                        }
                    }
                    html += '</div><div class="mt-1"><small class="text-info"><b>' + files.length + ' ไฟล์</b></small></div>';
                    $('#tsInvoicePreview').html(html).show();
                    // Load image thumbnails
                    for (var j = 0; j < files.length; j++) {
                        if (files[j].type.startsWith('image/')) {
                            (function(idx) {
                                var reader = new FileReader();
                                reader.onload = function(e) {
                                    $('.ts-preview-thumb[data-index="' + idx + '"]').attr('src', e.target.result);
                                };
                                reader.readAsDataURL(files[idx]);
                            })(j);
                        }
                    }
                } else {
                    $('#tsInvoicePreview').hide();
                }

                tsParsePreview(files);
            });

            function tsRenderPreviewRow(item, idx, allBoxesSet) {
                var refNo = (item.refNo || '').replace(/"/g, '&quot;');
                var courier = (item.courier || '').replace(/"/g, '&quot;');
                var dest = (item.destination || '').replace(/"/g, '&quot;');
                var boxes = Array.isArray(item.boxes) ? item.boxes : [];
                var boxStr = boxes.join('+');
                var price = item.totalPrice ? Number(item.totalPrice).toFixed(2) : '';

                var matched = 0;
                boxes.forEach(function(b){ if (allBoxesSet.has(String(b))) matched++; });
                var badge = '';
                if (boxes.length === 0) {
                    badge = '<span title="ไม่พบ Box" style="color:#94a3b8;">—</span>';
                } else if (matched === boxes.length) {
                    badge = '<span title="match กับ Box ที่เลือกทุกอัน" style="color:#15803d;">✅</span>';
                } else if (matched > 0) {
                    badge = '<span title="' + matched + '/' + boxes.length + ' Box match" style="color:#d97706;">⚠️</span>';
                } else {
                    badge = '<span title="ไม่มี Box ที่ match กับลูกค้าที่เลือก" style="color:#b91c1c;">❌</span>';
                }

                return '<tr class="ts-parse-row" data-idx="' + idx + '">'
                    + '<td style="padding:4px 8px; font-weight:700; color:#475569;">' + (idx + 1) + ' ' + badge + '</td>'
                    + '<td style="padding:4px 6px;"><input type="text" class="form-control form-control-sm ts-p-ref" value="' + refNo + '" style="font-size:11px; padding:3px 6px; font-family:monospace;"></td>'
                    + '<td style="padding:4px 6px;"><input type="text" class="form-control form-control-sm ts-p-courier" value="' + courier + '" style="font-size:11px; padding:3px 6px;"></td>'
                    + '<td style="padding:4px 6px;"><input type="text" class="form-control form-control-sm ts-p-dest" value="' + dest + '" style="font-size:11px; padding:3px 6px;"></td>'
                    + '<td style="padding:4px 6px;"><input type="text" class="form-control form-control-sm ts-p-boxes" value="' + boxStr + '" placeholder="66+88+..." style="font-size:11px; padding:3px 6px; font-family:monospace;"></td>'
                    + '<td style="padding:4px 6px;"><input type="number" step="0.01" min="0" class="form-control form-control-sm ts-p-price" value="' + price + '" style="font-size:11px; padding:3px 6px; text-align:right;"></td>'
                    + '<td style="padding:4px 6px; text-align:center;"><button type="button" class="btn btn-link p-0 ts-p-del" style="color:#dc2626;" title="ลบ"><i class="fa fa-trash"></i></button></td>'
                    + '</tr>';
            }

            function tsCollectSelectedBoxes() {
                var boxesSet = new Set();
                var customerMap = $('#thaiShippingModal').data('customerMap') || {};
                var ids = [];
                Object.keys(customerMap).forEach(function(cn){ ids = ids.concat(customerMap[cn] || []); });
                var idSet = new Set(ids.map(String));
                try {
                    var t = $.fn.DataTable.isDataTable('#dt-mant-table-1') ? $('#dt-mant-table-1').DataTable() : null;
                    if (t) {
                        t.rows().every(function(){
                            var d = this.data();
                            if (d && idSet.has(String(d.id)) && d.box_no) {
                                String(d.box_no).split(/[\s,+]+/).forEach(function(b){
                                    var n = String(parseInt(b, 10));
                                    if (n && n !== 'NaN') boxesSet.add(n);
                                });
                            }
                        });
                    }
                } catch(e) {}
                return boxesSet;
            }

            function tsParsePreview(files) {
                if (!files || files.length === 0) {
                    $('#tsParseCard').hide();
                    return;
                }
                var hasPdf = false;
                for (var i = 0; i < files.length; i++) {
                    if ((files[i].type || '').toLowerCase().indexOf('pdf') !== -1 || /\.pdf$/i.test(files[i].name)) {
                        hasPdf = true; break;
                    }
                }
                if (!hasPdf) {
                    $('#tsParseCard').show();
                    $('#tsParseCount').text('(ไม่มี PDF — กรอกข้อมูลเองได้ด้วยปุ่ม "เพิ่มรายการ")');
                    $('#tsParseSum').text('');
                    $('#tsParseBody').html('');
                    $('#tsParseStatus').hide().html('');
                    return;
                }

                $('#tsParseCard').show();
                $('#tsParseStatus').html('<div class="alert alert-info" style="padding:6px 10px; font-size:12px; margin:0;"><i class="fa fa-spinner fa-spin"></i> กำลังอ่านบิล PDF...</div>').show();
                $('#tsParseBody').html('');
                $('#tsParseCount').text('');
                $('#tsParseSum').text('');

                var fd = new FormData();
                fd.append('_token', '{{ csrf_token() }}');
                for (var k = 0; k < files.length; k++) {
                    fd.append('invoice_files[]', files[k]);
                }

                $.ajax({
                    url: "{{ route('shippop.parse-preview') }}",
                    type: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    success: function(resp) {
                        var items = (resp && resp.items) || [];
                        var warnings = (resp && resp.warnings) || [];
                        var allBoxes = tsCollectSelectedBoxes();
                        var bodyHtml = '';
                        items.forEach(function(it, i){ bodyHtml += tsRenderPreviewRow(it, i, allBoxes); });
                        $('#tsParseBody').html(bodyHtml);
                        $('#tsParseCount').text('(' + items.length + ' รายการ)');
                        var sum = (resp.totalAmount || 0).toLocaleString('th-TH', {minimumFractionDigits:2, maximumFractionDigits:2});
                        $('#tsParseSum').html('<i class="fa fa-money"></i> รวม ฿ <b>' + sum + '</b>');
                        if (warnings.length > 0) {
                            var wHtml = '<div class="alert alert-warning" style="padding:6px 10px; font-size:11px; margin:0;"><b>⚠️ ' + warnings.length + ' คำเตือน:</b><br>' + warnings.map(function(w){ return '• ' + w; }).join('<br>') + '</div>';
                            $('#tsParseStatus').html(wHtml).show();
                        } else if (items.length > 0) {
                            $('#tsParseStatus').html('<div class="alert alert-success" style="padding:6px 10px; font-size:11px; margin:0;"><i class="fa fa-check"></i> อ่านบิลสำเร็จ ' + items.length + ' รายการ — สามารถแก้ไขในตารางก่อนกดส่ง</div>').show();
                        } else {
                            $('#tsParseStatus').html('<div class="alert alert-warning" style="padding:6px 10px; font-size:11px; margin:0;"><i class="fa fa-info-circle"></i> ไม่พบรายการที่ parse ได้ — กรุณากรอกเองหรือเช็ครูปแบบ PDF</div>').show();
                        }
                    },
                    error: function(xhr) {
                        var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'เกิดข้อผิดพลาด';
                        $('#tsParseStatus').html('<div class="alert alert-danger" style="padding:6px 10px; font-size:11px; margin:0;"><i class="fa fa-exclamation-circle"></i> parse ไม่สำเร็จ: ' + msg + '</div>').show();
                    }
                });
            }

            $(document).on('click', '#tsAddRow', function() {
                var idx = $('#tsParseBody tr').length;
                $('#tsParseBody').append(tsRenderPreviewRow({refNo:'',courier:'',destination:'',boxes:[],totalPrice:0}, idx, tsCollectSelectedBoxes()));
                $('#tsParseCard').show();
            });

            $(document).on('click', '.ts-p-del', function() {
                $(this).closest('tr').remove();
                $('#tsParseBody tr').each(function(i){
                    var $first = $(this).find('td:first-child');
                    var badgeHtml = $first.find('span').prop('outerHTML') || '';
                    $first.html((i+1) + ' ' + badgeHtml);
                });
            });

            function tsCollectParsedItems() {
                var items = [];
                $('#tsParseBody tr').each(function(){
                    var $r = $(this);
                    var refNo = ($r.find('.ts-p-ref').val() || '').trim();
                    var courier = ($r.find('.ts-p-courier').val() || '').trim();
                    var dest = ($r.find('.ts-p-dest').val() || '').trim();
                    var boxesStr = ($r.find('.ts-p-boxes').val() || '').trim();
                    var priceVal = parseFloat($r.find('.ts-p-price').val()) || 0;
                    var boxes = boxesStr ? boxesStr.split(/[\s,+]+/).map(function(b){ return parseInt(b,10); }).filter(function(n){ return n>0; }) : [];
                    if (!refNo && boxes.length === 0) return;
                    items.push({ refNo:refNo, courier:courier, destination:dest, totalPrice:priceVal, boxes:boxes });
                });
                return items;
            }
            window.tsCollectParsedItems = tsCollectParsedItems;

            // เปิด modal แจ้งค่าส่งไทย
            $('#btn-thai-shipping-notify').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการแจ้งค่าส่งก่อน (ติ๊กถูกด้านซ้าย)');
                    return;
                }

                var customerMap = {};
                selectedRows.each(function() {
                    var row = dataTable.row($(this).closest('tr'));
                    var data = row.data();
                    if (data && data.customerno) {
                        var cn = data.customerno;
                        if (!customerMap[cn]) customerMap[cn] = [];
                        if (data.id) customerMap[cn].push(data.id);
                    }
                });

                // เรียงรหัสลูกค้าจากน้อย → มาก แบบ natural sort
                var customerNos = Object.keys(customerMap).sort(function(a, b) {
                    return String(a).localeCompare(String(b), undefined, { numeric: true, sensitivity: 'base' });
                });
                if (customerNos.length === 0) {
                    alert('ไม่พบรหัสลูกค้าจากรายการที่เลือก');
                    return;
                }

                $('#thaiShippingModal').data('customerMap', customerMap);

                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();
                $('#tsEtdDisplay').text(displayDate);

                var listHtml = '';
                customerNos.forEach(function(cn) {
                    var count = customerMap[cn].length;
                    listHtml += '<label class="ts-row" data-cn="' + cn + '" style="display:flex;align-items:center;gap:8px;padding:8px 10px;cursor:pointer;border-bottom:1px solid #f0f0f0;margin:0;background:#fff;border-radius:8px;margin-bottom:4px;transition:.12s;">'
                        + '<input type="checkbox" class="ts-customer-check" value="' + cn + '" checked style="width:18px;height:18px;flex-shrink:0;cursor:pointer;">'
                        + '<span style="font-weight:700;min-width:80px;color:#0c5e8e;font-family:\'SF Mono\',monospace;font-size:12px;">' + cn.toUpperCase() + '</span>'
                        + '<span class="badge" style="background:#dbeafe;color:#1e40af;font-size:10px;padding:3px 7px;border-radius:6px;font-weight:600;">' + count + ' ชิ้น</span>'
                        + '<span class="ts-line-badge" data-cn="' + cn + '" style="font-size:10px; flex-shrink:0;"><i class="fa fa-spinner fa-spin" style="color:#94a3b8;"></i></span>'
                        + '<span class="ts-send-result" data-cn="' + cn + '" style="font-size:10px;margin-left:auto;flex-shrink:0;text-align:right;"></span>'
                        + '</label>';
                });
                $('#tsCustomerList').html(listHtml);
                $('#tsCustomerCount').text(customerNos.length);

                $('#tsInvoiceFile').val('');
                $('#tsInvoicePreview').hide().html('');
                $('#tsMessage').val('');
                $('#tsResult').html('').hide();
                $('#tsSearch').val('');
                $('#tsParseCard').hide();
                $('#tsParseBody').html('');
                $('#tsParseStatus').hide().html('');
                $('#tsParseCount').text('');
                $('#tsParseSum').text('');
                $('#tsSendBtn').prop('disabled', false).html('<i class="fa fa-truck"></i> ส่งแจ้งค่าส่งไทย');

                $('#thaiShippingModal').modal('show');

                // ตรวจสอบ LINE/Chat connection ของแต่ละราย ผ่าน endpoint เดียวกับ invoiceChat (check_only)
                $.ajax({
                    url: "{{ route('send.invoice.chat') }}",
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", check_only: 1, customer_nos: customerNos, etd: etdDate },
                    success: function(resp) {
                        var results = (resp && resp.results) || {};
                        var lineCount = 0, otherCount = 0, noCount = 0;
                        customerNos.forEach(function(cn) {
                            var info = results[cn] || {};
                            var badge = $('.ts-line-badge[data-cn="' + cn + '"]');
                            var platform = (info.platform || '').toLowerCase();
                            if (info.connected && platform === 'line') {
                                lineCount++;
                                badge.html('<span title="LINE" style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#dcfce7;color:#15803d;border-radius:50%;font-size:11px;font-weight:700;">L</span>');
                            } else if (info.connected) {
                                otherCount++;
                                var plat = platform === 'facebook' ? 'F' : (platform.substring(0,1).toUpperCase() || '?');
                                badge.html('<span title="เชื่อมต่อผ่าน ' + platform + '" style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#dbeafe;color:#1e40af;border-radius:50%;font-size:11px;font-weight:700;">' + plat + '</span>');
                            } else {
                                noCount++;
                                badge.html('<span title="ยังไม่เชื่อมต่อ — จะถูกข้าม" style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:#fee2e2;color:#b91c1c;border-radius:50%;font-size:13px;font-weight:700;">✗</span>');
                                badge.closest('label').find('.ts-customer-check').prop('checked', false);
                                badge.closest('label').css({ 'opacity': '0.55', 'background': '#fafafa' });
                            }
                        });
                        var chips = '<span style="background:#dcfce7;color:#15803d;padding:5px 12px;border-radius:18px;font-size:12px;font-weight:700;">🟢 LINE ' + lineCount + ' ราย</span>';
                        if (otherCount > 0) chips += '<span style="background:#dbeafe;color:#1e40af;padding:5px 12px;border-radius:18px;font-size:12px;font-weight:700;">🔵 อื่นๆ ' + otherCount + ' ราย</span>';
                        chips += '<span style="background:#fee2e2;color:#b91c1c;padding:5px 12px;border-radius:18px;font-size:12px;font-weight:700;">🔴 ยังไม่เชื่อมต่อ ' + noCount + ' ราย</span>';
                        $('#tsLineChips').html(chips);
                    },
                    error: function() {
                        $('.ts-line-badge').html('<span style="background:#f1f5f9;color:#475569;padding:2px 8px;border-radius:10px;font-size:11px;">?</span>');
                    }
                });
            });

            // สลับทั้งหมด
            $(document).on('click', '#tsSelectAll', function() {
                var visible = $('.ts-row:visible .ts-customer-check');
                var allChecked = visible.filter(':checked').length === visible.length;
                visible.prop('checked', !allChecked);
            });

            // ค้นหา ANW-xxx
            $(document).on('input', '#tsSearch', function() {
                var q = $(this).val().toLowerCase().trim();
                $('.ts-row').each(function() {
                    var cn = String($(this).data('cn') || '').toLowerCase();
                    $(this).toggle(!q || cn.indexOf(q) !== -1);
                });
            });

            // Drag & Drop upload
            $(document).on('dragover dragenter', '#tsDropZone', function(e) {
                e.preventDefault(); e.stopPropagation();
                $(this).css({ background:'#dbeafe', borderColor:'#3b82f6' });
            });
            $(document).on('dragleave drop', '#tsDropZone', function(e) {
                e.preventDefault(); e.stopPropagation();
                $(this).css({ background:'#f0f9ff', borderColor:'#93c5fd' });
            });
            $(document).on('drop', '#tsDropZone', function(e) {
                var files = e.originalEvent.dataTransfer && e.originalEvent.dataTransfer.files;
                if (files && files.length) {
                    document.getElementById('tsInvoiceFile').files = files;
                    $('#tsInvoiceFile').trigger('change');
                }
            });

            // ===== แจ้งเตือนค้างจ่าย =====
            $('#btn-thai-remind').on('click', function() {
                var etdDate = $('#start_date').val();
                if (!etdDate) {
                    alert('กรุณาเลือกวันที่ปิดตู้ก่อน');
                    return;
                }

                var d = new Date(etdDate);
                var displayDate = ('0'+d.getDate()).slice(-2) + '/' + ('0'+(d.getMonth()+1)).slice(-2) + '/' + d.getFullYear();
                $('#rmEtdDisplay').text(displayDate);
                $('#rmLoading').show();
                $('#rmEmpty').hide();
                $('#rmContent').hide();
                $('#rmResult').html('').hide();
                $('#rmSendBtn').hide();
                $('#rmMessage').val('');
                $('#thaiRemindModal').modal('show');

                $.ajax({
                    url: "{{ route('shippop.unpaid.customers') }}",
                    type: 'GET',
                    data: { etd: etdDate },
                    success: function(response) {
                        $('#rmLoading').hide();
                        if (!response.customers || response.customers.length === 0) {
                            $('#rmEmpty').show();
                            return;
                        }

                        var customers = response.customers;
                        var listHtml = '';
                        var totalAll = 0;
                        customers.forEach(function(c) {
                            var amt = parseFloat(c.bill_amount) || 0;
                            totalAll += amt;
                            listHtml += '<label style="display:flex;align-items:center;gap:8px;padding:8px 6px;cursor:pointer;border-bottom:1px solid #fee2e2;margin:0;">'
                                + '<input type="checkbox" class="rm-customer-check" value="' + c.customerno + '" data-amount="' + amt + '" checked style="width:18px;height:18px;flex-shrink:0;cursor:pointer;">'
                                + '<span style="font-weight:600;min-width:80px;">' + c.customerno.toUpperCase() + '</span>'
                                + '<span class="badge badge-info" style="font-size:11px;">' + c.item_count + ' ชิ้น</span>'
                                + (amt > 0 ? '<span class="badge" style="background:#ef4444;color:#fff;font-size:11px;">฿' + amt.toLocaleString('th-TH', {minimumFractionDigits:2}) + '</span>' : '')
                                + '<span class="rm-send-result" data-cn="' + c.customerno + '" style="font-size:10px;margin-left:auto;"></span>'
                                + '</label>';
                        });
                        $('#rmCustomerList').html(listHtml);
                        $('#rmCount').text(customers.length);
                        $('#rmContent').show();
                        $('#rmSendBtn').show().prop('disabled', false).html('<i class="fa fa-bell"></i> ส่งแจ้งเตือน LINE');

                        // Update count on checkbox change
                        $(document).off('change', '.rm-customer-check').on('change', '.rm-customer-check', function() {
                            var checked = $('.rm-customer-check:checked').length;
                            $('#rmCount').text(checked);
                        });
                    },
                    error: function(xhr) {
                        $('#rmLoading').hide();
                        $('#rmResult').html('<div class="alert alert-danger">เกิดข้อผิดพลาดในการดึงข้อมูล</div>').show();
                    }
                });
            });

            // เลือก/ยกเลิกทั้งหมด
            $(document).on('click', '#rmSelectAll', function() {
                var checks = $('.rm-customer-check');
                var allChecked = checks.filter(':checked').length === checks.length;
                checks.prop('checked', !allChecked);
                $('#rmCount').text(allChecked ? 0 : checks.length);
            });

            // ส่งแจ้งเตือน
            $(document).on('click', '#rmSendBtn', function() {
                var selectedCustomers = [];
                $('.rm-customer-check:checked').each(function() {
                    selectedCustomers.push($(this).val());
                });

                if (selectedCustomers.length === 0) {
                    alert('กรุณาเลือกลูกค้าอย่างน้อย 1 ราย');
                    return;
                }

                if (!confirm('ต้องการส่งแจ้งเตือนค้างจ่ายให้ลูกค้า ' + selectedCustomers.length + ' ราย?\n(' + selectedCustomers.join(', ') + ')')) {
                    return;
                }

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังส่งแจ้งเตือน...');
                selectedCustomers.forEach(function(cn) {
                    $('.rm-send-result[data-cn="' + cn + '"]').html('<i class="fa fa-spinner fa-spin" style="color:#ef4444;"></i>');
                });

                $.ajax({
                    url: "{{ route('shippop.send.reminder') }}",
                    type: 'POST',
                    data: JSON.stringify({
                        _token: '{{ csrf_token() }}',
                        customer_nos: selectedCustomers,
                        etd: $('#start_date').val(),
                        message: $('#rmMessage').val()
                    }),
                    contentType: 'application/json',
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-bell"></i> ส่งแจ้งเตือน LINE');
                        var successCount = 0, failedCount = 0;
                        if (response.results && response.results.details) {
                            response.results.details.forEach(function(d) {
                                var badge = $('.rm-send-result[data-cn="' + d.customerno + '"]');
                                if (d.status === 'success') {
                                    successCount++;
                                    badge.html('<span class="badge" style="background:#22c55e;color:#fff;font-size:10px;">✅ ส่งสำเร็จ</span>');
                                } else {
                                    failedCount++;
                                    badge.html('<span class="badge" style="background:#dc3545;color:#fff;font-size:10px;">❌ ' + (d.message||'ไม่สำเร็จ') + '</span>');
                                }
                            });
                        }
                        var alertClass = failedCount > 0 ? 'alert-warning' : 'alert-success';
                        var parts = [];
                        if (successCount > 0) parts.push('✅ สำเร็จ ' + successCount + ' ราย');
                        if (failedCount > 0) parts.push('❌ ไม่สำเร็จ ' + failedCount + ' ราย');
                        $('#rmResult').html('<div class="alert ' + alertClass + '" style="padding:8px 12px;"><b>' + parts.join(' &nbsp;|&nbsp; ') + '</b></div>').show();
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-bell"></i> ส่งแจ้งเตือน LINE');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) errMsg = xhr.responseJSON.message;
                        $('#rmResult').html('<div class="alert alert-danger">' + errMsg + '</div>').show();
                    }
                });
            });

            // ส่งบิล Shippop ผ่าน LINE
            $(document).on('click', '#tsSendBtn', function() {
                var selectedCustomers = [];
                $('.ts-customer-check:checked').each(function() {
                    selectedCustomers.push($(this).val());
                });

                if (selectedCustomers.length === 0) {
                    alert('กรุณาเลือกลูกค้าอย่างน้อย 1 ราย');
                    return;
                }

                var fileInput = document.getElementById('tsInvoiceFile');
                if (!fileInput.files || fileInput.files.length === 0) {
                    alert('กรุณาอัพโหลดรูปบิล / ใบเสร็จ Shippop (เลือกได้หลายไฟล์)');
                    return;
                }

                if (!confirm('ต้องการส่งบิล Shippop ผ่าน LINE ให้ลูกค้า ' + selectedCustomers.length + ' ราย?')) {
                    return;
                }

                // เรียงรหัสจากน้อย → มาก
                selectedCustomers.sort(function(a, b) {
                    return String(a).localeCompare(String(b), undefined, { numeric: true, sensitivity: 'base' });
                });

                var btn = $(this);
                btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังอัพโหลดและส่ง...');
                $('#tsResult').html('<div class="alert alert-info" style="padding:8px 12px;"><i class="fa fa-spinner fa-spin"></i> กำลังส่งบิล ' + selectedCustomers.length + ' ราย กรุณารอ...</div>').show();

                selectedCustomers.forEach(function(cn) {
                    $('.ts-send-result[data-cn="' + cn + '"]').html('<i class="fa fa-spinner fa-spin" style="color:#0ea5e9;"></i>');
                });

                var customerMap = $('#thaiShippingModal').data('customerMap') || {};
                var formData = new FormData();
                formData.append('_token', '{{ csrf_token() }}');
                for (var fi = 0; fi < fileInput.files.length; fi++) {
                    formData.append('invoice_files[]', fileInput.files[fi]);
                }
                formData.append('message', $('#tsMessage').val());
                formData.append('etd', $('#start_date').val());
                selectedCustomers.forEach(function(cn) {
                    formData.append('customer_nos[]', cn);
                });
                formData.append('customer_map', JSON.stringify(customerMap));

                // แนบรายการ shipment ต่อ Box (จาก preview ที่ admin review/แก้แล้ว)
                try {
                    var parsedItems = (typeof tsCollectParsedItems === 'function') ? tsCollectParsedItems() : [];
                    formData.append('parsed_items', JSON.stringify(parsedItems));
                } catch(e) { formData.append('parsed_items', '[]'); }

                // Helper: badge icon-only พร้อม tooltip
                var pi = function(bg, color, icon, tip){
                    return '<span title="' + tip.replace(/"/g, '&quot;') + '" style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;background:' + bg + ';color:' + color + ';border-radius:50%;font-size:12px;font-weight:700;">' + icon + '</span>';
                };

                $.ajax({
                    url: "{{ route('shippop.notify.shipping') }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        btn.prop('disabled', false).html('<i class="fa fa-truck"></i> ส่งแจ้งค่าส่งไทย');

                        var successCount = 0, failedCount = 0, notFoundCount = 0;
                        var details = [];
                        if (response.results && response.results.details) {
                            response.results.details.forEach(function(d) {
                                var cn = d.customerno;
                                var badge = $('.ts-send-result[data-cn="' + cn + '"]');
                                if (d.status === 'success') {
                                    successCount++;
                                    badge.html(pi('#dcfce7','#15803d','✅','ส่งสำเร็จ'));
                                } else if (d.status === 'not_found' || (d.message || '').indexOf('ไม่มี LINE') >= 0) {
                                    notFoundCount++;
                                    badge.html(pi('#fef3c7','#92400e','🔍','ไม่พบ LINE: ' + (d.message||'')));
                                } else {
                                    failedCount++;
                                    badge.html(pi('#fee2e2','#b91c1c','❌','ส่งไม่สำเร็จ: ' + (d.message||'')));
                                }
                                details.push({
                                    customerno: cn,
                                    status: d.status === 'success' ? 'success' : (d.status === 'not_found' ? 'not_found' : 'failed'),
                                    message: d.message || ''
                                });
                            });
                        }

                        var alertClass = (failedCount > 0 || notFoundCount > 0) ? 'alert-warning' : 'alert-success';
                        var summaryParts = [];
                        if (successCount > 0) summaryParts.push('✅ สำเร็จ ' + successCount + ' ราย');
                        if (failedCount > 0)  summaryParts.push('❌ ไม่สำเร็จ ' + failedCount + ' ราย');
                        if (notFoundCount > 0) summaryParts.push('🔍 ไม่พบ LINE ' + notFoundCount + ' ราย');
                        var warningHtml = '';
                        if (response.parse_warning) {
                            warningHtml = '<div class="alert alert-danger" style="padding:6px 12px;margin-bottom:4px;"><b>' + response.parse_warning + '</b></div>';
                        }
                        var summary = summaryParts.join(' | ');
                        $('#tsResult').html(warningHtml + '<div class="alert ' + alertClass + '" style="padding:8px 12px;"><b>' + summary + '</b></div>').show();

                        // เปิด result modal (ใช้ฟังก์ชันร่วมกับ invoiceChat)
                        if (typeof showInvoiceResultModal === 'function' && details.length > 0) {
                            showInvoiceResultModal(summary, details, {
                                success: successCount, partial: 0, not_found: notFoundCount, failed: failedCount
                            });
                        }
                    },
                    error: function(xhr) {
                        btn.prop('disabled', false).html('<i class="fa fa-truck"></i> ส่งแจ้งค่าส่งไทย');
                        var errMsg = 'เกิดข้อผิดพลาด';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errMsg = xhr.responseJSON.message;
                        }
                        $('#tsResult').html('<div class="alert alert-danger">' + errMsg + '</div>').show();
                    }
                });
            });

        });

    </script>

    <!-- Standalone script: gallery + showImage (isolated from jQuery errors) -->
    <script>
        // === Product Image Simple Viewer (for product_image column) ===
        function showImage(imageUrl) {
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.85);z-index:9999;display:flex;align-items:center;justify-content:center;cursor:pointer;';
            var img = document.createElement('img');
            img.src = imageUrl;
            img.style.cssText = 'max-width:85%;max-height:85%;border-radius:8px;';
            overlay.appendChild(img);
            document.body.appendChild(overlay);
            overlay.onclick = function() { document.body.removeChild(overlay); };
        }
    </script>

    <!-- Box Image Gallery: fully JS-created, appended to body -->
    <script>
        var _gImgs = [], _gLabels = [], _gIdx = 0;
        var _gOverlay = null;

        // Create gallery overlay dynamically (appended to body, no CSS dependency)
        function _gCreate() {
            _gOverlay = document.createElement('div');
            _gOverlay.id = 'jsBoxGallery';
            _gOverlay.style.cssText = 'display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.92);z-index:99999;align-items:center;justify-content:center;flex-direction:column;';

            _gOverlay.innerHTML =
                '<button id="gClose" style="position:absolute;top:16px;right:20px;background:none;border:none;color:#fff;font-size:40px;cursor:pointer;z-index:100000;">&times;</button>' +
                '<button id="gPrev" style="position:absolute;top:50%;left:16px;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:36px;cursor:pointer;padding:12px 18px;border-radius:8px;">&#10094;</button>' +
                '<img id="gImg" src="" style="max-width:90%;max-height:75vh;object-fit:contain;border-radius:8px;user-select:none;" />' +
                '<button id="gNext" style="position:absolute;top:50%;right:16px;transform:translateY(-50%);background:rgba(255,255,255,0.15);border:none;color:#fff;font-size:36px;cursor:pointer;padding:12px 18px;border-radius:8px;">&#10095;</button>' +
                '<div id="gCounter" style="color:#fff;font-size:16px;margin-top:16px;font-weight:600;"></div>' +
                '<div id="gLabel" style="color:rgba(255,255,255,0.8);font-size:14px;margin-top:6px;"></div>';

            document.body.appendChild(_gOverlay);

            var gImg = document.getElementById('gImg');
            var _gZoom = 1;

            document.getElementById('gClose').onclick = _gClose;
            document.getElementById('gPrev').onclick = function() { if (_gIdx > 0) { _gIdx--; _gZoom=1; _gPanX=0; _gPanY=0; gImg.style.transform='scale(1)'; _gUpdate(); } };
            document.getElementById('gNext').onclick = function() { if (_gIdx < _gImgs.length - 1) { _gIdx++; _gZoom=1; _gPanX=0; _gPanY=0; gImg.style.transform='scale(1)'; _gUpdate(); } };
            _gOverlay.onclick = function(e) { if (e.target === _gOverlay) { if (_gZoom > 1) { _gZoom=1; _gPanX=0; _gPanY=0; gImg.style.transform='scale(1)'; gImg.style.cursor='zoom-in'; } else { _gClose(); } } };

            // Zoom + Pan state
            var _gPanX = 0, _gPanY = 0, _isDragging = false, _dragStartX = 0, _dragStartY = 0, _panStartX = 0, _panStartY = 0;
            gImg.style.cursor = 'zoom-in';
            gImg.style.transition = 'transform 0.2s ease';

            function _gApplyTransform() {
                gImg.style.transform = 'scale(' + _gZoom + ') translate(' + (_gPanX/_gZoom) + 'px,' + (_gPanY/_gZoom) + 'px)';
                gImg.style.cursor = _gZoom > 1 ? 'grab' : 'zoom-in';
            }
            function _gResetZoom() { _gZoom = 1; _gPanX = 0; _gPanY = 0; _gApplyTransform(); }

            // Click to zoom in, click again to zoom out
            gImg.addEventListener('click', function(e) {
                e.stopPropagation();
                if (_isDragging) return;
                if (_gZoom > 1) { _gResetZoom(); } else { _gZoom = 2.5; _gPanX = 0; _gPanY = 0; _gApplyTransform(); }
            });

            // Mouse wheel zoom at cursor position
            gImg.addEventListener('wheel', function(e) {
                e.preventDefault(); e.stopPropagation();
                gImg.style.transition = 'none';
                var oldZoom = _gZoom;
                if (e.deltaY < 0) { _gZoom = Math.min(_gZoom + 0.5, 6); }
                else { _gZoom = Math.max(_gZoom - 0.5, 1); }
                if (_gZoom === 1) { _gPanX = 0; _gPanY = 0; }
                _gApplyTransform();
                setTimeout(function() { gImg.style.transition = 'transform 0.2s ease'; }, 50);
            });

            // Mouse drag to pan when zoomed
            gImg.addEventListener('mousedown', function(e) {
                if (_gZoom <= 1) return;
                e.preventDefault();
                _isDragging = false;
                _dragStartX = e.clientX; _dragStartY = e.clientY;
                _panStartX = _gPanX; _panStartY = _gPanY;
                gImg.style.cursor = 'grabbing';
                gImg.style.transition = 'none';

                function onMove(ev) {
                    var dx = ev.clientX - _dragStartX, dy = ev.clientY - _dragStartY;
                    if (Math.abs(dx) > 3 || Math.abs(dy) > 3) _isDragging = true;
                    _gPanX = _panStartX + dx; _gPanY = _panStartY + dy;
                    gImg.style.transform = 'scale(' + _gZoom + ') translate(' + (_gPanX/_gZoom) + 'px,' + (_gPanY/_gZoom) + 'px)';
                }
                function onUp() {
                    document.removeEventListener('mousemove', onMove);
                    document.removeEventListener('mouseup', onUp);
                    gImg.style.cursor = _gZoom > 1 ? 'grab' : 'zoom-in';
                    gImg.style.transition = 'transform 0.2s ease';
                    setTimeout(function() { _isDragging = false; }, 50);
                }
                document.addEventListener('mousemove', onMove);
                document.addEventListener('mouseup', onUp);
            });

            // Touch pinch-to-zoom + drag to pan
            var _touchStartDist = 0, _touchStartZoom = 1;
            var _touchStartX = 0, _touchStartY = 0, _touchPanStartX = 0, _touchPanStartY = 0;
            gImg.addEventListener('touchstart', function(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    var dx = e.touches[0].clientX - e.touches[1].clientX;
                    var dy = e.touches[0].clientY - e.touches[1].clientY;
                    _touchStartDist = Math.sqrt(dx*dx + dy*dy);
                    _touchStartZoom = _gZoom;
                } else if (e.touches.length === 1 && _gZoom > 1) {
                    _touchStartX = e.touches[0].clientX; _touchStartY = e.touches[0].clientY;
                    _touchPanStartX = _gPanX; _touchPanStartY = _gPanY;
                    gImg.style.transition = 'none';
                }
            }, {passive:false});
            gImg.addEventListener('touchmove', function(e) {
                if (e.touches.length === 2) {
                    e.preventDefault();
                    var dx = e.touches[0].clientX - e.touches[1].clientX;
                    var dy = e.touches[0].clientY - e.touches[1].clientY;
                    var dist = Math.sqrt(dx*dx + dy*dy);
                    _gZoom = Math.min(Math.max(_touchStartZoom * (dist / _touchStartDist), 1), 6);
                    if (_gZoom === 1) { _gPanX = 0; _gPanY = 0; }
                    _gApplyTransform();
                } else if (e.touches.length === 1 && _gZoom > 1) {
                    e.preventDefault();
                    _gPanX = _touchPanStartX + (e.touches[0].clientX - _touchStartX);
                    _gPanY = _touchPanStartY + (e.touches[0].clientY - _touchStartY);
                    gImg.style.transform = 'scale(' + _gZoom + ') translate(' + (_gPanX/_gZoom) + 'px,' + (_gPanY/_gZoom) + 'px)';
                }
            }, {passive:false});
            gImg.addEventListener('touchend', function(e) {
                gImg.style.transition = 'transform 0.2s ease';
            });

            document.addEventListener('keydown', function(e) {
                if (!_gOverlay || _gOverlay.style.display === 'none') return;
                if (e.key === 'ArrowLeft' && _gIdx > 0) { _gIdx--; _gUpdate(); }
                if (e.key === 'ArrowRight' && _gIdx < _gImgs.length - 1) { _gIdx++; _gUpdate(); }
                if (e.key === 'Escape') _gClose();
            });

            // Touch swipe
            var startX = 0;
            _gOverlay.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; }, {passive:true});
            _gOverlay.addEventListener('touchend', function(e) {
                var diff = e.changedTouches[0].clientX - startX;
                if (Math.abs(diff) > 50) {
                    if (diff < 0 && _gIdx < _gImgs.length - 1) { _gIdx++; _gUpdate(); }
                    if (diff > 0 && _gIdx > 0) { _gIdx--; _gUpdate(); }
                }
            }, {passive:true});

            console.log('Gallery created and appended to body');
        }

        function openBoxGallery(imgEl) {
            if (!_gOverlay) _gCreate();
            _gImgs = []; _gLabels = []; _gIdx = 0;
            var rows = document.querySelectorAll('#dt-mant-table-1 tbody tr');
            for (var i = 0; i < rows.length; i++) {
                var imgs = rows[i].querySelectorAll('img.box-img');
                for (var j = 0; j < imgs.length; j++) {
                    var src = imgs[j].getAttribute('src');
                    if (src && src.indexOf('error-icon') === -1) {
                        _gImgs.push(src);
                        _gLabels.push('เลขกล่อง: ' + (imgs[j].getAttribute('data-boxno') || '-') + '  |  ลูกค้า: ' + (imgs[j].getAttribute('data-customer') || '-'));
                        if (imgs[j] === imgEl) _gIdx = _gImgs.length - 1;
                    }
                }
            }
            if (!_gImgs.length) return;
            _gUpdate();
            _gOverlay.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        }

        function _gUpdate() {
            var img = document.getElementById('gImg');
            var counter = document.getElementById('gCounter');
            var label = document.getElementById('gLabel');
            var prev = document.getElementById('gPrev');
            var next = document.getElementById('gNext');
            if (img) img.src = _gImgs[_gIdx];
            if (counter) counter.textContent = (_gIdx + 1) + ' / ' + _gImgs.length;
            if (label) label.textContent = _gLabels[_gIdx] || '';
            if (prev) prev.style.display = _gIdx > 0 ? 'block' : 'none';
            if (next) next.style.display = _gIdx < _gImgs.length - 1 ? 'block' : 'none';
        }

        function _gClose() {
            if (_gOverlay) _gOverlay.style.display = 'none';
            document.body.style.overflow = '';
        }

    </script>

<!-- Thai Shipping Notify Modal (redesigned to match Invoice Chat style) -->
<div class="modal fade" id="thaiShippingModal" tabindex="-1" role="dialog" aria-labelledby="thaiShippingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-height: 92vh; max-width: 1180px; width: 96%;">
        <div class="modal-content" style="max-height: 92vh; display: flex; flex-direction: column; border:0; border-radius:14px; box-shadow:0 12px 40px rgba(0,0,0,.15);">
            <div class="modal-header py-3" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4); color:#fff; flex-shrink: 0; border-radius:14px 14px 0 0;">
                <h5 class="modal-title" id="thaiShippingModalLabel" style="font-weight:700;"><i class="fa fa-truck"></i> แจ้งค่าส่งในไทย (LINE)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto; padding:20px 22px; background:#f8fafc;">

                <!-- ===== Top stats row ===== -->
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:14px; padding:10px 16px; background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <i class="fa fa-calendar" style="color:#0369a1;"></i>
                        <span style="color:#64748b; font-size:12px;">รอบปิดตู้:</span>
                        <strong style="color:#0369a1; font-size:14px;"><span id="tsEtdDisplay">-</span></strong>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; padding-left:14px; border-left:1px solid #e2e8f0;">
                        <i class="fa fa-users" style="color:#475569;"></i>
                        <span style="color:#64748b; font-size:12px;">ลูกค้า:</span>
                        <strong style="color:#1e293b; font-size:14px;"><span id="tsCustomerCount">0</span> ราย</strong>
                    </div>
                    <div id="tsLineChips" style="flex:1 1 auto; display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end;"></div>
                </div>

                <div class="row">
                    <!-- ===== LEFT: Customer list ===== -->
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <div style="background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:14px 16px; height:100%;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; margin-bottom:10px; flex-wrap:wrap;">
                                <strong style="color:#0369a1; font-size:14px;"><i class="fa fa-users"></i> รายชื่อลูกค้า</strong>
                                <button type="button" class="btn btn-sm" id="tsSelectAll" style="background:#f1f5f9; color:#475569; border:0; font-size:11px; font-weight:600; padding:4px 10px; border-radius:8px;">
                                    <i class="fa fa-check-square-o"></i> สลับทั้งหมด
                                </button>
                            </div>
                            <input type="text" id="tsSearch" placeholder="🔍 ค้นหา ANW-xxx..." style="width:100%; padding:6px 12px; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:8px; font-size:13px;">
                            <div id="tsCustomerList" style="max-height: 460px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; padding:6px; background:#fafbfc;">
                            </div>
                            <small class="text-muted mt-2 d-block" style="font-size:11px;">⚠️ ลูกค้าที่ไม่มี LINE จะถูกยกเลิกอัตโนมัติ</small>
                        </div>
                    </div>

                    <!-- ===== RIGHT: Upload + Message ===== -->
                    <div class="col-lg-8">
                        <!-- Upload card -->
                        <div style="background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:14px 16px; margin-bottom:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                <span style="color:#0369a1; font-weight:700; font-size:14px;"><i class="fa fa-file-text-o"></i> บิลจาก Shippop / ใบเสร็จ <span class="text-danger">*</span></span>
                                <small class="text-muted" style="font-size:11px;">JPG · PNG · PDF — เลือกได้หลายไฟล์</small>
                            </div>
                            <label for="tsInvoiceFile" id="tsDropZone" style="display:block; cursor:pointer; border:2px dashed #93c5fd; background:#f0f9ff; border-radius:10px; padding:18px; text-align:center; color:#0369a1; font-weight:600; transition:.15s;">
                                <i class="fa fa-cloud-upload" style="font-size:24px; color:#0ea5e9;"></i>
                                <div style="margin-top:6px; font-size:13px;">คลิกเพื่อเลือกไฟล์ — หรือลากไฟล์มาวาง</div>
                                <small class="text-muted d-block" style="font-weight:400; font-size:11px;">รองรับหลายไฟล์พร้อมกัน · PDF จะ auto-parse ให้</small>
                            </label>
                            <input type="file" id="tsInvoiceFile" accept="image/*,.pdf" multiple style="display:none;">
                            <div id="tsInvoicePreview" style="display:none; margin-top:10px;"></div>
                        </div>

                        <!-- Parse Preview card — แสดงรายการ shipment ที่ parse ได้จาก PDF -->
                        <div id="tsParseCard" style="display:none; background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:14px 16px; margin-bottom:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                <span style="color:#0369a1; font-weight:700; font-size:14px;"><i class="fa fa-list-alt"></i> พรีวิวรายการในบิล <small id="tsParseCount" class="text-muted" style="font-weight:400;"></small></span>
                                <div style="display:flex; gap:6px; align-items:center;">
                                    <span id="tsParseSum" style="font-size:11px; color:#475569;"></span>
                                    <button type="button" id="tsAddRow" class="btn btn-sm" style="background:#e0f2fe; color:#0369a1; border:0; font-size:11px; font-weight:600; padding:4px 10px; border-radius:8px;"><i class="fa fa-plus"></i> เพิ่มรายการ</button>
                                </div>
                            </div>
                            <div id="tsParseStatus" style="display:none; margin-bottom:8px;"></div>
                            <div style="max-height:300px; overflow-y:auto; border:1px solid #e2e8f0; border-radius:8px;">
                                <table id="tsParseTable" class="table table-sm mb-0" style="font-size:12px;">
                                    <thead style="background:#f1f5f9; position:sticky; top:0; z-index:1;">
                                        <tr>
                                            <th style="font-size:11px; padding:6px 8px;">#</th>
                                            <th style="font-size:11px; padding:6px 8px; min-width:130px;">เลขอ้างอิง <span class="text-danger">*</span></th>
                                            <th style="font-size:11px; padding:6px 8px; min-width:100px;">Courier</th>
                                            <th style="font-size:11px; padding:6px 8px; min-width:130px;">ปลายทาง</th>
                                            <th style="font-size:11px; padding:6px 8px; min-width:90px;">Box <span class="text-danger">*</span></th>
                                            <th style="font-size:11px; padding:6px 8px; min-width:80px;">ราคา (฿)</th>
                                            <th style="font-size:11px; padding:6px 8px; width:40px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="tsParseBody"></tbody>
                                </table>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size:11px;">💡 แก้ไขได้ — ✅ = Box match กับลูกค้าที่เลือก, ⚠️ = ไม่ match (ปล่อยว่างได้)</small>
                        </div>

                        <!-- Message card -->
                        <div style="background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:14px 16px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                <span style="color:#0369a1; font-weight:700; font-size:14px;"><i class="fa fa-commenting-o"></i> ข้อความเพิ่มเติม <small class="text-muted" style="font-weight:400;">(ไม่บังคับ)</small></span>
                            </div>
                            <small class="text-muted d-block mb-2" style="font-size:11px;">💡 ลากมุมขวาล่างเพื่อขยายช่อง</small>
                            <textarea class="form-control" id="tsMessage" rows="5"
                                placeholder="เช่น&#10;แจ้งยอดค่าส่งพัสดุในไทยครับ&#10;กรุณาชำระเงินภายใน 3 วันนะครับ"
                                style="font-size:14px; line-height:1.7; resize:vertical; min-height:140px; max-height:360px; border-radius:10px;"></textarea>
                        </div>

                        <!-- Info banner -->
                        <div style="padding:10px 14px; margin-top:12px; background:#cffafe; border-left:4px solid #06b6d4; border-radius:8px; color:#155e75; font-size:12px; line-height:1.6;">
                            <strong>📨 ระบบจะส่ง:</strong> รูปบิล/ใบเสร็จ Shippop ทั้งหมด + ข้อความเพิ่มเติม (ถ้ามี) ผ่าน LINE Official Account
                        </div>
                    </div>
                </div>
                <div id="tsResult" style="display: none;" class="mt-2"></div>
            </div>
            <div class="modal-footer py-2" style="flex-shrink: 0; background:#fff; border-radius:0 0 14px 14px;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-sm" id="tsSendBtn" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4); color:#fff; font-weight:700;"><i class="fa fa-truck"></i> ส่งแจ้งค่าส่งไทย</button>
            </div>
        </div>
    </div>
</div>

<!-- Thai Shipping Reminder Modal -->
<div class="modal fade" id="thaiRemindModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width:520px;">
        <div class="modal-content" style="max-height: 90vh; display: flex; flex-direction: column;">
            <div class="modal-header py-2" style="background: linear-gradient(135deg,#ef4444,#f97316); color: white; flex-shrink: 0;">
                <h5 class="modal-title"><i class="fa fa-bell"></i> แจ้งเตือนค่าส่งไทย (ค้างจ่าย)</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto;">
                <h6 class="mb-2"><b>รอบปิดตู้:</b> <span id="rmEtdDisplay" class="text-danger"></span></h6>
                <div id="rmLoading" style="text-align:center;padding:30px;">
                    <i class="fa fa-spinner fa-spin fa-2x" style="color:#ef4444;"></i>
                    <p class="mt-2 text-muted">กำลังตรวจสอบรายการค้างจ่าย...</p>
                </div>
                <div id="rmEmpty" style="display:none;text-align:center;padding:30px;">
                    <i class="fa fa-check-circle fa-3x" style="color:#22c55e;"></i>
                    <p class="mt-2" style="font-weight:600;color:#22c55e;">ไม่มีรายการค้างจ่ายในรอบนี้</p>
                </div>
                <div id="rmContent" style="display:none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span><b>ลูกค้าค้างจ่าย (<span id="rmCount">0</span> ราย)</b></span>
                        <button type="button" class="btn btn-outline-secondary btn-sm" id="rmSelectAll">เลือก/ยกเลิกทั้งหมด</button>
                    </div>
                    <div id="rmCustomerList" style="max-height:300px; overflow-y:auto; border:1px solid #fee2e2; border-radius:6px; padding:8px; background:#fff5f5;">
                    </div>
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:14px; margin-top:12px;">
                        <h6 style="font-weight:700; color:#dc2626; margin-bottom:10px;"><i class="fa fa-commenting-o"></i> ข้อความเพิ่มเติม <small class="text-muted">(ไม่บังคับ)</small></h6>
                        <textarea class="form-control form-control-sm" id="rmMessage" rows="3" placeholder="เช่น &#10;รบกวนชำระภายในวันนี้นะครับ"></textarea>
                    </div>
                </div>
                <div id="rmResult" style="display:none;" class="mt-2"></div>
            </div>
            <div class="modal-footer py-2" style="flex-shrink: 0;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-sm" id="rmSendBtn" style="background:linear-gradient(135deg,#ef4444,#f97316); color:#fff; font-weight:700; display:none;">
                    <i class="fa fa-bell"></i> ส่งแจ้งเตือน LINE
                </button>
            </div>
        </div>
    </div>
</div>

<!-- 📢 Broadcast ETD Modal -->
<div class="modal fade" id="broadcastEtdModal" tabindex="-1" role="dialog" aria-labelledby="broadcastEtdModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header py-2" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff;">
                <h5 class="modal-title" id="broadcastEtdModalLabel"><i class="fa fa-bullhorn"></i> บรอดแคสรอบปิดตู้</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="padding: 18px 22px;">
                <div class="row mb-2">
                    <div class="col-md-6">
                        <small class="text-muted">รอบปิดตู้:</small>
                        <div><strong id="bcEtdDisplay" class="text-primary"></strong></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">จำนวนลูกค้าในรอบนี้:</small>
                        <div><strong id="bcCustomerCount" class="text-warning">-</strong> ราย</div>
                    </div>
                </div>

                <hr style="margin:10px 0;">

                <label class="mt-2"><strong>📋 เลือกข้อความสำเร็จรูป (หรือพิมพ์เอง):</strong></label>
                <div id="bcPresetGroup" class="d-flex flex-wrap" style="gap:6px; margin-bottom:12px;">
                    <button type="button" class="btn btn-sm btn-outline-warning bc-preset-btn"
                            data-title="🕐 แจ้งสินค้าล่าช้ากว่ากำหนด" data-color="#f59e0b"
                            data-message="เรียนลูกค้าทุกท่าน&#10;&#10;เนื่องจากสถานการณ์การขนส่ง สินค้าในรอบปิดตู้นี้จะมาถึงประเทศไทยล่าช้ากว่ากำหนดประมาณ 3-5 วัน&#10;&#10;ทางทีมงานต้องขออภัยในความไม่สะดวก และจะแจ้งให้ทราบทันทีเมื่อสินค้าถึงโกดังไทย ขอบคุณที่เข้าใจครับ 🙏">
                        🕐 ล่าช้ากว่ากำหนด
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-success bc-preset-btn"
                            data-title="📦 สินค้ามาถึงโกดังไทยแล้ว" data-color="#10b981"
                            data-message="แจ้งข่าวดี! สินค้ารอบนี้ถึงโกดังประเทศไทยเรียบร้อยแล้ว 🎉&#10;&#10;ทีมงานกำลังคัดแยกและเตรียมจัดส่งภายใน 1-2 วันทำการ สามารถเช็คสถานะการจัดส่งผ่านระบบได้ตลอดเวลา ขอบคุณที่ใช้บริการครับ 🙏">
                        📦 ถึงโกดังไทยแล้ว
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-info bc-preset-btn"
                            data-title="🚚 เริ่มจัดส่งในประเทศไทย" data-color="#0ea5e9"
                            data-message="สินค้าของท่านในรอบนี้กำลังจัดส่งโดยขนส่งในประเทศไทย 🚚&#10;&#10;กรุณาเตรียมรับสินค้า สามารถติดตามสถานะได้ผ่านระบบ ขอบคุณที่ใช้บริการครับ 🙏">
                        🚚 เริ่มจัดส่ง
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger bc-preset-btn"
                            data-title="⚠️ แจ้งเหตุขัดข้องการขนส่ง" data-color="#ef4444"
                            data-message="เรียนลูกค้าทุกท่าน&#10;&#10;ทางทีมงานได้รับแจ้งจากบริษัทขนส่งว่ามีเหตุขัดข้องในรอบนี้ จะแจ้งความคืบหน้าให้ทราบโดยเร็วที่สุด&#10;&#10;ขออภัยในความไม่สะดวกครับ 🙏">
                        ⚠️ เหตุขัดข้อง
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary bc-preset-btn"
                            data-title="📌 แจ้งข่าวสาร" data-color="#1D8AC9" data-message="">
                        📝 พิมพ์เอง
                    </button>
                </div>

                <div class="form-group">
                    <label><strong>หัวข้อแจ้งเตือน:</strong> <span class="text-danger">*</span></label>
                    <input type="text" id="bcTitle" class="form-control" maxlength="80" placeholder="หัวข้อสั้นๆ เช่น แจ้งสินค้าล่าช้า">
                </div>

                <div class="form-group">
                    <label class="d-flex justify-content-between align-items-center">
                        <span><strong>ข้อความถึงลูกค้า:</strong> <span class="text-danger">*</span></span>
                        <small class="text-muted" style="font-weight:400; font-size:11px;">💡 ลากมุมขวาล่างเพื่อขยายช่อง</small>
                    </label>
                    <textarea id="bcMessage" class="form-control" rows="8" maxlength="700"
                              placeholder="พิมพ์ข้อความถึงลูกค้า..."
                              style="min-height:180px; max-height:520px; resize:vertical; line-height:1.6; font-size:14px;"></textarea>
                    <small class="text-muted">เหลือ <span id="bcCharLeft">700</span> ตัวอักษร</small>
                </div>

                <div class="form-group">
                    <label><strong>สีหัว Flex Message:</strong></label>
                    <div class="d-flex" style="gap:8px;">
                        @php $colors = [
                            ['#f59e0b','ส้ม (แจ้งเตือน)'],
                            ['#10b981','เขียว (ข่าวดี)'],
                            ['#0ea5e9','ฟ้า (ข้อมูล)'],
                            ['#ef4444','แดง (สำคัญ)'],
                            ['#1D8AC9','น้ำเงิน (ทั่วไป)'],
                        ]; @endphp
                        @foreach($colors as $c)
                            <button type="button" class="bc-color-btn" data-color="{{ $c[0] }}" title="{{ $c[1] }}"
                                style="width:32px; height:32px; border-radius:8px; border:2px solid #fff; box-shadow:0 0 0 2px #e5e7eb; background:{{ $c[0] }}; cursor:pointer;"></button>
                        @endforeach
                        <input type="hidden" id="bcHeaderColor" value="#f59e0b">
                        <span style="margin-left:auto; font-size:13px; color:#64748b;">เลือก: <span id="bcColorName" style="font-weight:700; color:#f59e0b;">ส้ม</span></span>
                    </div>
                </div>

                <hr style="margin:10px 0;">

                <div class="form-group">
                    <label class="d-flex align-items-center" style="gap:6px;">
                        <input type="checkbox" id="bcOnlySelected">
                        <span>ส่งเฉพาะลูกค้าที่ติ๊กในตาราง (default = ส่งทุกคนในรอบนี้)</span>
                    </label>
                </div>

                <div id="bcPreview" style="background:#fff7ed; border:1px dashed #fde68a; border-radius:10px; padding:12px 14px; font-size:13px; color:#92400e;">
                    💡 <strong>ตัวอย่าง alt text:</strong> <span id="bcAltText" style="color:#1f2937;">📢 หัวข้อ (รอบ dd/mm/yyyy)</span>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-sm" id="bcSendBtn" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; font-weight:700;">
                    <i class="fa fa-paper-plane"></i> ส่งบรอดแคส LINE
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Invoice Chat Modal -->
<div class="modal fade" id="invoiceChatModal" tabindex="-1" role="dialog" aria-labelledby="invoiceChatModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-height: 92vh; max-width: 1180px; width: 96%;">
        <div class="modal-content" style="max-height: 92vh; display: flex; flex-direction: column; border:0; border-radius:14px; box-shadow:0 12px 40px rgba(0,0,0,.15);">
            <div class="modal-header py-3" style="background:linear-gradient(135deg,#0084FF,#0c5e8e); color:#fff; flex-shrink: 0; border-radius:14px 14px 0 0;">
                <h5 class="modal-title" id="invoiceChatModalLabel" style="font-weight:700;"><i class="fa fa-paper-plane"></i> ส่งบิลผ่าน SKJ Chat</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: white; opacity:.9;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto; flex: 1 1 auto; padding:20px 22px; background:#f8fafc;">

                <!-- ===== Top stats row ===== -->
                <div style="display:flex; flex-wrap:wrap; align-items:center; gap:14px; padding:10px 16px; background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); margin-bottom:14px;">
                    <div style="display:flex; align-items:center; gap:6px;">
                        <i class="fa fa-calendar" style="color:#0c5e8e;"></i>
                        <span style="color:#64748b; font-size:12px;">รอบปิดตู้:</span>
                        <strong style="color:#0c5e8e; font-size:14px;"><span id="invoiceChatEtdDisplay">-</span></strong>
                    </div>
                    <div style="display:flex; align-items:center; gap:6px; padding-left:14px; border-left:1px solid #e2e8f0;">
                        <i class="fa fa-users" style="color:#475569;"></i>
                        <span style="color:#64748b; font-size:12px;">ลูกค้า:</span>
                        <strong style="color:#1e293b; font-size:14px;"><span id="invoiceChatCustomerCount">0</span> ราย</strong>
                    </div>
                    <div id="invoiceChatConnectionChips" style="flex:1 1 auto; display:flex; flex-wrap:wrap; gap:6px; justify-content:flex-end;"></div>
                </div>

                <div class="row">
                    <!-- ===== LEFT: Customer list ===== -->
                    <div class="col-lg-4 mb-3 mb-lg-0">
                        <div style="background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:14px 16px; height:100%;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:6px; margin-bottom:10px; flex-wrap:wrap;">
                                <strong style="color:#0c5e8e; font-size:14px;"><i class="fa fa-users"></i> รายชื่อลูกค้า</strong>
                                <button type="button" class="btn btn-sm" id="invoiceChatSelectAll" style="background:#f1f5f9; color:#475569; border:0; font-size:11px; font-weight:600; padding:4px 10px; border-radius:8px;">
                                    <i class="fa fa-check-square-o"></i> สลับทั้งหมด
                                </button>
                            </div>
                            <input type="text" id="invoiceChatSearch" placeholder="🔍 ค้นหา ANW-xxx..." style="width:100%; padding:6px 12px; border:1px solid #cbd5e1; border-radius:8px; margin-bottom:8px; font-size:13px;">
                            <div id="invoiceChatCustomerList" style="max-height: 460px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 10px; padding:6px; background:#fafbfc;">
                            </div>
                            <small class="text-muted mt-2 d-block" style="font-size:11px;">⚠️ ลูกค้าที่ยังไม่เชื่อมต่อ จะถูกยกเลิกอัตโนมัติ</small>
                        </div>
                    </div>

                    <!-- ===== RIGHT: Message + fee ===== -->
                    <div class="col-lg-8">
                        <div style="background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:14px 16px; margin-bottom:12px;">
                            <div style="display:flex; justify-content:space-between; align-items:center; gap:8px; margin-bottom:8px; flex-wrap:wrap;">
                                <span style="color:#0c5e8e; font-weight:700; font-size:14px;"><i class="fa fa-commenting-o"></i> ข้อความแจ้งลูกค้า</span>
                                <div style="display:flex; flex-wrap:wrap; gap:4px; align-items:center;">
                                    <span style="font-size:11px; color:#64748b;">แทรกตัวแปร:</span>
                                    <code class="ic-var" data-var="@{{จำนวน}}" style="cursor:pointer; background:#dbeafe; color:#1e40af; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:600;">@{{จำนวน}}</code>
                                    <code class="ic-var" data-var="@{{รวม}}" style="cursor:pointer; background:#dbeafe; color:#1e40af; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:600;">@{{รวม}}</code>
                                    <code class="ic-var" data-var="@{{ค่าแมส}}" style="cursor:pointer; background:#fef3c7; color:#92400e; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:600;">@{{ค่าแมส}}</code>
                                    <code class="ic-var" data-var="@{{ยอดรวมทั้งหมด}}" style="cursor:pointer; background:#fef3c7; color:#92400e; padding:2px 7px; border-radius:6px; font-size:11px; font-weight:600;">@{{ยอดรวมทั้งหมด}}</code>
                                </div>
                            </div>
                            <small class="text-muted d-block mb-2" style="font-size:11px;">💡 ลากมุมขวาล่างเพื่อขยายช่อง — คลิก chip ด้านบนเพื่อแทรกตัวแปรเข้าตำแหน่ง cursor</small>
                            <textarea class="form-control" id="invoiceChatMessageTemplate"
                                placeholder="พิมพ์ข้อความแจ้งลูกค้า..."
                                style="font-size:14px; line-height:1.7; resize:vertical; min-height:260px; max-height:520px; border-radius:10px;"></textarea>
                            <small class="text-muted d-block mt-1" style="font-size:11px;">ตัวแปร <code>@{{จำนวน}}</code> และ <code>@{{รวม}}</code> จะถูกแทนที่อัตโนมัติตามข้อมูลแต่ละลูกค้า</small>
                        </div>

                        <div style="display:flex; gap:12px; align-items:stretch; flex-wrap:wrap;">
                            <div style="background:#fff; border-radius:12px; box-shadow:0 1px 4px rgba(0,0,0,.04); padding:12px 14px; flex:1 1 240px;">
                                <label class="mb-2 d-block" style="font-size:13px;"><span style="color:#92400e; font-weight:700;"><i class="fa fa-motorcycle"></i> ค่าแมสเซ็นเจอร์</span> <small class="text-muted" style="font-weight:400;">(ไม่บังคับ)</small></label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend"><span class="input-group-text" style="background:#fef3c7; border-color:#fcd34d; color:#92400e; font-weight:700;">฿</span></div>
                                    <input type="number" class="form-control" id="invoiceChatMessengerFee" placeholder="0" min="0" step="1" value="" style="border-color:#fcd34d; font-weight:600;">
                                    <div class="input-group-append"><span class="input-group-text" style="background:#fef3c7; border-color:#fcd34d; color:#92400e;">บาท</span></div>
                                </div>
                            </div>
                            <div style="padding:12px 14px; background:#dbeafe; border-left:4px solid #1D8AC9; border-radius:8px; color:#1e3a8a; font-size:12px; line-height:1.6; flex:2 1 320px;">
                                <strong>📨 ระบบจะส่ง 3 อย่าง:</strong><br>1) ข้อความด้านบน &nbsp; 2) PDF ใบแจ้งหนี้ &nbsp; 3) QR PromptPay (สร้างอัตโนมัติ)
                            </div>
                        </div>
                    </div>
                </div>
                <div id="invoiceChatResult" style="display: none;" class="mt-2"></div>
            </div>
            <div class="modal-footer py-2" style="flex-shrink: 0; background:#fff; border-radius:0 0 14px 14px;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">ปิด</button>
                <button type="button" class="btn btn-sm" id="invoiceChatRemindBtn" style="background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff; font-weight:700;"><i class="fa fa-bell"></i> เตือนชำระเงิน</button>
                <button type="button" class="btn btn-sm" id="invoiceChatSendBtn" style="background:linear-gradient(135deg,#0084FF,#0c5e8e); color:#fff; font-weight:700;"><i class="fa fa-paper-plane"></i> แจ้งค่านำเข้า</button>
            </div>
        </div>
    </div>
</div>

<!-- Batch Recipient Modal -->
<div id="batchRecipientModal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; z-index:9999; background:rgba(0,0,0,0.5); backdrop-filter:blur(2px);">
    <div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); background:white; border-radius:20px; width:95%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 25px 60px rgba(0,0,0,0.3);">
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
        <div style="padding:20px 28px;">
            <div style="margin-bottom:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">วิธีจัดส่ง</label>
                <select id="batch_delivery_type" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem; color:#1e293b;">
                    <option value="3" selected>เพิ่มที่อยู่เอง</option>
                    <option value="1">รับเอง</option>
                </select>
            </div>
            <div id="batchPickupNameFields" style="display:none;">
                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ชื่อผู้รับ</label>
                    <input type="text" id="batch_pickup_name" placeholder="ชื่อผู้มารับ" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>
            </div>
            <div id="batchRecipientFields">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ชื่อ-นามสกุล</label>
                        <input type="text" id="batch_fullname" placeholder="ชื่อ-นามสกุล" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_fullname-results" class="batch-search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">เบอร์โทร</label>
                        <input type="text" id="batch_mobile" placeholder="เบอร์โทร" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_mobile-results" class="batch-search-results"></div>
                    </div>
                </div>
                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ที่อยู่</label>
                    <input type="text" id="batch_address" placeholder="บ้านเลขที่ ซอย ถนน" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">แขวง/ตำบล</label>
                        <input type="text" id="batch_subdistrict" placeholder="ตำบล" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_subdistrict-results" class="batch-search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">เขต/อำเภอ</label>
                        <input type="text" id="batch_district" placeholder="อำเภอ" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_district-results" class="batch-search-results"></div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:10px;">
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">จังหวัด</label>
                        <input type="text" id="batch_province" placeholder="จังหวัด" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_province-results" class="batch-search-results"></div>
                    </div>
                    <div class="position-relative">
                        <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">รหัสไปรษณีย์</label>
                        <input type="text" id="batch_postcode" placeholder="รหัสไปรษณีย์" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                        <div id="batch_postcode-results" class="batch-search-results"></div>
                    </div>
                </div>
            </div>
            <div style="margin-bottom:10px; margin-top:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">หมายเหตุ <span style="font-weight:400; color:#94a3b8;">(ไม่บังคับ)</span></label>
                <input type="text" id="batch_note" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
            </div>
        </div>
        <div style="padding:16px 28px 24px; border-top:1px solid #f1f5f9; display:flex; gap:10px; justify-content:flex-end;">
            <button onclick="closeBatchRecipientModal()" style="padding:12px 24px; background:#f1f5f9; color:#64748b; border:1.5px solid #e2e8f0; border-radius:12px; font-size:0.9rem; font-weight:600; cursor:pointer;">ยกเลิก</button>
            <button onclick="submitBatchRecipient()" id="batchSubmitBtn" style="padding:12px 28px; background:linear-gradient(135deg,#1D8AC9,#0ea5e9); color:white; border:none; border-radius:12px; font-size:0.9rem; font-weight:600; cursor:pointer; box-shadow:0 4px 15px rgba(29,138,201,0.3);"><i class="fa fa-check"></i> บันทึก</button>
        </div>
    </div>
</div>

<style>
    #batchRecipientModal .batch-search-results {
        position: absolute; top: 100%; left: 0; right: 0;
        background: white; border: 1px solid #e2e8f0; border-radius: 8px;
        max-height: 200px; overflow-y: auto; z-index: 10000;
        box-shadow: 0 8px 25px rgba(0,0,0,0.15); display: none;
    }
    #batchRecipientModal .batch-search-results .search-result-item {
        padding: 10px 14px; cursor: pointer; font-size: 0.85rem; border-bottom: 1px solid #f8fafc;
    }
    #batchRecipientModal .batch-search-results .search-result-item:hover { background: #f0f9ff; }
</style>

<script>
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
        $('#batch_delivery_type').val('3');
        $('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode, #batch_note, #batch_pickup_name').val('');
        $('#batchRecipientFields').show();
        $('#batchPickupNameFields').hide();
        $('#batchRecipientModal').fadeIn(200);
        initBatchCustomerSearch();
    }

    function closeBatchRecipientModal() {
        $('#batchRecipientModal').fadeOut(200);
        $('#batchRecipientModal .batch-search-results').hide().empty();
    }

    $('#batch_delivery_type').on('change', function() {
        var val = $(this).val();
        if (val === '3') {
            $('#batchRecipientFields').slideDown(200);
            $('#batchPickupNameFields').slideUp(200);
        } else {
            $('#batchRecipientFields').slideUp(200);
            $('#batchPickupNameFields').slideDown(200);
        }
    });

    function submitBatchRecipient() {
        var deliveryType = $('#batch_delivery_type').val();
        var batchNote = $('#batch_note').val().trim();
        var data = { ids: batchSelectedIds, delivery_type_id: parseInt(deliveryType), _token: '{{ csrf_token() }}' };
        if (batchNote) { data.note = batchNote; }

        if (deliveryType === '3') {
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

        var typeName = deliveryType === '1' ? ('รับเอง: ' + ($('#batch_pickup_name').val().trim() || '-')) : data.delivery_fullname;

        Swal.fire({
            title: 'ยืนยันกำหนดผู้รับ?',
            html: 'อัพเดท <b>' + batchSelectedIds.length + '</b> รายการ<br>ผู้รับ: <b>' + typeName + '</b>',
            icon: 'question', showCancelButton: true, confirmButtonColor: '#1D8AC9', cancelButtonColor: '#94a3b8',
            confirmButtonText: 'ยืนยัน', cancelButtonText: 'ยกเลิก'
        }).then(function(result) {
            if (result.isConfirmed) {
                $('#batchSubmitBtn').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> กำลังบันทึก...');
                $.ajax({
                    url: '{{ route("batch.update.recipient") }}',
                    type: 'POST', data: data,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    success: function(res) {
                        closeBatchRecipientModal();
                        $('#batchSubmitBtn').prop('disabled', false).html('<i class="fa fa-check"></i> บันทึก');
                        Swal.fire({ icon: 'success', title: 'สำเร็จ!', text: res.message, confirmButtonColor: '#1D8AC9', timer: 2500 });
                        $('#dt-mant-table-1').DataTable().ajax.reload();
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

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#batch_fullname, #batch_fullname-results').length) $('#batch_fullname-results').hide();
            if (!$(e.target).closest('#batch_mobile, #batch_mobile-results').length) $('#batch_mobile-results').hide();
        });
    }
</script>

@endsection
