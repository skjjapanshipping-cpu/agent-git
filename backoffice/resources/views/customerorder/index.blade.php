@extends('layouts.app')

@section('template_title')
    Customerorder
@endsection

@section('extra-css')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* ========================================
           COMPLETE LAYOUT OVERRIDE - Fix Paper Dashboard (From My Shipping)
           ======================================== */
        
        input[type='submit'].disabled { opacity: 0.5; pointer-events: none; }

        /* Global Overflow */
        html, body {
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100vw !important;
        }

        /* Wrapper - Flexbox */
        .wrapper {
            display: flex !important;
            flex-direction: row !important;
            min-height: 100vh;
            position: relative !important;
            width: 100vw !important;
            overflow-x: hidden !important;
        }

        /* Sidebar - Fixed & Flex Column */
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

        /* HIDE OLD NAVBAR/HEADERS */
        .navbar-modern, 
        .navbar, 
        .navbar-expand-lg,
        .panel-header,
        .main-panel > .panel-header {
            display: none !important;
        }

        /* Ensure content is visible and full size */
        .main-panel > .content {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }

        .table td, .table th {
            white-space: nowrap; /* ปรับให้ข้อมูลในตารางไม่ขึ้นบรรทัดใหม่ */
        }
        th,td{
            text-align: center;
        }
        .table td .link-cell { /* สร้างคลาสใหม่สำหรับ cell ที่มีลิงก์ */
            max-width: 200px; /* กำหนดความกว้างสูงสุดของลิงก์ */
            overflow: hidden;
            text-overflow: ellipsis; /* แสดง ... เมื่อข้อความเกิน */
            white-space: nowrap;
        }

        .spinner-border {
            width: 1.5rem;
            height: 1.5rem;
            border: 0.2em solid rgba(0, 0, 0, 0.1);
            border-radius: 50%;
            border-top-color: #000;
            animation: spin 0.75s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .btn-edit.disabled {
            cursor: not-allowed;
            pointer-events: none;
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

        .bg-lightblue {
            background-color: #007bff;
        }

        .bg-black {
            background-color: #000;
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

        .form-check-label {
            white-space: nowrap;
        }

        /* Boss dropdown สลับสีน้ำเงินกับชมพูเข้ม */
        select.boss_id option.boss-blue {
            color: #007bff !important;
            font-weight: bold;
        }
        select.boss_id option.boss-pink {
            color: #e91e63 !important;
            font-weight: bold;
        }

        /* === Modern Admin UI (matching My Shipping) === */
        .card { border:none; border-radius:12px; box-shadow:0 2px 12px rgba(0,0,0,0.08); overflow:visible; }
        .card-header { background:#fff !important; border-bottom:1px solid #f0f0f0; padding:12px 20px !important; border-radius:12px 12px 0 0 !important; }
        .card-body { padding:16px 20px !important; }
        .toolbar-row { display:flex; flex-wrap:wrap; gap:8px; align-items:center; padding:4px 0; }
        .toolbar-row + .toolbar-row { border-top:1px solid #f0f0f0; margin-top:6px; padding-top:10px; }
        .toolbar-group { display:flex; flex-wrap:wrap; gap:6px; align-items:center; }
        .toolbar-group-label { font-size:11px; color:#94a3b8; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; margin-right:2px; white-space:nowrap; }
        .toolbar-spacer { flex:1; }
        .btn-modern { border-radius:8px !important; font-size:12px !important; font-weight:600 !important; padding:6px 14px !important; border:none !important; transition:all 0.2s !important; display:inline-flex !important; align-items:center !important; gap:5px !important; white-space:nowrap !important; }
        .btn-modern:hover { transform:translateY(-1px); box-shadow:0 4px 12px rgba(0,0,0,0.15) !important; }
        .btn-modern i { font-size:13px; }
        .btn-modern.btn-red { background:linear-gradient(135deg,#dc3545,#c82333) !important; color:#fff !important; }
        .btn-modern.btn-blue { background:linear-gradient(135deg,#0084FF,#0066cc) !important; color:#fff !important; }
        .btn-modern.btn-green { background:linear-gradient(135deg,#28a745,#1e7e34) !important; color:#fff !important; }
        .btn-modern.btn-orange { background:linear-gradient(135deg,#fd7e14,#e8690a) !important; color:#fff !important; }
        .btn-modern.btn-dark { background:linear-gradient(135deg,#343a40,#23272b) !important; color:#fff !important; }
        .btn-modern.btn-outline { background:#fff !important; border:2px solid #dee2e6 !important; color:#495057 !important; }
        .btn-modern.btn-outline:hover { border-color:#dc3545 !important; color:#dc3545 !important; background:#fff8f8 !important; }
        .btn-modern.disabled, .btn-modern[disabled] { opacity:0.45 !important; pointer-events:none !important; transform:none !important; box-shadow:none !important; }
        .date-filter-bar { display:flex; flex-wrap:wrap; gap:12px; align-items:center; padding:12px 16px; background:#f8fafc; border-radius:10px; margin-bottom:16px; border:1px solid #e2e8f0; }
        .date-filter-bar label { font-size:12px; font-weight:700; color:#475569; margin:0; white-space:nowrap; }
        .date-filter-bar input { border-radius:8px; border:2px solid #e2e8f0; padding:6px 12px; font-size:13px; max-width:180px; transition:border-color 0.2s; }
        .date-filter-bar input:focus { border-color:#dc3545; outline:none; box-shadow:0 0 0 3px rgba(220,53,69,0.1); }
        .stats-row { display:flex; flex-wrap:wrap; gap:12px; justify-content:center; margin:16px 0; }
        .stat-card { background:#fff; border:1px solid #e2e8f0; border-radius:10px; padding:12px 20px; text-align:center; min-width:130px; flex:1; max-width:200px; transition:all 0.2s; }
        .stat-card:hover { border-color:#dc3545; box-shadow:0 4px 12px rgba(220,53,69,0.1); }
        .stat-card .stat-label { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; }
        .stat-card .stat-value { font-size:20px; font-weight:800; color:#1e293b; margin-top:2px; }
        .stat-card .stat-unit { font-size:12px; color:#64748b; font-weight:500; }
        .stat-card.stat-highlight { border-left:4px solid #dc3545; }
        .table thead th { background:#f8fafc !important; color:#475569 !important; font-size:11px !important; font-weight:700 !important; text-transform:uppercase !important; letter-spacing:0.3px !important; border-bottom:2px solid #e2e8f0 !important; padding:10px 8px !important; }
        .table tbody td { font-size:12px; padding:8px !important; vertical-align:middle !important; }
        .table-striped tbody tr:nth-of-type(odd) { background-color:rgba(248,250,252,0.7) !important; }
        .table-hover tbody tr:hover { background-color:rgba(220,53,69,0.04) !important; }
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
            .toolbar-row { flex-direction:column; align-items:stretch; gap:6px; }
            .toolbar-row + .toolbar-row { margin-top:4px; padding-top:8px; }
            .toolbar-spacer { display:none; }
            .toolbar-group { flex-wrap:wrap; justify-content:flex-start; gap:4px; }
            .toolbar-group-label { width:100%; margin-bottom:2px; font-size:10px; }
            .page-title { font-size:15px; }
            .btn-modern { font-size:11px !important; padding:5px 10px !important; border-radius:6px !important; }
            .date-filter-bar { flex-direction:column; align-items:stretch; gap:6px; padding:10px 12px; }
            .date-filter-bar input { max-width:100%; width:100%; }
            .date-filter-bar label { font-size:11px; }
            .stats-row { gap:8px; margin:10px 0; }
            .stat-card { min-width:0; padding:8px 10px; flex:1 1 calc(50% - 8px); max-width:none; }
            .stat-card .stat-label { font-size:9px; }
            .stat-card .stat-value { font-size:16px; }
            .stat-card .stat-unit { font-size:10px; }
            .table thead th { font-size:9px !important; padding:6px 4px !important; }
            .table tbody td { font-size:11px !important; padding:6px 4px !important; }
        }
        @media (max-width: 480px) {
            .toolbar-group { gap:3px; }
            .btn-modern { font-size:10px !important; padding:4px 8px !important; gap:3px !important; }
            .btn-modern i { font-size:11px; }
            .page-title { font-size:13px; gap:5px; }
            .stat-card { flex:1 1 100%; }
            .date-filter-bar { padding:8px 10px; gap:4px; }
        }

        /* SIDEBAR LOGOUT STYLE MATCHING MY SHIPPING */
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
            background: rgba(230, 57, 70, 0.8) !important;
        }
        
        .sidebar-logout .logout-link i {
            font-size: 1rem;
        }
        
        @media (max-width: 992px) {
            .sidebar-logout {
                border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
                padding: 20px !important;
            }
        }

    </style>
@endsection
@section('extra-script')
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.11/clipboard.min.js"></script>

    <script>
        function resorting() {
            $('.no').each(function (index) {
                $(this).text(index + 1);
            });
        }

        function showImage(imageUrl) {
            // สร้าง element สำหรับแสดงภาพใหญ่
            var overlay = document.createElement('div');
            overlay.style.position = 'fixed';
            overlay.style.top = '0';
            overlay.style.left = '0';
            overlay.style.width = '100%';
            overlay.style.height = '100%';
            overlay.style.backgroundColor = 'rgba(0, 0, 0, 0.7)';
            overlay.style.zIndex = '9999';
            overlay.style.display = 'flex';
            overlay.style.alignItems = 'center';
            overlay.style.justifyContent = 'center';

            var img = document.createElement('img');
            img.src = imageUrl;
            img.style.maxWidth = '80%';
            img.style.maxHeight = '80%';

            // เพิ่มภาพลงใน overlay
            overlay.appendChild(img);

            // เพิ่ม overlay ลงใน body
            document.body.appendChild(overlay);

            // เมื่อคลิกที่ overlay ให้ซ่อนภาพใหญ่
            overlay.onclick = function() {
                document.body.removeChild(overlay);
            }
        }

        function getNameFromDomain(urlData) {
            let domainName;
            try {
                const url = new URL(urlData);
                domainName = url.hostname; // Extracts the domain

                const parts = domainName.split('.');
                const numParts = parts.length;

                // Define common TLDs and SLDs for various domains (can be expanded as needed)
                const commonTLDs = ['jp', 'co', 'com', 'net', 'org', 'gov', 'edu', 'th', 'co.th'];
                const commonSLDs = ['co', 'ac', 'ne', 'or', 'com', 'net', 'org', 'edu', 'th'];

                if (numParts > 2) {
                    // For domains with subdomains, handle based on common TLD and SLD cases
                    const tld = parts[numParts - 1];
                    const sld = parts[numParts - 2];
                    const secondLastPart = parts[numParts - 3];

                    if (commonTLDs.includes(tld)) {
                        if (commonSLDs.includes(sld)) {
                            // For cases like "example.co.jp" or "example.ac.jp"
                            domainName = secondLastPart;
                        } else {
                            // For domains like "example.jp" or "example.com"
                            domainName = sld;
                        }
                    } else {
                        // Handle other complex domains, extract the most relevant part
                        domainName = sld;
                    }
                } else if (numParts === 2) {
                    // Handle simpler domains with only two parts
                    domainName = parts[0];
                } else {
                    // Fallback to the full hostname if not handled
                    domainName = domainName;
                }

                // Special handling to remove "www" or other common subdomains if present
                if (domainName.startsWith('www.')) {
                    domainName = domainName.substring(4);
                }

                domainName = domainName.charAt(0).toUpperCase() + domainName.slice(1);

            } catch (e) {
                domainName = urlData; // If there's an error, fallback to the full URL
            }
            return domainName;
        }

        $(function () {
            // ตั้งค่า default ให้แสดงวันที่ปัจจุบันหรือวันที่ที่เลือกไว้เดิม
            function setDefaultDate() {
                // ตรวจสอบว่ามีวันที่ที่เลือกไว้ใน localStorage หรือไม่
                const savedDate = localStorage.getItem('orderStartDate');
                
                if (savedDate) {
                    // ถ้ามีวันที่ที่เลือกไว้ ให้ใช้ค่านั้น (เอาแค่วันที่ ไม่เอาเวลา)
                    const dateOnly = savedDate.split('T')[0];
                    $('#start_date').val(dateOnly);
                } else {
                    // ถ้าไม่มี ให้ใช้วันที่ปัจจุบัน
                    const today = new Date();
                    const year = today.getFullYear();
                    const month = String(today.getMonth() + 1).padStart(2, '0');
                    const day = String(today.getDate()).padStart(2, '0');
                    const todayString = `${year}-${month}-${day}`;
                    $('#start_date').val(todayString);
                }
            }
            
            // ตั้งค่า default ให้แสดงค่าการค้นหาที่เลือกไว้เดิม
            function setDefaultSearch() {
                const savedSearch = localStorage.getItem('selectedSearch');
                
                if (savedSearch) {
                    $("input[type='search']").val(savedSearch);
                } else {
                    $("input[type='search']").val('ANW-');
                }
            }
            
            // เรียกใช้ฟังก์ชันตั้งค่า default
            setDefaultDate();
            setDefaultSearch();
            
            // รอให้ค่า search ถูกตั้งค่าเสร็จก่อนสร้าง DataTable
            var searchValue = localStorage.getItem('selectedSearch') || 'ANW-';
            var initialSearch = searchValue;
            var initialLoad = true; // ตัวแปรสำหรับเช็คว่าเป็นการโหลดครั้งแรกหรือไม่
            
           // เพิ่มฟังก์ชันสำหรับตรวจสอบและอัพเดทสถานะปุ่ม Invoice และ Data Export
           function updateInvoiceButtonState() {
                var $invoiceBtn = $('#invoiceBtn');
                var $dataExportBtn = $('#data-export');
                var $btn2 = $('#updateSelected2');
                var $btn3 = $('#updateSelected3');
                var selectedRows = $('tbody').find(':checkbox:checked');
                
                if (selectedRows.length > 0) {
                    $invoiceBtn.removeClass('disabled');
                    $dataExportBtn.removeClass('disabled');
                    $btn2.removeClass('disabled');
                    $btn3.removeClass('disabled');
                } else {
                    $invoiceBtn.addClass('disabled');
                    $dataExportBtn.addClass('disabled');
                    $btn2.addClass('disabled');
                    $btn3.addClass('disabled');
                }
            }

            // เพิ่ม event listeners
            $('#start_date, #end_date').on('change', updateInvoiceButtonState);
            $("input[type='search']").on('keyup input', function() {
                // บันทึกค่าการค้นหาแบบ real-time
                var searchValue = $(this).val();
                
                // ถ้าค่าว่าง ให้ลบค่าออกจาก localStorage
                if (!searchValue || searchValue.trim() === '') {
                    localStorage.removeItem('selectedSearch');
                }
                // ไม่บันทึก "แสดง" หรือ "ซ่อน" ลง localStorage เพื่อไม่ให้วนซ้ำ
                else if (searchValue.toLowerCase() !== 'แสดง' && searchValue.toLowerCase() !== 'ซ่อน') {
                    localStorage.setItem('selectedSearch', searchValue);
                }
            });
            $("select.status").on('change', updateInvoiceButtonState);
            // เรียกใช้ฟังก์ชันครั้งแรกเมื่อโหลดหน้า
            updateInvoiceButtonState();

            var dataTable=$('#dt-mant-table-1').DataTable({
                "processing": true,
                "serverSide": true,
                "language": {
                    "processing": "กำลังโหลด..."
                },
                "ajax": {
                    "url": "{{ route('fetch.customerorder') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d){

                        // ใช้ค่า search จาก input field โดยตรง แต่ถ้าเป็นการโหลดครั้งแรกให้ใช้ initialSearch
                        var currentSearch = $("input[type='search']").val() || (initialLoad ? initialSearch : '');
                        d.search = currentSearch;
                        d.status = $("select.status").val();
                        d.shipping_status = $("select.shipping_status").val();
                        d.supplier_status_id = $("select.supplier_status_id").val();
                        d.boss_id = $("select.boss_id").val();
                        
                        // ส่ง date ไปยัง server เพื่อค้นหาจากวันที่
                        d.start_date = $('#start_date').val() || '';
                        d.end_date = $('#end_date').val() || '';
                        d._token = "{{ csrf_token() }}";

                        // จัดการการซ่อน/แสดงคอลัมน์
                        if (d.search && d.search.toLowerCase() === 'แสดง') {
                            d.hide = 'true';
                        } else if (d.search && d.search.toLowerCase() === 'ซ่อน') {
                            d.hide = 'false';
                        }

                    }
                },
                "lengthMenu": [10,20,30,50,100,200, 300, 400, 500,600,1000,5000,10000], // ตัวเลือกที่สามารถเลือกได้
                "pageLength": 100,
                "initComplete": function () {

                    // ใช้ session search ถ้ามี (จาก redirect หลัง create/edit) มิฉะนั้นใช้ localStorage
                    var sessionSearch = '{{ Session::get("search", "") }}';
                    var searchToUse = sessionSearch || initialSearch;
                    if (searchToUse) {
                        $("input[type='search']").val(searchToUse);
                        dataTable.search(searchToUse).draw();
                        localStorage.setItem('selectedSearch', searchToUse);
                    }
                    
                    $('a.btn-edit').html('<span class="spinner-border"></span>').addClass('disabled');

                    // status สถานะการชำระเงิน
                    this.api().columns([10]).every(function () {
                        var column = this;
                        var select = $('<select class="status"><option value="">C.Status(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // alert('test');
                                dataTable.ajax.reload();

                            });

                        select.append('<option value="1">ยังไม่ชำระเงิน</option>')
                        select.append('<option value="5">รอโอน</option>')
                        select.append('<option value="2">ชำระเงินแล้ว</option>')
                        select.append('<option value="3">รอร้านแจ้งค่าส่งในญี่ปุ่น</option>')
                        select.append('<option value="4">ร้านค้ายกเลิก</option>')


                        // });
                    });


                    this.api().columns([11]).every(function () {
                        var column = this;
                        var select = $('<select class="supplier_status_id"><option value="">Buyer Status(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                dataTable.ajax.reload();
                            });

                        // Hardcode options เหมือน status dropdown เพื่อให้โหลดเร็ว
                        select.append('<option value="1">รอตรวจสอบ</option>')
                        select.append('<option value="2">รอโอน</option>')
                        select.append('<option value="3">จ่ายแล้ว</option>')
                        select.append('<option value="4">ยกเลิก/คืนเงิน</option>')
                    });

                    this.api().columns([14]).every(function () {
                        var column = this;
                        var select = $('<select class="shipping_status"><option value="">สถานะขนส่ง(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                // alert('test');
                                dataTable.ajax.reload();

                            });

                        select.append('<option value="1">รอดำเนินการ</option>')
                        select.append('<option value="2">อยู่ระหว่างขนส่ง</option>')
                        select.append('<option value="3">สินค้าถึงไทยแล้ว</option>')
                        select.append('<option value="4">สำเร็จ</option>')
                        // });
                    });

                    this.api().columns([19]).every(function () {
                        var column = this;
                        var select = $('<select class="boss_id"><option value="">Boss(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                dataTable.ajax.reload();
                            });

                        // สร้าง options A-Z พร้อม id 1-26 สลับจุดวงกลมน้ำเงินกับเหลือง
                        for (var i = 1; i <= 26; i++) {
                            var letter = String.fromCharCode(64 + i); // A=65, B=66, ..., Z=90
                            var dot = (i % 2 === 1) ? '🔵' : '🟡'; // สลับจุดวงกลม น้ำเงิน/เหลือง
                            select.append('<option value="' + i + '">' + dot + ' ' + letter + '</option>');
                        }
                        
                        // สร้าง options AA-ZZ พร้อม id 27-52 สลับจุดวงกลมน้ำเงินกับเหลือง
                        for (var i = 1; i <= 26; i++) {
                            var letter = String.fromCharCode(64 + i); // A=65, B=66, ..., Z=90
                            var doubleLetter = letter + letter; // AA, BB, CC, ..., ZZ
                            var dotIndex = 26 + i; // เริ่มจาก 27
                            var dot = (dotIndex % 2 === 1) ? '🔵' : '🟡'; // สลับจุดวงกลม น้ำเงิน/เหลือง
                            select.append('<option value="' + (26 + i) + '">' + dot + ' ' + doubleLetter + '</option>');
                        }
                    });


                },
                "columnDefs": [
                    { "targets": 0, "data": null,"orderable": false, "render": function (data, type, full, meta) {

                            return `<input type="checkbox" value="${full.id}">`;

                        }
                    },
                    { "targets": 7, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // เงินเยน
                    { "targets": 8, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // เรท
                    { "targets": 9, "visible": {{Session::get('hide') ? 'true' : 'false'}} }, // เงินบาท
                    { "targets": 1, "data": null,title:"No","orderable": false, "render": function (data, type, full, meta) {
                            return meta.row + 1;
                        } },
                    { "targets": 2, "data": "order_date",
                        "render": function (data, type, full, meta) {
                            // Format วันที่พร้อมเวลา
                            var formattedDate = data || '-';
                            if (data && data !== '-') {
                                try {
                                    // ใช้ moment.js เพื่อ format วันที่
                                    if (typeof moment !== 'undefined') {
                                        var momentDate = moment(data);
                                        if (momentDate.isValid()) {
                                            formattedDate = momentDate.format('DD/MM/YYYY HH:mm');
                                        }
                                    } else {
                                        // Fallback ถ้าไม่มี moment.js
                                        var dateObj = new Date(data);
                                        if (!isNaN(dateObj.getTime())) {
                                            var day = String(dateObj.getDate()).padStart(2, '0');
                                            var month = String(dateObj.getMonth() + 1).padStart(2, '0');
                                            var year = dateObj.getFullYear();
                                            var hours = String(dateObj.getHours()).padStart(2, '0');
                                            var minutes = String(dateObj.getMinutes()).padStart(2, '0');
                                            formattedDate = day + '/' + month + '/' + year + ' ' + hours + ':' + minutes;
                                        }
                                    }
                                } catch(e) {
                                    // ถ้า format ไม่ถูกต้อง ให้แสดงตามเดิม
                                }
                            }

                            return `
                            <div>${formattedDate}</div>
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
                    { "targets": 3, "data": "customerno"},
                    { "targets": 4, "data": "image_link", "render": function (data, type, full, meta) {
                            if (!data || data.trim() === '-') {
                                return '-';
                            } else {
                                return '<img src="uploads/' + data + '" class="img-thumbnail" width="50" height="50" onclick="showImage(\'uploads/' + data + '\')" alt="" style="cursor: pointer;" onerror="this.onerror=null;this.src=\'/img/error-icon.png\';this.alt=\'-\';">';
                            }
                        } }, // คอลัมน์ที่ 2
                    // เพิ่มคอลัมน์ที่ต้องการให้แสดงในตารางตามลำดับที่เป็นไปตามลิสต์ของคุณ

                    // { "targets": 5, "data": "link" },
                    {
                        "targets": 5,
                        "data": "link",
                        "render": function (data, type, full, meta) {


                            return `
                            <div class="link-cell" title="${data}">
<button class="btn btn-sm btn-outline-secondary copy-link d-none" data-clipboard-text="${data}">คัดลอก</button>
                                <a href="${data}" target="_blank">${getNameFromDomain(data)}</a>

                            </div>`;
                        }
                    },
                    { "targets": 6, "data": "quantity" },
                    { "targets": 7, "data": "product_cost_yen" },
                    { "targets": 8, "data": "rateprice" },
                    // { "targets": 8, "data": "unit_price", "render": function (data, type, full, meta) {
                    //         if (!data || (data > 180.00||data===0||data===0.00) ) {
                    //             return 'ราคาเหมา';
                    //         } else {
                    //             return data;
                    //         }
                    //     } },
                    { "targets": 9, "data": "product_cost_baht" },
                    {
                        "targets": 10,
                        "data": "status",
                        "orderable": false,
                        "render": function(data, type, row) {
                            let statusClass = '';
                            console.log(data);
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
                                case 'ร้านค้ายกเลิก':
                                    statusClass = 'black';
                                    break;
                                case 'รอร้านแจ้งค่าส่งในญี่ปุ่น':
                                    statusClass = 'lightblue';
                                    break;
                            }
                            
                            return `<div class="status-container">
                                        <span title="${data}" class="dot bg-${statusClass}"></span>
                                    </div>`;
                        }
                    },

                    {
                        "targets": 11,
                        "data": "supplier_status",
                        "orderable": false,
                        "render": function(data, type, row) {
                            if (!data || data === '-') {
                                return '-';
                            }
                            
                            let statusClass = '';
                            switch(data) {
                                case 'รอตรวจสอบ':
                                    statusClass = 'danger';
                                    break;
                                case 'รอโอน':
                                    statusClass = 'warning';
                                    break;
                                case 'จ่ายแล้ว':
                                    statusClass = 'success';
                                    break;
                                case 'ยกเลิก/คืนเงิน':
                                    statusClass = 'black';
                                    break;
                                default:
                                    return data; // ถ้าไม่ตรงกับเงื่อนไข ให้แสดงข้อความปกติ
                            }
                            
                            return `<div class="status-container">
                                        <span title="${data}" class="dot bg-${statusClass}"></span>
                                    </div>`;
                        }
                    },
                    { "targets": 12, "data": "tracking_number" },
                    // { "targets": 13, "data": "warehouse" },
                    { "targets": 13, "data": "cutoff_date", "render": function (data, type, full, meta) {
                            // Format วันที่ (ไม่แสดงเวลา)
                            var formattedDate = data || '-';
                            if (data && data !== '-') {
                                try {
                                    // ใช้ moment.js เพื่อ format วันที่
                                    if (typeof moment !== 'undefined') {
                                        var momentDate = moment(data);
                                        if (momentDate.isValid()) {
                                            formattedDate = momentDate.format('DD/MM/YYYY');
                                        }
                                    } else {
                                        // Fallback ถ้าไม่มี moment.js
                                        var dateObj = new Date(data);
                                        if (!isNaN(dateObj.getTime())) {
                                            var day = String(dateObj.getDate()).padStart(2, '0');
                                            var month = String(dateObj.getMonth() + 1).padStart(2, '0');
                                            var year = dateObj.getFullYear();
                                            formattedDate = day + '/' + month + '/' + year;
                                        }
                                    }
                                } catch(e) {
                                    // ถ้า format ไม่ถูกต้อง ให้แสดงตามเดิม
                                }
                            }
                            return formattedDate;
                        }},

                    { "targets": 14, "data": "shipping_status", orderable:false, "render": function(data, type, row) {
                            if (!data || data === '-') return '-';
                            var colors = {
                                'รอดำเนินการ': {bg:'#f1f5f9',color:'#64748b'},
                                'อยู่ระหว่างขนส่ง': {bg:'#fef2f2',color:'#dc2626'},
                                'สินค้าถึงไทยแล้ว': {bg:'#dcfce7',color:'#16a34a'},
                                'สำเร็จ': {bg:'#fdf2f8',color:'#ec4899'}
                            };
                            var c = colors[data] || {bg:'#f1f5f9',color:'#64748b'};
                            return '<span style="display:inline-block;padding:4px 12px;border-radius:20px;background:'+c.bg+';color:'+c.color+';font-size:11px;font-weight:700;white-space:nowrap;">'+data+'</span>';
                        }},
                    { "targets": 15, "data": "note" },
                    { "targets": 16, "data": "note_admin" },
                    { "targets": 17, "data": "itemno" },
                    { "targets": 18, "data": "itemno2" },
                    { "targets": 19, "data": "boss", "orderable": false, "render": function (data, type, full, meta) {
                            return data || '-';
                        }},
                    { "targets": 20, "data": "category",visible:false}

                ],
            });

            @if ($search = Session::get('search'))
            $("input[type='search']").val('{{ $search }}');
            $("#sessionSearch").val('{{ $search }}');
            console.log('get search');
            dataTable.search('{{ $search }}').draw();
            // บันทึกค่าการค้นหาจาก session ลงใน localStorage
            localStorage.setItem('selectedSearch', '{{ $search }}');
            @endif



            // Event handler สำหรับ date picker (ค้นหาจากวันที่อย่างเดียว ไม่มีเวลา)
            $('#start_date,#end_date').on('change', function () {
                var $input = $(this);
                var currentValue = $input.val();
                var inputId = $input.attr('id');
                
                // ถ้าค่าว่าง ให้ลบค่าใน localStorage
                if (!currentValue || currentValue === '') {
                    if (inputId === 'start_date') {
                        localStorage.removeItem('orderStartDate');
                    }
                }
                // บันทึกวันที่ที่เลือกไว้ใน localStorage (เฉพาะ start_date)
                else if (inputId === 'start_date') {
                    localStorage.setItem('orderStartDate', currentValue);
                }
                
                // Reload DataTable
                console.log('date change:', inputId, currentValue);
                dataTable.ajax.reload();
            });

// สร้างตัวแปรเพื่อเก็บค่าการค้นหาก่อนหน้า
            var previousSearchValue = ''; // เก็บค่าการค้นหาก่อนหน้า
            var previousStartDate = '';    // เก็บค่า start_date ก่อนหน้า
            var previousEndDate = '';      // เก็บค่า end_date ก่อนหน้า

            dataTable.on('xhr.dt', function(e, settings, json, xhr) {
                // ดึงข้อมูลที่ส่งกลับมาจากการเรียกใช้ AJAX
                {{--settings.oPreviousSearch.sSearch !== '{{ $search }}'||--}}
                if (settings.jqXHR.readyState < 4) {
                    // Data is still being loaded
                    $('a.btn-edit').html('<span class="spinner-border"></span>').addClass('disabled');
                }
                
                // คำนวณยอดรวมทุกครั้งที่ข้อมูลเปลี่ยนแปลง
                if (json.payprice !== undefined) {
                    $('#payprice').text(json.payprice);
                }
                if (json.totalprice !== undefined) {
                    $('#totalprice').text(json.totalprice);
                }
                
                if (initialLoad || settings.oPreviousSearch.sSearch !== previousSearchValue ||
                    $('#start_date').val() !== previousStartDate ||
                    $('#end_date').val() !== previousEndDate) {
                    initialLoad = false;
                    console.log('sss:'+settings.oPreviousSearch.sSearch);
                    previousSearchValue = settings.oPreviousSearch.sSearch; // อัปเดตค่าการค้นหาก่อนหน้า
                    previousStartDate = $('#start_date').val(); // อัปเดตค่า start_date ก่อนหน้า
                    previousEndDate = $('#end_date').val();

                    // $('#sessionSearch').val('');
                    initialLoad = false; // ตั้งค่า initialLoad เป็น false หลังจากการโหลดครั้งแรก
                    console.log('initialSearch:', initialLoad);

                    console.log('Response Data:', json);
                }
                // dataTable.columns.adjust();

            });


            dataTable.on('draw.dt', function () {
                $('a.btn-edit').html('<i class="fa fa-fw fa-edit"></i> Edit').removeClass('disabled');
                // อัพเดทสถานะปุ่มเมื่อ DataTable redraw
                updateInvoiceButtonState();


                $('.copy-link').on('click', function (e) {
                    e.preventDefault();

                    var linkToCopy = $(this).data('clipboard-text'); // ดึงลิงก์จาก data attribute

                    // สร้าง element textarea ชั่วคราวเพื่อเก็บลิงก์
                    var $temp = $("<textarea>");
                    $("body").append($temp);
                    $temp.val(linkToCopy).select();

                    // คัดลอกข้อความ
                    document.execCommand("copy");

                    // ลบ element textarea ชั่วคราว
                    $temp.remove();

                    alert('คัดลอกลิงก์เรียบร้อยแล้ว!');
                });
            });
            // dataTable.columns().every(function (colIdx) {
            //     if (colIdx !== 5) { // ไม่ปรับคอลัมน์ "URL"
            //         // ใช้ DataTables API เพื่อตั้งค่าความกว้าง
            //         var column = dataTable.column(colIdx); // Get the column object
            //         column.width(column.header().offsetWidth + 'px'); // Set width with unit
            //     }
            // });


            $('#checkAll').on('change', function() {
                $(':checkbox', dataTable.rows().nodes()).prop('checked', $(this).prop('checked'));
            });
            $('#updateSelected,#updateSelected2,#updateSelected3').on('click', function(e) {

                var selectedRows = $('tbody').find(':checkbox:checked');
                // console.log(selectedRows.length);
                if (selectedRows.length > 0) {
                    var selectedIds = [];
                    selectedRows.each(function() {
                        selectedIds.push($(this).val());
                    });
                    $('#trackIdsInput').val(selectedIds.join(','));
                    $('#trackIdsInput2').val(selectedIds.join(','));
                    $('#trackIdsInput3').val(selectedIds.join(','));
                    // ทำ AJAX request เพื่ออัปเดตสถานะ
                    {{--$.ajax({--}}
                    {{--    url: '/update-status',--}}
                    {{--    type: 'POST',--}}
                    {{--    data: {--}}
                    {{--        track_ids: selectedIds,--}}
                    {{--        destination_date: $('#date').val(),--}}
                    {{--        _token: '{{ csrf_token() }}',--}}
                    {{--    },--}}
                    {{--    success: function(response) {--}}
                    {{--        console.log(response.message);--}}
                    {{--        // ทำสิ่งที่คุณต้องการหลังจากอัปเดตสถานะเรียบร้อย--}}
                    {{--    },--}}
                    {{--    error: function(error) {--}}
                    {{--        console.error('Error:', error);--}}
                    {{--    }--}}
                    {{--});--}}
                } else {
                    e.preventDefault();
                    alert("กรุณาเลือกรายการที่ต้องอัพเดท");
                }
            });

            // อัพเดทสถานะปุ่มเมื่อ DataTable ทำการค้นหา
            dataTable.on('search.dt', function() {
                updateInvoiceButtonState();
                
                // บันทึกค่าการค้นหาไว้ใน localStorage
                var searchValue = $("input[type='search']").val();
                
                // ตรวจสอบการพิมพ์ "แสดง" หรือ "ซ่อน"
                if (searchValue.toLowerCase() === 'แสดง' || searchValue.toLowerCase() === 'ซ่อน') {
                    // ล้างค่า localStorage ก่อน reload เพื่อไม่ให้วนซ้ำ
                    localStorage.removeItem('selectedSearch');
                    // รีเฟรชหน้าเพื่อให้ session ถูกอัพเดท
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                } 
                // ถ้าค่าว่าง ให้ล้าง localStorage
                else if (!searchValue || searchValue.trim() === '') {
                    localStorage.removeItem('selectedSearch');
                } 
                // ถ้ามีค่า ให้บันทึกลง localStorage
                else if (searchValue) {
                    localStorage.setItem('selectedSearch', searchValue);
                }
            });

            // เพิ่ม event handler สำหรับปุ่ม Invoice
            $('#invoiceBtn').on('click', function() {
                var selectedRows = $('tbody').find(':checkbox:checked');
                if (selectedRows.length > 0) {
                    var selectedIds = [];
                    selectedRows.each(function() {
                        selectedIds.push($(this).val());
                    });
                    
                    // ส่งวันที่ไปยัง server
                    var startDate = $('#start_date').val() || moment().format('YYYY-MM-DD');
                    var endDate = $('#end_date').val() || moment().format('YYYY-MM-DD');
                    var status = $('select.status').val() || 1;
                    
                    var url = "{{ route('invoice.order', [
                        'order_date' => ':start_date', 
                        'end_order_date' => ':end_date', 
                        'status' => ':status',
                        'customerorderids' => ':customerorderids',
                        'customerno' => ':customerno'
                    ]) }}";
                    
                    url = url.replace(':start_date', startDate)
                             .replace(':end_date', endDate)
                             .replace(':status', status)
                             .replace(':customerorderids', selectedIds.join(','))
                             .replace(':customerno', $('input[type="search"]').val() );
                            
                    window.open(url, '_blank');
                }
            });

            // อัพเดทสถานะปุ่มเมื่อมีการเลือก/ยกเลิกเลือก checkbox
            $(document).on('change', ':checkbox', function() {
                updateInvoiceButtonState();
            });

            // เพิ่ม event handler สำหรับปุ่ม Data Export EXCEL
            $('#data-export').on('click', function(e) {
                e.preventDefault();
                
                var selectedRows = $('tbody').find(':checkbox:checked');
                
                if (selectedRows.length === 0) {
                    alert('กรุณาเลือกรายการที่ต้องการ Export ก่อน');
                    return false;
                }
                
                var selectedIds = [];
                selectedRows.each(function() {
                    selectedIds.push($(this).val());
                });
                
                // ส่งวันที่ไปยัง server
                var startDate = $('#start_date').val() || '';
                var endDate = $('#end_date').val() || '';
                var customerno = $("input[type='search']").val() || '';
                var status = $("select.status").val() || '';
                var shippingStatus = $("select.shipping_status").val() || '';
                var includeImage = $('#include-image').is(':checked') ? '1' : '0';
                
                // สร้าง URL สำหรับ Export
                var url = "{{ route('customerorderexport2') }}";
                var params = [];
                
                // ส่ง customerorder_ids ที่ติ๊ก
                if (selectedIds.length > 0) {
                    params.push('customerorder_ids=' + encodeURIComponent(selectedIds.join(',')));
                }
                
                if (startDate) params.push('start_date=' + encodeURIComponent(startDate));
                if (endDate) params.push('end_date=' + encodeURIComponent(endDate));
                if (customerno) params.push('customerno=' + encodeURIComponent(customerno));
                if (status) params.push('status=' + encodeURIComponent(status));
                if (shippingStatus) params.push('shipping_status=' + encodeURIComponent(shippingStatus));
                params.push('include_image=' + encodeURIComponent(includeImage));
                
                if (params.length > 0) {
                    url += '?' + params.join('&');
                }
                
                // เปิดหน้าต่าง export
                window.open(url, '_blank');
                
                // Reload DataTable ทันทีหลังจากเปิดหน้าต่าง export
                // และ reload อีกครั้งหลังจาก 1.5 วินาทีเพื่อให้แน่ใจว่าเห็นการอัพเดท
                dataTable.ajax.reload(null, false);
                setTimeout(function() {
                    dataTable.ajax.reload(null, false);
                }, 1500);
            });
        });</script>
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">


                    <div class="card-header">
                        <!-- Row 1: Title + Status Actions -->
                        <div class="toolbar-row">
                            <span class="page-title"><i class="fa fa-shopping-cart"></i> My Orders</span>
                            <div class="toolbar-spacer"></div>
                            <div class="toolbar-group">
                                <span class="toolbar-group-label">อัพเดทสถานะ:</span>
                                <form method="POST" action="{{ route('update-status-pay') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids2" id="trackIdsInput2" value="">
                                    <input type="submit" class="btn-modern btn-blue disabled" id="updateSelected2" value="💰 C.ชำระเงินแล้ว">
                                </form>
                                <form method="POST" action="{{ route('update-status-supplier-pay') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids3" id="trackIdsInput3" value="">
                                    <input type="submit" class="btn-modern btn-red disabled" id="updateSelected3" value="💳 B.ชำระเงินแล้ว">
                                </form>
                                <form method="POST" action="{{ route('update-status-shipping') }}" style="display:none;">
                                    @csrf
                                    <input type="hidden" name="track_ids" id="trackIdsInput" value="">
                                    <input type="submit" id="updateSelected" value="">
                                </form>
                            </div>
                        </div>
                        <!-- Row 2: Navigation + Export -->
                        <div class="toolbar-row">
                            <div class="toolbar-group">
                                <a href="{{ route('welcome') }}" class="btn-modern btn-dark"><i class="fa fa-dashboard"></i> Dashboard</a>
                                <a href="{{ route('customerorders.create') }}" class="btn-modern btn-blue"><i class="fa fa-plus"></i> Create New</a>
                                <button id="invoiceBtn" class="btn-modern btn-red disabled"><i class="fa fa-file-text-o"></i> Invoice</button>
                            </div>
                            <div class="toolbar-spacer"></div>
                            <div class="toolbar-group">
                                <span class="toolbar-group-label">ส่งออก:</span>
                                <label class="btn-modern btn-outline" style="cursor:pointer;margin:0;">
                                    <input type="checkbox" id="include-image" checked style="accent-color:#dc3545;"> รูปภาพ
                                </label>
                                <button id="data-export" class="btn-modern btn-green disabled"><i class="fa fa-file-excel-o"></i> Data Excel</button>
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
                                <label for="start_date">วันที่เริ่มต้น</label>
                                @if ($date = Session::get('startdate'))
                                    <input type="date" id="start_date" value="{{$date}}" style="cursor:pointer;">
                                    @php  session()->forget('startdate'); @endphp
                                @else
                                    <input type="date" id="start_date" value="{{ date('Y-m-d') }}" style="cursor:pointer;">
                                @endif
                                <label for="end_date">ถึง</label>
                                <input type="date" id="end_date" placeholder="วันที่สิ้นสุด" style="cursor:pointer;">
                            </div>

                            <div class="stats-row {{Session::get('hide')?'':'d-none'}}">
                                <div class="stat-card" id="totalprice_section">
                                    <div class="stat-label">🛒 รวมค่าสินค้า</div>
                                    <div class="stat-value"><span id="totalprice">-</span></div>
                                    <div class="stat-unit">บาท</div>
                                </div>
                                <div class="stat-card stat-highlight" id="payprice_section">
                                    <div class="stat-label">💰 ยอดรอชำระ</div>
                                    <div class="stat-value"><span id="payprice">-</span></div>
                                    <div class="stat-unit">บาท</div>
                                </div>
                            </div>



                            <table class="table table-striped table-hover" id="dt-mant-table-1">
                                <thead class="thead">
                                <tr>
                                    <th><input type="checkbox" id="checkAll"></th>
                                    <th>No</th>

                                    <th>วันที่</th>
                                    <th>รหัสลูกค้า</th>
                                    <th>รูปภาพ</th>
                                    <th>URL</th>
                                    <th>จำนวน</th>
                                    <th>เงินเยน</th>
                                    <th>เรท</th>
                                    <th>เงินบาท</th>
                                    <th>C.Status</th>
                                    <th>Buyer Status</th>
                                    <th>เลขพัสดุ</th>
                                    <th>รอบปิดตู้</th>
                                    <th>สถานะขนส่ง</th>
                                    <th>หมายเหตุ</th>
                                    <th>Note Admin</th>
                                    <th>Items</th>
                                    <th>Items2</th>
                                    <th>Boss</th>
                                    <th class="d-none">ประเภท</th>

                                </tr>
                                </thead>
                                <tbody>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{--                {!! $customerorders->links() !!}--}}
            </div>
        </div>
    </div>
@endsection

