@extends('layouts.app')

@section('template_title')
    Purchase Requests
@endsection

@section('extra-css')
    <style>
        input[type='submit'].disabled { opacity: 0.5; pointer-events: none; }

        html, body {
            overflow-x: hidden !important;
            width: 100% !important;
            max-width: 100vw !important;
        }

        .wrapper {
            display: flex !important;
            flex-direction: row !important;
            min-height: 100vh;
            position: relative !important;
            width: 100vw !important;
            overflow-x: hidden !important;
        }

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

        .navbar-modern, 
        .navbar, 
        .navbar-expand-lg,
        .panel-header,
        .main-panel > .panel-header {
            display: none !important;
        }

        .main-panel > .content {
            display: block !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }

        .table td, .table th {
            white-space: nowrap;
        }
        th, td {
            text-align: center;
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

        .dot {
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
        }

        .bg-warning { background-color: #ffc107; }
        .bg-info { background-color: #17a2b8; }
        .bg-primary { background-color: #007bff; }
        .bg-success { background-color: #28a745; }
        .bg-secondary { background-color: #6c757d; }
        .bg-dark { background-color: #343a40; }
        .bg-danger { background-color: #dc3545; }

        .badge-status {
            padding: 4px 10px;
            border-radius: 12px;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
        }

        .product-thumb {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-buy-now {
            display: inline-block;
            margin-top: 4px;
            padding: 3px 10px;
            border-radius: 5px;
            color: #fff !important;
            font-size: 11px;
            font-weight: 700;
            text-decoration: none !important;
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        .btn-buy-now:hover { opacity: 0.85; }
        .btn-buy-now i { margin-right: 3px; }

        .site-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
        }
        .site-mercari { background: #ff4655; color: #fff; }
        .site-yahoo { background: #7b0099; color: #fff; }
        .site-rakuten { background: #bf0000; color: #fff; }
        .site-amazon { background: #ff9900; color: #000; }
        .site-paypay { background: #ff0033; color: #fff; }
        .site-other { background: #6c757d; color: #fff; }

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

    <script>
        function showImage(imageUrl) {
            var overlay = document.createElement('div');
            overlay.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);z-index:9999;display:flex;align-items:center;justify-content:center;';
            var img = document.createElement('img');
            img.src = imageUrl;
            img.style.cssText = 'max-width:80%;max-height:80%;border-radius:8px;';
            overlay.appendChild(img);
            document.body.appendChild(overlay);
            overlay.onclick = function() { document.body.removeChild(overlay); }
        }

        function getSiteBadge(site) {
            var cls = 'site-other';
            if (site === 'Mercari') cls = 'site-mercari';
            else if (site === 'Yahoo Auctions') cls = 'site-yahoo';
            else if (site === 'Rakuten') cls = 'site-rakuten';
            else if (site === 'Amazon JP') cls = 'site-amazon';
            else if (site === 'PayPay') cls = 'site-paypay';
            return '<span class="site-badge ' + cls + '">' + (site || '-') + '</span>';
        }

        $(function () {
            function updateBulkButtons() {
                var selected = $('tbody').find(':checkbox:checked').length;
                if (selected > 0) {
                    $('#bulkStatusBtn').removeClass('disabled').prop('disabled', false);
                } else {
                    $('#bulkStatusBtn').addClass('disabled').prop('disabled', true);
                }
            }

            var dataTable = $('#pr-table').DataTable({
                "processing": true,
                "serverSide": true,
                "language": {
                    "processing": "กำลังโหลด..."
                },
                "ajax": {
                    "url": "{{ route('fetch.purchase-requests') }}",
                    "dataType": "json",
                    "type": "POST",
                    "data": function (d) {
                        d.search = $("input[type='search']").val() || '';
                        d.status = $("select.filter-status").val();
                        d.start_date = $('#start_date').val() || '';
                        d.end_date = $('#end_date').val() || '';
                        d._token = "{{ csrf_token() }}";
                    }
                },
                "lengthMenu": [10, 20, 50, 100, 200],
                "pageLength": 50,
                "order": [[1, 'desc']],
                "initComplete": function () {
                    // Status filter dropdown
                    this.api().columns([8]).every(function () {
                        var column = this;
                        var select = $('<select class="filter-status"><option value="">สถานะ(ทั้งหมด)</option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                dataTable.ajax.reload();
                            });

                        select.append('<option value="0">รอดำเนินการ</option>');
                        select.append('<option value="1">อนุมัติแล้ว</option>');
                        select.append('<option value="2">กำลังสั่งซื้อ</option>');
                        select.append('<option value="3">สั่งซื้อแล้ว</option>');
                        select.append('<option value="4">ถึงโกดังแล้ว</option>');
                        select.append('<option value="5">ส่งแล้ว</option>');
                        select.append('<option value="6">ยกเลิก</option>');
                    });
                },
                "columnDefs": [
                    {
                        "targets": 0, "data": null, "orderable": false,
                        "render": function (data, type, full) {
                            return '<input type="checkbox" value="' + full.id + '">';
                        }
                    },
                    {
                        "targets": 1, "data": "created_at",
                        "render": function (data, type, full) {
                            var date = data ? moment(data).format('DD/MM/YYYY HH:mm') : '-';
                            return '<div>' + date + '</div>' +
                                '<a class="btn btn-sm btn-success" href="' + full.action_edit + '"><i class="fa fa-fw fa-edit"></i> Edit</a> ' +
                                '<form action="' + full.action_del + '" method="POST" style="display:inline;">' +
                                '@csrf @method("DELETE")' +
                                '<button type="submit" class="btn btn-danger btn-sm" onclick="return confirm(\'ลบรายการนี้?\')"><i class="fa fa-fw fa-trash"></i></button>' +
                                '</form>';
                        }
                    },
                    { "targets": 2, "data": "request_no" },
                    { "targets": 3, "data": "customerno" },
                    {
                        "targets": 4, "data": "site", "orderable": false,
                        "render": function (data) {
                            return getSiteBadge(data);
                        }
                    },
                    {
                        "targets": 5, "data": "product_title",
                        "render": function (data, type, full) {
                            var title = data ? (data.length > 30 ? data.substring(0, 30) + '...' : data) : '-';
                            var img = '';
                            if (full.product_image) {
                                img = '<img src="' + full.product_image + '" class="product-thumb" onclick="showImage(\'' + full.product_image + '\')" onerror="this.style.display=\'none\'" /> ';
                            }
                            var link = '<a href="' + full.product_url + '" target="_blank" title="' + (data || '') + '">' + title + '</a>';
                            var siteColors = {'Mercari':'#ff4655','Yahoo Auctions':'#7b0099','Rakuten':'#bf0000','Amazon JP':'#ff9900','PayPay':'#ff0033'};
                            var btnColor = siteColors[full.site] || '#1D8AC9';
                            var buyBtn = '<a href="' + full.product_url + '" target="_blank" class="btn-buy-now" style="background:' + btnColor + ';" title="เปิดหน้าสินค้าเพื่อสั่งซื้อ"><i class="fa fa-external-link"></i> สั่งซื้อ</a>';
                            return img + link + buyBtn;
                        }
                    },
                    { "targets": 6, "data": "quantity" },
                    {
                        "targets": 7, "data": "estimated_price_yen",
                        "render": function (data, type, full) {
                            var est = data ? Number(data).toLocaleString() : '-';
                            var act = full.actual_price_yen ? '<br><small class="text-success">฿' + Number(full.actual_price_yen).toLocaleString() + '</small>' : '';
                            return '¥' + est + act;
                        }
                    },
                    {
                        "targets": 8, "data": "status", "orderable": false,
                        "render": function (data, type, full) {
                            var colors = {0:'warning',1:'info',2:'primary',3:'success',4:'secondary',5:'dark',6:'danger'};
                            return '<div class="status-container"><span title="' + full.status_label + '" class="dot bg-' + (colors[data] || 'secondary') + '"></span></div>';
                        }
                    },
                    {
                        "targets": 9, "data": "boss_name", "orderable": false
                    },
                    {
                        "targets": 10, "data": "purchase_ref",
                        "render": function (data) {
                            return data || '-';
                        }
                    }
                ],
                "drawCallback": function () {
                    updateBulkButtons();
                }
            });

            // Checkbox events
            $(document).on('change', 'tbody input[type="checkbox"]', updateBulkButtons);

            // Select All
            $(document).on('change', '#selectAll', function () {
                $('tbody input[type="checkbox"]').prop('checked', this.checked);
                updateBulkButtons();
            });

            // Bulk status update
            $('#bulkStatusBtn').on('click', function () {
                var ids = [];
                $('tbody input[type="checkbox"]:checked').each(function () {
                    ids.push($(this).val());
                });
                if (ids.length === 0) {
                    alert('กรุณาเลือกรายการก่อน');
                    return;
                }
                var newStatus = $('#bulkStatus').val();
                if (newStatus === '') {
                    alert('กรุณาเลือกสถานะ');
                    return;
                }
                if (!confirm('เปลี่ยนสถานะ ' + ids.length + ' รายการ?')) return;

                $.ajax({
                    url: "{{ route('purchase-requests.bulk-status') }}",
                    type: 'POST',
                    data: { ids: ids, status: newStatus, _token: "{{ csrf_token() }}" },
                    success: function () {
                        dataTable.ajax.reload();
                    }
                });
            });

            // Date filter
            $('#start_date, #end_date').on('change', function () {
                dataTable.ajax.reload();
            });
        });
    </script>
@endsection

@section('content')
    @include('layouts.partials.side-bar')
    <div class="main-panel">
        <div class="panel-header panel-header-sm"></div>
        <div class="content">
            <div class="container-fluid py-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="mb-0"><i class="fa fa-shopping-bag"></i> Purchase Requests</h4>
                    <a href="{{ route('purchase-requests.create') }}" class="btn btn-primary">
                        <i class="fa fa-plus"></i> สร้างคำขอสั่งซื้อ
                    </a>
                </div>

                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                    </div>
                @endif

                <div class="card">
                    <div class="card-body">
                        {{-- Filters --}}
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>วันที่เริ่ม</label>
                                <input type="date" id="start_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label>วันที่สิ้นสุด</label>
                                <input type="date" id="end_date" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6 text-right" style="padding-top: 28px;">
                                <select id="bulkStatus" class="form-control form-control-sm d-inline-block" style="width:auto;">
                                    <option value="">-- เปลี่ยนสถานะ --</option>
                                    <option value="0">รอดำเนินการ</option>
                                    <option value="1">อนุมัติแล้ว</option>
                                    <option value="2">กำลังสั่งซื้อ</option>
                                    <option value="3">สั่งซื้อแล้ว</option>
                                    <option value="4">ถึงโกดังแล้ว</option>
                                    <option value="5">ส่งแล้ว</option>
                                    <option value="6">ยกเลิก</option>
                                </select>
                                <button id="bulkStatusBtn" class="btn btn-sm btn-warning disabled" disabled>
                                    <i class="fa fa-refresh"></i> อัปเดตสถานะ
                                </button>
                            </div>
                        </div>

                        {{-- DataTable --}}
                        <div class="table-responsive">
                            <table id="pr-table" class="table table-striped table-bordered" style="width:100%">
                                <thead>
                                    <tr>
                                        <th style="width:30px;"><input type="checkbox" id="selectAll"></th>
                                        <th>วันที่</th>
                                        <th>เลขคำขอ</th>
                                        <th>รหัสลูกค้า</th>
                                        <th>เว็บ</th>
                                        <th>สินค้า</th>
                                        <th>จำนวน</th>
                                        <th>ราคา(¥)</th>
                                        <th>สถานะ</th>
                                        <th>Boss</th>
                                        <th>Ref.</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
