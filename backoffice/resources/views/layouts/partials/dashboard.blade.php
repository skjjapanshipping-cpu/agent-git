@role('admin')
<div class="dashboard-content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>ยินดีต้อนรับ, {{ Auth::user()->name }}!</h2>
            <p>ระบบจัดการ SKJ Japan Shipping</p>
        </div>
        <div class="welcome-date">
            {{ \Carbon\Carbon::now()->locale('th')->translatedFormat('วันl ที่ j F Y') }}
        </div>
    </div>

    <!-- Stats Grid -->
    <div class="stats-grid">
        <a href="{{ route('users.index') }}" class="stat-card">
            <div class="stat-icon blue">
                <i class="fa fa-users"></i>
            </div>
            <div class="stat-value">{{ App\User::userCount() }}</div>
            <div class="stat-label">Users</div>
        </a>

        <a href="{{ route('dailyrates.index') }}" class="stat-card">
            <div class="stat-icon green">
                <i class="fa fa-jpy"></i>
            </div>
            <div class="stat-value">{{ \App\Models\Dailyrate::curRatePerBath() }} /
                {{ \App\Models\Dailyrate::getCodRate() }}</div>
            <div class="stat-label">Rate / COD Rate</div>
        </a>

        <a href="{{ route('tracks.index') }}" class="stat-card">
            <div class="stat-icon orange">
                <i class="fa fa-th-large"></i>
            </div>
            <div class="stat-value">Stock</div>
            <div class="stat-label">คลังสินค้า</div>
        </a>

        <a href="{{ route('customerorders.index') }}" class="stat-card">
            <div class="stat-icon red">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="stat-value">Orders</div>
            <div class="stat-label">คำสั่งซื้อ</div>
        </a>

        <a href="{{ route('customers.index') }}" class="stat-card">
            <div class="stat-icon purple">
                <i class="fa fa-address-book"></i>
            </div>
            <div class="stat-value">Customer</div>
            <div class="stat-label">ลูกค้า</div>
        </a>

        <a href="{{ route('customershippings.index') }}" class="stat-card">
            <div class="stat-icon cyan">
                <i class="fa fa-truck"></i>
            </div>
            <div class="stat-value">Shipping</div>
            <div class="stat-label">การจัดส่ง</div>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="section-title">
        <span class="title-bar"></span>
        Quick Actions
    </div>
    <div class="quick-actions">
        <a href="https://skjjapanshipping.com/skjtrack/customershippings/create" target="_blank" class="action-card">
            <div class="action-icon gradient-blue">
                <i class="fa fa-plus"></i>
            </div>
            <div class="action-content">
                <h3>เพิ่มรายการพัสดุรอบปิดตู้</h3>
                <p>เพิ่มสินค้าเข้าคลัง</p>
            </div>
        </a>
        <a href="https://skjjapanshipping.com/skjtrack/tracking" target="_blank" class="action-card">
            <div class="action-icon gradient-red">
                <i class="fa fa-search"></i>
            </div>
            <div class="action-content">
                <h3>ค้นหาพัสดุ</h3>
                <p>ติดตามสถานะพัสดุ</p>
            </div>
        </a>
    </div>

    <!-- ยอดค้างชำระ (Outstanding Payments) -->
    @if(isset($pendingPayments) && $pendingPayments->count() > 0)
    @php
        $totalImport = $pendingPayments->sum('pending_import');
        $totalThai = $pendingPayments->sum('pending_thai');
        $totalCustomers = $pendingPayments->pluck('customerno')->unique()->count();
        $totalAll = $totalImport + $totalThai;
    @endphp
    <style>
        .pending-section { margin-top:24px; }
        .pending-stats { display:flex; gap:12px; flex-wrap:wrap; margin-bottom:16px; }
        .p-stat { background:#fff; border:1px solid #e2e8f0; border-radius:12px; padding:14px 20px; flex:1; min-width:140px; text-align:center; }
        .p-stat .p-num { font-size:22px; font-weight:800; color:#1e293b; }
        .p-stat .p-lbl { font-size:11px; font-weight:600; color:#94a3b8; text-transform:uppercase; letter-spacing:0.3px; margin-top:2px; }
        .p-stat.red { border-left:4px solid #dc2626; }
        .p-stat.orange { border-left:4px solid #ea580c; }
        .p-stat.blue { border-left:4px solid #2563eb; }
        .p-stat.green { border-left:4px solid #059669; }
        .pending-table-wrap { background:#fff; border:1px solid #e2e8f0; border-radius:12px; overflow:hidden; }
        .pending-table-header { display:flex; justify-content:space-between; align-items:center; padding:14px 18px; border-bottom:1px solid #f1f5f9; }
        .pending-table-header h4 { margin:0; font-size:14px; font-weight:700; color:#1e293b; }
        .pending-table-header .toggle-btn { background:none; border:1px solid #e2e8f0; border-radius:6px; padding:4px 10px; font-size:12px; color:#64748b; cursor:pointer; }
        .pending-table-header .toggle-btn:hover { background:#f8fafc; }
        .pending-tbl { width:100%; border-collapse:collapse; font-size:13px; }
        .pending-tbl thead th { background:#f8fafc; color:#475569; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.3px; padding:10px 14px; text-align:left; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
        .pending-tbl thead th.num { text-align:right; }
        .pending-tbl tbody td { padding:10px 14px; border-bottom:1px solid #f1f5f9; color:#334155; vertical-align:middle; }
        .pending-tbl tbody td.num { text-align:right; font-variant-numeric:tabular-nums; font-weight:600; }
        .pending-tbl tbody tr:hover { background:#f0f9ff; }
        .pending-tbl tbody tr { cursor:pointer; transition:background 0.15s; }
        .pending-tbl tfoot td { padding:10px 14px; font-weight:800; color:#1e293b; background:#f8fafc; border-top:2px solid #e2e8f0; }
        .pending-tbl tfoot td.num { text-align:right; font-variant-numeric:tabular-nums; }
        .pending-tbl .badge-import { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; background:#fef2f2; color:#dc2626; }
        .pending-tbl .badge-thai { display:inline-block; padding:2px 8px; border-radius:10px; font-size:10px; font-weight:700; background:#fff7ed; color:#ea580c; }
        .pending-tbl .text-muted { color:#cbd5e1; }
        .etd-tag { display:inline-block; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:600; background:#f1f5f9; color:#475569; }
        @media (max-width:768px) {
            .pending-stats { gap:8px; }
            .p-stat { min-width:0; flex:1 1 calc(50% - 8px); padding:10px 12px; }
            .p-stat .p-num { font-size:18px; }
            .pending-tbl { font-size:11px; }
            .pending-tbl thead th, .pending-tbl tbody td, .pending-tbl tfoot td { padding:8px 8px; }
        }
    </style>
    <div class="pending-section">
        <div class="section-title">
            <span class="title-bar"></span>
            ยอดค้างชำระ
        </div>

        <div class="pending-stats">
            <div class="p-stat green">
                <div class="p-num">{{ $totalCustomers }}</div>
                <div class="p-lbl">ลูกค้าค้างชำระ</div>
            </div>
            <div class="p-stat red">
                <div class="p-num">{{ number_format($totalImport, 0) }}</div>
                <div class="p-lbl">ค่านำเข้ารอโอน (฿)</div>
            </div>
            <div class="p-stat orange">
                <div class="p-num">{{ number_format($totalThai, 0) }}</div>
                <div class="p-lbl">ค่าส่งในไทยรอโอน (฿)</div>
            </div>
            <div class="p-stat blue">
                <div class="p-num">{{ number_format($totalAll, 0) }}</div>
                <div class="p-lbl">รวมทั้งหมด (฿)</div>
            </div>
        </div>

        <div class="pending-table-wrap">
            <div class="pending-table-header">
                <h4><i class="fa fa-exclamation-circle" style="color:#dc2626;margin-right:6px;"></i>รายละเอียดยอดค้างชำระ ({{ $pendingPayments->count() }} รายการ)</h4>
                <button class="toggle-btn" onclick="var b=document.getElementById('pendingTableBody');b.style.display=b.style.display==='none'?'':'none';this.textContent=b.style.display==='none'?'แสดง':'ซ่อน';">ซ่อน</button>
            </div>
            <div id="pendingTableBody">
                <table class="pending-tbl">
                    <thead>
                        <tr>
                            <th>รหัสลูกค้า</th>
                            <th>รอบปิดตู้</th>
                            <th class="num">ชิ้น</th>
                            <th class="num">ค่านำเข้าค้างชำระ</th>
                            <th class="num">ค่าส่งในไทยค้างชำระ</th>
                            <th class="num">รวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingPayments as $row)
                        @php
                            $etdFormatted = $row->etd ? \Carbon\Carbon::parse($row->etd)->format('d/m/Y') : '-';
                            $etdParam = $row->etd ? \Carbon\Carbon::parse($row->etd)->format('Y-m-d') : '';
                            $rowTotal = round($row->pending_import + $row->pending_thai, 2);
                        @endphp
                        <tr onclick="window.location='{{ route('customershippings.index') }}'+'?search={{ urlencode($row->customerno) }}&start_date={{ $etdParam }}'">
                            <td><strong>{{ $row->customerno }}</strong></td>
                            <td><span class="etd-tag">{{ $etdFormatted }}</span></td>
                            <td class="num">{{ $row->item_count }}</td>
                            <td class="num">
                                @if($row->has_import_pending)
                                    <span class="badge-import">{{ number_format($row->pending_import, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="num">
                                @if($row->has_thai_pending)
                                    <span class="badge-thai">{{ number_format($row->pending_thai, 2) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="num" style="font-weight:800;">{{ number_format($rowTotal, 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" style="text-align:right;">รวมทั้งหมด</td>
                            <td class="num">{{ number_format($totalImport, 2) }}</td>
                            <td class="num">{{ number_format($totalThai, 2) }}</td>
                            <td class="num">{{ number_format($totalAll, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Mobile Logout Button -->
    <div class="mobile-logout-section">
        <a href="{{ route('logout') }}" class="mobile-logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form-dashboard').submit();">
            <i class="fa fa-sign-out"></i>
            <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form-dashboard" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</div>
@endrole

@role('warden')
<div class="dashboard-content">
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>ยินดีต้อนรับ, {{ Auth::user()->name }}!</h2>
            <p>Warden Panel</p>
        </div>
    </div>

    <div class="stats-grid">
        <a href="{{ route('warden-process-gc') }}" class="stat-card">
            <div class="stat-icon orange">
                <i class="fa fa-truck"></i>
            </div>
            <div class="stat-value">{{ App\Capturegc::gcAlottedCount() }}</div>
            <div class="stat-label">Alotted GC</div>
        </a>
    </div>

    <!-- Mobile Logout Button -->
    <div class="mobile-logout-section">
        <a href="{{ route('logout') }}" class="mobile-logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form-dashboard-w').submit();">
            <i class="fa fa-sign-out"></i>
            <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form-dashboard-w" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</div>
@endrole

@role('user')
<div class="dashboard-content">
    <div class="welcome-banner">
        <div class="welcome-text">
            <h2>ยินดีต้อนรับ, {{ Auth::user()->name }}!</h2>
            <p>ระบบติดตามพัสดุ SKJ Japan Shipping</p>
        </div>
    </div>

    <div class="quick-actions">
        <a href="{{ route('clear.session') }}" class="action-card">
            <div class="action-icon gradient-blue">
                <i class="fa fa-box"></i>
            </div>
            <div class="action-content">
                <h3>My Shipping</h3>
                <p>ดูรายการพัสดุของคุณ</p>
            </div>
        </a>
        <a href="{{ route('orderview.index') }}" class="action-card">
            <div class="action-icon gradient-red">
                <i class="fa fa-shopping-cart"></i>
            </div>
            <div class="action-content">
                <h3>My Orders</h3>
                <p>ดูรายการสั่งซื้อของคุณ</p>
            </div>
        </a>
    </div>

    <!-- Mobile Logout Button -->
    <div class="mobile-logout-section">
        <a href="{{ route('logout') }}" class="mobile-logout-btn"
            onclick="event.preventDefault(); document.getElementById('logout-form-dashboard-u').submit();">
            <i class="fa fa-sign-out"></i>
            <span>ออกจากระบบ</span>
        </a>
        <form id="logout-form-dashboard-u" action="{{ route('logout') }}" method="POST" style="display: none;">
            {{ csrf_field() }}
        </form>
    </div>
</div>
@endrole