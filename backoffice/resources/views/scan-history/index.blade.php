@extends('layouts.app')

@section('template_title')
    ประวัติสแกนพัสดุ
@endsection

@section('extra-css')
    <style>
        .scan-history-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 12px;
            padding: 20px 24px;
            margin-bottom: 20px;
            color: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }
        .scan-history-header h2 { font-size: 20px; font-weight: 700; margin: 0; }
        .scan-history-header .subtitle { font-size: 13px; opacity: 0.8; margin-top: 2px; }

        .stats-row {
            display: flex;
            gap: 12px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .stat-card {
            flex: 1;
            min-width: 120px;
            background: #fff;
            border-radius: 10px;
            padding: 16px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            text-align: center;
        }
        .stat-card .num { font-size: 28px; font-weight: 800; }
        .stat-card .label { font-size: 12px; color: #64748b; margin-top: 2px; }
        .stat-card.green .num { color: #059669; }
        .stat-card.blue .num { color: #2563eb; }
        .stat-card.amber .num { color: #d97706; }
        .stat-card.purple .num { color: #7c3aed; }

        .progress-bar-wrap {
            background: #e2e8f0;
            border-radius: 8px;
            height: 10px;
            margin-top: 8px;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            border-radius: 8px;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.5s ease;
        }

        .filter-row {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
            margin-bottom: 16px;
        }
        .filter-row select, .filter-row input[type="text"] {
            padding: 8px 14px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-family: inherit;
        }
        .filter-row select { min-width: 220px; }
        .filter-row input[type="text"] { width: 160px; }
        .filter-row button {
            padding: 8px 16px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            font-size: 13px;
            font-family: inherit;
        }
        .btn-filter { background: #3b82f6; color: #fff; }
        .btn-reset { background: #e2e8f0; color: #475569; }

        .scan-table { width: 100%; border-collapse: collapse; }
        .scan-table thead th {
            background: #f1f5f9;
            padding: 10px 14px;
            text-align: left;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            border-bottom: 2px solid #e2e8f0;
            white-space: nowrap;
        }
        .scan-table tbody td {
            padding: 10px 14px;
            font-size: 13px;
            border-bottom: 1px solid #f1f5f9;
            white-space: nowrap;
        }
        .scan-table tbody tr:hover { background: #f8fafc; }
        .badge-scanned {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            background: #dcfce7;
            color: #16a34a;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-completed {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            background: #fdf2f8;
            color: #ec4899;
            font-size: 11px;
            font-weight: 700;
        }
        .badge-scanner {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            background: #eff6ff;
            color: #2563eb;
            font-size: 11px;
            font-weight: 600;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid">
        <div class="scan-history-header">
            <div>
                <h2><i class="fa fa-history"></i> ประวัติสแกนพัสดุ</h2>
                <div class="subtitle">รายการพัสดุที่ถูกสแกนเข้าระบบ — เลือกรอบปิดตู้เพื่อดูรายละเอียด</div>
            </div>
            <a href="{{ route('home') }}" style="background:#fff;color:#1e40af;padding:8px 18px;border-radius:8px;font-weight:600;text-decoration:none;font-size:13px;"><i class="fa fa-arrow-left"></i> กลับหน้าหลัก</a>
        </div>

        <div class="stats-row">
            <div class="stat-card green">
                <div class="num" id="statScanned">-</div>
                <div class="label">สแกนแล้ว</div>
            </div>
            <div class="stat-card purple" id="statTotalCard" style="display:none;">
                <div class="num" id="statTotalParcels">-</div>
                <div class="label">พัสดุทั้งหมดในรอบ</div>
                <div class="progress-bar-wrap">
                    <div class="progress-bar-fill" id="progressBar" style="width:0%"></div>
                </div>
            </div>
            <div class="stat-card blue">
                <div class="num" id="statToday">-</div>
                <div class="label">สแกนวันนี้</div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="filter-row">
                    <select id="filterEtd">
                        <option value="">-- เลือกรอบปิดตู้ --</option>
                    </select>
                    <input type="text" id="filterCustomer" placeholder="กรองรหัสลูกค้า...">
                    <button class="btn-filter" onclick="loadHistory()"><i class="fa fa-search"></i> ค้นหา</button>
                    <button class="btn-reset" onclick="resetFilter()"><i class="fa fa-times"></i> ล้าง</button>
                </div>

                <div class="table-responsive">
                    <table class="scan-table" id="scanTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>วันที่สแกน</th>
                                <th>ผู้สแกน</th>
                                <th>เลขกล่อง</th>
                                <th>รหัสลูกค้า</th>
                                <th>เลขพัสดุ</th>
                                <th>น้ำหนัก</th>
                                <th>รอบปิดตู้</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="scanBody">
                            <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:40px;">กำลังโหลด...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('extra-script')
    <script>
        var apiBase = (window.location.pathname.indexOf('/skjtrack') !== -1) ? '/skjtrack' : '';
        var roundsLoaded = false;

        document.getElementById('filterEtd').addEventListener('change', function() {
            loadHistory();
        });

        function loadHistory() {
            var filterEtd = document.getElementById('filterEtd').value;
            var filterCustomer = document.getElementById('filterCustomer').value.trim();

            var url = apiBase + '/scan-history/data?_=' + Date.now();
            if (filterEtd) url += '&etd=' + encodeURIComponent(filterEtd);
            if (filterCustomer) url += '&customer=' + encodeURIComponent(filterCustomer);

            fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('statScanned').textContent = data.stats.total;
                document.getElementById('statToday').textContent = data.stats.today;

                if (data.stats.total_parcels > 0) {
                    document.getElementById('statTotalCard').style.display = '';
                    document.getElementById('statTotalParcels').textContent = data.stats.total_parcels;
                    var pct = Math.round((data.stats.total / data.stats.total_parcels) * 100);
                    document.getElementById('progressBar').style.width = pct + '%';
                    document.getElementById('statScanned').textContent = data.stats.total + ' (' + pct + '%)';
                } else {
                    document.getElementById('statTotalCard').style.display = 'none';
                }

                // Populate ETD dropdown once
                if (!roundsLoaded && data.rounds && data.rounds.length > 0) {
                    var sel = document.getElementById('filterEtd');
                    data.rounds.forEach(function(r) {
                        var opt = document.createElement('option');
                        opt.value = r.etd;
                        opt.textContent = r.etd_display + '  (' + r.scanned + '/' + r.total + ' สแกน)';
                        sel.appendChild(opt);
                    });
                    roundsLoaded = true;
                }

                var tbody = document.getElementById('scanBody');
                if (data.items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:40px;">ไม่พบรายการ</td></tr>';
                    return;
                }
                var html = '';
                data.items.forEach(function(item, i) {
                    html += '<tr>' +
                        '<td>' + (i + 1) + '</td>' +
                        '<td>' + item.scanned_at + '</td>' +
                        '<td>' + (item.scanned_by && item.scanned_by !== '-' ? '<span class="badge-scanner">' + item.scanned_by + '</span>' : '-') + '</td>' +
                        '<td style="font-weight:700;">' + item.box_no + '</td>' +
                        '<td>' + item.customerno + '</td>' +
                        '<td>' + item.track_no + '</td>' +
                        '<td>' + (item.weight || '-') + '</td>' +
                        '<td>' + item.etd + '</td>' +
                        '<td><span class="badge-scanned">ถึงไทยแล้ว</span>' + (item.picked_up ? ' <span class="badge-completed">สำเร็จ</span>' : '') + '</td>' +
                        '</tr>';
                });
                tbody.innerHTML = html;
            })
            .catch(function(err) {
                document.getElementById('scanBody').innerHTML = '<tr><td colspan="9" style="text-align:center;color:#ef4444;">เกิดข้อผิดพลาด: ' + err.message + '</td></tr>';
            });
        }

        function resetFilter() {
            document.getElementById('filterEtd').value = '';
            document.getElementById('filterCustomer').value = '';
            loadHistory();
        }

        loadHistory();
        setInterval(loadHistory, 10000);
    </script>
@endsection
