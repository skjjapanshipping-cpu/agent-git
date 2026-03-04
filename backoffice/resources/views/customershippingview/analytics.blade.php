@extends('home')

@section('title')
    Shipment Analytics
@endsection

@section('extra-css')
    <style>
        /* ========================================
           HIDE LEGACY NAVBAR
           ======================================== */
        .navbar-modern,
        .navbar,
        .main-panel > .navbar {
            display: none !important;
        }
        .content { padding-top: 0 !important; margin-top: 0 !important; }
        .content > .row { margin-top: 0 !important; }
        .main-panel > .content { margin-top: 0 !important; padding: 0 !important; }
        .main-panel > .alert,
        .main-panel > div > .alert,
        .main-panel > div[style*="margin-top"] {
            display: none !important;
        }
        .mobile-nav-toggle { display: none; }

        /* ========================================
           GLOBAL LAYOUT
           ======================================== */
        html, body {
            background-color: #f0f4f8 !important;
            height: 100%;
            overflow-x: hidden;
        }
        .wrapper {
            display: flex !important;
            width: 100% !important;
            overflow-x: hidden;
        }
        .main-panel {
            float: right !important;
            width: calc(100% - 260px) !important;
            min-height: 100vh !important;
            background: #f0f4f8 !important;
            display: flex !important;
            flex-direction: column !important;
        }

        /* ========================================
           ANALYTICS PAGE
           ======================================== */
        .analytics-container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 0 24px 60px;
        }

        /* --- Banner --- */
        .analytics-banner {
            background: linear-gradient(135deg, #0a3d62 0%, #1D8AC9 60%, #38a3d6 100%);
            border-radius: 0 0 24px 24px;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 28px 36px;
            color: white;
            overflow: hidden;
        }
        .banner-decor {
            position: absolute;
            inset: 0;
            overflow: hidden;
            pointer-events: none;
        }
        .banner-decor .circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255,255,255,0.06);
        }
        .banner-decor .c1 { width: 300px; height: 300px; top: -100px; right: -60px; }
        .banner-decor .c2 { width: 200px; height: 200px; bottom: -80px; left: -40px; }
        .banner-decor .c3 { width: 120px; height: 120px; top: 20px; left: 20%; background: rgba(255,255,255,0.04); }
        .banner-left {
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            z-index: 2;
        }
        .banner-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            flex-shrink: 0;
        }
        .banner-text h1 {
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 0 2px;
            line-height: 1.3;
        }
        .banner-text p {
            font-size: 0.82rem;
            opacity: 0.75;
            margin: 0;
            font-weight: 500;
        }
        .banner-right {
            display: flex;
            align-items: center;
            gap: 12px;
            position: relative;
            z-index: 2;
        }
        .banner-email {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.12);
            backdrop-filter: blur(8px);
            padding: 8px 16px;
            border-radius: 24px;
            font-size: 0.82rem;
            font-weight: 600;
            color: white;
            text-decoration: none;
            transition: background 0.2s;
        }
        .banner-email:hover {
            background: rgba(255,255,255,0.2);
            color: white;
            text-decoration: none;
        }
        .banner-email i {
            font-size: 0.9rem;
            opacity: 0.8;
        }
        .banner-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            border: 2px solid rgba(255,255,255,0.3);
            flex-shrink: 0;
        }

        /* --- Summary Cards --- */
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            margin-top: 24px;
        }
        .summary-card {
            background: #fff;
            border-radius: 16px;
            padding: 20px 24px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #e8ecf1;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .summary-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.08);
        }
        .summary-icon {
            width: 52px;
            height: 52px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }
        .summary-icon.blue { background: #eff6ff; color: #3b82f6; }
        .summary-icon.orange { background: #fef2f2; color: #ef4444; }
        .summary-icon.green { background: #f0fdf4; color: #22c55e; }
        .summary-icon.teal { background: #f0fdfa; color: #14b8a6; }
        .summary-icon.navy { background: #fdf2f8; color: #ec4899; }
        .summary-info .label {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 600;
            margin-bottom: 2px;
        }
        .summary-info .value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1D8AC9;
            line-height: 1.2;
        }

        /* --- Chart Grid --- */
        .chart-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
            align-items: stretch;
        }

        /* --- Card --- */
        .analytics-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.04);
            border: 1px solid #e8ecf1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .card-head {
            padding: 18px 24px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .card-head h3 {
            font-size: 1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }
        .card-head .view-all {
            font-size: 0.8rem;
            color: #1D8AC9;
            font-weight: 600;
            text-decoration: none;
        }
        .card-head .view-all:hover { text-decoration: underline; }
        .card-body-analytics {
            padding: 16px 24px 20px;
            flex: 1;
        }

        /* --- Chart Container --- */
        .chart-wrap {
            position: relative;
            width: 100%;
            height: 260px;
        }

        /* --- Donut Legend --- */
        .donut-section {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .donut-legend {
            flex: 1;
        }
        .donut-legend-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .donut-legend-item:last-child { border-bottom: none; }
        .donut-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }
        .donut-legend-label {
            flex: 1;
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 500;
        }
        .donut-legend-value {
            font-size: 0.9rem;
            color: #1e293b;
            font-weight: 700;
        }
        .donut-chart-wrap {
            width: 200px;
            height: 200px;
            flex-shrink: 0;
        }

        /* --- Timeline --- */
        .timeline-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .timeline-item {
            display: flex;
            align-items: flex-start;
            gap: 14px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
        }
        .timeline-item:last-child { border-bottom: none; }
        .timeline-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            margin-top: 5px;
            flex-shrink: 0;
        }
        .timeline-dot.status-1 { background: #94a3b8; }
        .timeline-dot.status-2 { background: #ef4444; }
        .timeline-dot.status-3 { background: #10b981; }
        .timeline-dot.status-4 { background: #ec4899; }
        .timeline-content {
            flex: 1;
            min-width: 0;
        }
        .timeline-track {
            font-weight: 700;
            color: #1D8AC9;
            font-size: 0.88rem;
        }
        .timeline-status {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }
        .timeline-time {
            font-size: 0.75rem;
            color: #94a3b8;
            font-weight: 600;
            white-space: nowrap;
        }

        /* --- Orders Table --- */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.85rem;
        }
        .orders-table th {
            text-align: left;
            color: #94a3b8;
            font-weight: 600;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 10px 8px;
            border-bottom: 2px solid #f1f5f9;
        }
        .orders-table td {
            padding: 10px 8px;
            color: #334155;
            border-bottom: 1px solid #f8fafc;
            vertical-align: middle;
        }
        .orders-table tr:hover td {
            background: #f8fafc;
        }
        .orders-table .link-domain {
            color: #1D8AC9;
            font-weight: 600;
            text-decoration: none;
            max-width: 120px;
            display: inline-block;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .badge-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 0.72rem;
            font-weight: 700;
        }
        .badge-status.ordered { background: #fff7ed; color: #ea580c; }
        .badge-status.paid { background: #f0fdf4; color: #16a34a; }
        .badge-status.waiting { background: #eff6ff; color: #2563eb; }

        /* --- Empty State --- */
        .empty-state {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
        }
        .empty-state i {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }

        /* ========================================
           SIDEBAR OVERRIDES
           ======================================== */
        .sidebar {
            display: flex;
            flex-direction: column;
        }
        .sidebar-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        /* ========================================
           RESPONSIVE
           ======================================== */
        @media (max-width: 992px) {
            .main-panel {
                width: 100% !important;
                float: none !important;
            }
            .analytics-container { padding: 0 16px 40px; }
            .analytics-banner {
                border-radius: 0 0 16px 16px;
                flex-direction: column;
                gap: 12px;
                padding: 20px 24px;
            }
            .banner-left { justify-content: center; }
            .banner-text h1 { font-size: 1.1rem; }
            .banner-right { flex-wrap: wrap; justify-content: center; }
            .banner-email { font-size: 0.75rem; padding: 6px 12px; }
            .summary-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .chart-grid {
                grid-template-columns: 1fr;
            }
            .donut-section {
                flex-direction: column-reverse;
            }
            .donut-chart-wrap {
                width: 180px;
                height: 180px;
            }
            .mobile-nav-toggle {
                display: flex !important;
                position: fixed;
                top: 15px;
                left: 20px;
                z-index: 9000;
                background: white;
                color: #1D8AC9;
                border: none;
                width: 45px;
                height: 45px;
                border-radius: 12px;
                align-items: center;
                justify-content: center;
                font-size: 1.2rem;
                box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.6);
                z-index: 8000;
                backdrop-filter: blur(4px);
            }
            .sidebar-overlay.show { display: block !important; }
        }

        @media (max-width: 600px) {
            .summary-grid {
                grid-template-columns: 1fr 1fr;
                gap: 10px;
            }
            .summary-card {
                padding: 14px 16px;
                gap: 12px;
            }
            .summary-icon {
                width: 42px;
                height: 42px;
                font-size: 1.1rem;
            }
            .summary-info .value { font-size: 1.3rem; }
            .orders-table { font-size: 0.78rem; }
        }
    </style>
@endsection

@section('index')
    <!-- Mobile Toggle -->
    <button class="mobile-nav-toggle" id="sidebarToggle"><i class="fa fa-bars"></i></button>
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <div class="analytics-container">

        <!-- ===== BANNER ===== -->
        <div class="analytics-banner">
            <div class="banner-decor">
                <div class="circle c1"></div>
                <div class="circle c2"></div>
                <div class="circle c3"></div>
            </div>
            <div class="banner-left">
                <div class="banner-icon"><i class="fa fa-bar-chart"></i></div>
                <div class="banner-text">
                    <h1>สถิติและการเคลื่อนไหวพัสดุ</h1>
                    <p>จัดการและติดตามสถานะการจัดส่งสินค้าของคุณ</p>
                </div>
            </div>
            <div class="banner-right">
                <a href="{{ route('profile.index') }}" class="banner-email">
                    <i class="fa fa-envelope-o"></i>
                    {{ Auth::user()->email }}
                </a>
                <div class="banner-avatar"><i class="fa fa-user"></i></div>
            </div>
        </div>

        <!-- ===== SUMMARY CARDS ===== -->
        <div class="summary-grid">
            <div class="summary-card">
                <div class="summary-icon blue"><i class="fa fa-cube"></i></div>
                <div class="summary-info">
                    <div class="label">ทั้งหมด</div>
                    <div class="value">{{ $totalShipments }}</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon orange"><i class="fa fa-ship"></i><i class="fa fa-plane" style="margin-left:4px;font-size:0.9rem;"></i></div>
                <div class="summary-info">
                    <div class="label">กำลังขนส่ง</div>
                    <div class="value">{{ ($statusCounts[2] ?? 0) }}</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon teal"><i class="fa fa-flag"></i></div>
                <div class="summary-info">
                    <div class="label">ถึงไทยแล้ว</div>
                    <div class="value">{{ ($statusCounts[3] ?? 0) }}</div>
                </div>
            </div>
            <div class="summary-card">
                <div class="summary-icon navy"><i class="fa fa-check-circle"></i></div>
                <div class="summary-info">
                    <div class="label">สำเร็จ</div>
                    <div class="value">{{ ($statusCounts[4] ?? 0) }}</div>
                </div>
            </div>
        </div>

        <!-- ===== CHARTS ROW ===== -->
        <div class="chart-grid">

            <!-- Bar Chart: จำนวนพัสดุรายเดือน -->
            <div class="analytics-card">
                <div class="card-head">
                    <h3><i class="fa fa-bar-chart" style="color:#1D8AC9;margin-right:8px;"></i>จำนวนพัสดุรายเดือน</h3>
                </div>
                <div class="card-body-analytics">
                    <div class="chart-wrap">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Donut Chart: สถานะปัจจุบัน -->
            <div class="analytics-card">
                <div class="card-head">
                    <h3><i class="fa fa-pie-chart" style="color:#1D8AC9;margin-right:8px;"></i>สถานะปัจจุบัน</h3>
                    <a href="{{ route('clear.session') }}" class="view-all">ดูทั้งหมด &rsaquo;</a>
                </div>
                <div class="card-body-analytics">
                    <div class="donut-section">
                        <div class="donut-legend">
                            @php
                                $statusColors = [
                                    1 => '#94a3b8',
                                    2 => '#ef4444',
                                    3 => '#10b981',
                                    4 => '#ec4899',
                                ];
                                $statusLabels = $shippingStatuses;
                                $statusLabels[4] = 'สำเร็จ';
                            @endphp
                            @foreach($statusLabels as $id => $name)
                                @if(isset($statusCounts[$id]) && $statusCounts[$id] > 0)
                                <div class="donut-legend-item">
                                    <div class="donut-dot" style="background:{{ $statusColors[$id] ?? '#cbd5e1' }}"></div>
                                    <span class="donut-legend-label">{{ $name }}</span>
                                    <span class="donut-legend-value">{{ $statusCounts[$id] }}</span>
                                </div>
                                @endif
                            @endforeach
                            @if($totalShipments == 0)
                                <div class="empty-state"><i class="fa fa-inbox"></i>ไม่มีข้อมูล</div>
                            @endif
                        </div>
                        <div class="donut-chart-wrap">
                            <canvas id="statusChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ===== BOTTOM ROW ===== -->
        <div class="chart-grid" style="margin-top:20px;">

            <!-- Timeline: ความเคลื่อนไหวล่าสุด (ETD) -->
            <div class="analytics-card">
                <div class="card-head">
                    <h3><i class="fa fa-ship" style="color:#1D8AC9;margin-right:4px;"></i><i class="fa fa-plane" style="color:#2563eb;margin-right:8px;font-size:0.85rem;"></i>ความเคลื่อนไหวล่าสุด</h3>
                    <a href="{{ route('clear.session') }}" class="view-all">ดูทั้งหมด &rsaquo;</a>
                </div>
                <div class="card-body-analytics">
                    @if($etdTimeline->count() > 0)
                    <ul class="timeline-list">
                        @foreach($etdTimeline as $etd)
                        @php
                            $dotClass = 'status-1';
                            if ($etd->main_status == 2) $dotClass = 'status-2';
                            elseif ($etd->main_status == 3) $dotClass = 'status-3';
                            elseif ($etd->main_status == 4) $dotClass = 'status-4';

                            $parts = [];
                            if ($etd->cnt_shipping > 0) $parts[] = 'อยู่ระหว่างขนส่ง ' . $etd->cnt_shipping;
                            if ($etd->cnt_arrived > 0) $parts[] = 'สินค้าถึงไทยแล้ว ' . $etd->cnt_arrived;
                            if ($etd->cnt_completed > 0) $parts[] = 'สำเร็จ ' . $etd->cnt_completed;
                            if ($etd->cnt_pending > 0) $parts[] = 'รอดำเนินการ ' . $etd->cnt_pending;
                            $statusText = count($parts) > 0 ? implode(' · ', $parts) : 'รอดำเนินการ';
                        @endphp
                        @php
                            $isAirEtd = ($etd->shipping_method ?? 1) == 2;
                            $etdIcon = $isAirEtd ? '✈️' : '🚢';
                            $etdLabel = $isAirEtd ? 'รอบเที่ยวบิน' : 'รอบปิดตู้';
                        @endphp
                        <li class="timeline-item">
                            <div class="timeline-dot {{ $dotClass }}"></div>
                            <div class="timeline-content">
                                <div class="timeline-track">{{ $etdIcon }} {{ $etdLabel }} {{ \Carbon\Carbon::parse($etd->etd_date)->format('d/m/Y') }}</div>
                                <div class="timeline-status">{{ $statusText }} รายการ</div>
                            </div>
                            <div class="timeline-time" style="text-align:right;">
                                <span style="font-size:0.7rem;color:#94a3b8;">รวมพัสดุ</span><br>
                                <span style="font-size:1rem;font-weight:800;color:#1D8AC9;">{{ $etd->total }}</span>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                    @else
                        <div class="empty-state"><i class="fa fa-inbox"></i>ไม่มีข้อมูลความเคลื่อนไหว</div>
                    @endif
                </div>
            </div>

            <!-- Orders Table: รายการสินค้าล่าสุด -->
            <div class="analytics-card">
                <div class="card-head">
                    <h3><i class="fa fa-shopping-cart" style="color:#1D8AC9;margin-right:8px;"></i>รายการสินค้าล่าสุด</h3>
                    <a href="{{ route('orderview.index') }}" class="view-all">ดูทั้งหมด &rsaquo;</a>
                </div>
                <div class="card-body-analytics" style="padding-bottom:8px;">
                    @if($recentOrders->count() > 0)
                    <div style="overflow-x:auto;">
                    <table class="orders-table">
                        <thead>
                            <tr>
                                <th>วันที่สั่งซื้อ</th>
                                <th>Link</th>
                                <th>ราคา(฿)</th>
                                <th>สถานะ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentOrders as $order)
                            <tr>
                                <td style="font-weight:700;color:#1e293b;">{{ $order->order_date ? $order->order_date->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @if($order->link)
                                        @php
                                            $domain = strtolower(str_replace('www.', '', parse_url($order->link, PHP_URL_HOST) ?? ''));
                                            $siteNames = [
                                                'mercari' => 'Mercari',
                                                'amazon' => 'Amazon',
                                                'rakuten' => 'Rakuten',
                                                'yahoo' => 'Yahoo',
                                                'paypayfleamarket' => 'PayPay',
                                                'fril' => 'Fril',
                                                'suruga-ya' => 'Suruga-ya',
                                                'auctions.yahoo' => 'Yahoo Auction',
                                                'shopping.yahoo' => 'Yahoo Shopping',
                                                'jp-mercari' => 'Mercari',
                                            ];
                                            $displayName = null;
                                            foreach ($siteNames as $key => $label) {
                                                if (strpos($domain, $key) !== false) {
                                                    $displayName = $label;
                                                    break;
                                                }
                                            }
                                            if (!$displayName) {
                                                $parts = explode('.', $domain);
                                                $displayName = ucfirst($parts[0] === 'jp' && isset($parts[1]) ? $parts[1] : $parts[0]);
                                            }
                                        @endphp
                                        <a href="{{ $order->link }}" target="_blank" class="link-domain" title="{{ $domain }}">{{ $displayName }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $order->product_cost_baht ? number_format($order->product_cost_baht, 0) : '-' }}</td>
                                <td>
                                    @php
                                        $payName = $payStatuses[$order->status] ?? '-';
                                        $badgeClass = 'waiting';
                                        if ($order->status == 1) $badgeClass = 'ordered';
                                        elseif ($order->status == 2) $badgeClass = 'paid';
                                    @endphp
                                    <span class="badge-status {{ $badgeClass }}">{{ $payName }}</span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @else
                        <div class="empty-state"><i class="fa fa-inbox"></i>ไม่มีรายการสินค้า</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
@endsection

@section('extra-script')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        // Mobile sidebar
        document.addEventListener('DOMContentLoaded', function () {
            var toggle = document.getElementById('sidebarToggle');
            var overlay = document.getElementById('sidebarOverlay');
            var sidebar = document.querySelector('.sidebar-modern');
            if (toggle && overlay && sidebar) {
                toggle.addEventListener('click', function() { sidebar.classList.add('show'); overlay.classList.add('show'); });
                overlay.addEventListener('click', function() { sidebar.classList.remove('show'); overlay.classList.remove('show'); });
            }
        });

        // ===== Bar Chart: จำนวนพัสดุรายเดือน =====
        var monthlyLabels = @json(array_column($monthlyData, 'label'));
        var monthlyCounts = @json(array_column($monthlyData, 'count'));

        var barCtx = document.getElementById('monthlyChart').getContext('2d');
        var barGradient = barCtx.createLinearGradient(0, 0, 0, 260);
        barGradient.addColorStop(0, 'rgba(29, 138, 201, 0.95)');
        barGradient.addColorStop(1, 'rgba(0, 180, 216, 0.4)');

        var barHoverGradient = barCtx.createLinearGradient(0, 0, 0, 260);
        barHoverGradient.addColorStop(0, 'rgba(10, 61, 98, 1)');
        barHoverGradient.addColorStop(1, 'rgba(29, 138, 201, 0.7)');

        // Plugin: แสดงตัวเลขบนแท่ง
        var datalabelPlugin = {
            id: 'barDatalabels',
            afterDatasetsDraw: function(chart) {
                var ctx2 = chart.ctx;
                chart.data.datasets.forEach(function(dataset, i) {
                    var meta = chart.getDatasetMeta(i);
                    meta.data.forEach(function(bar, index) {
                        var val = dataset.data[index];
                        if (val > 0) {
                            ctx2.save();
                            ctx2.fillStyle = '#0a3d62';
                            ctx2.font = 'bold 14px Montserrat, sans-serif';
                            ctx2.textAlign = 'center';
                            ctx2.textBaseline = 'bottom';
                            ctx2.fillText(val, bar.x, bar.y - 8);
                            ctx2.restore();
                        }
                    });
                });
            }
        };

        new Chart(barCtx, {
            type: 'bar',
            data: {
                labels: monthlyLabels,
                datasets: [{
                    label: 'จำนวนพัสดุ',
                    data: monthlyCounts,
                    backgroundColor: barGradient,
                    hoverBackgroundColor: barHoverGradient,
                    borderColor: 'rgba(29, 138, 201, 0.3)',
                    borderWidth: 1,
                    borderRadius: 12,
                    borderSkipped: false,
                    maxBarThickness: 65,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                layout: { padding: { top: 30 } },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0a3d62',
                        padding: 14,
                        cornerRadius: 10,
                        titleFont: { size: 13, weight: '700' },
                        bodyFont: { size: 14, weight: '600' },
                        displayColors: false,
                        callbacks: {
                            title: function(ctx) { return ctx[0].label; },
                            label: function(ctx) { return '📦 ' + ctx.parsed.y + ' รายการ'; }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0,
                            font: { size: 12, weight: '600' },
                            color: '#94a3b8',
                            padding: 8
                        },
                        grid: { color: '#f1f5f9', drawBorder: false }
                    },
                    x: {
                        ticks: {
                            font: { size: 12, weight: '700' },
                            color: '#1D8AC9',
                            padding: 6
                        },
                        grid: { display: false }
                    }
                },
                animation: {
                    duration: 1200,
                    easing: 'easeOutQuart'
                }
            },
            plugins: [datalabelPlugin]
        });

        // ===== Donut Chart: สถานะปัจจุบัน =====
        var statusData = @json($statusCounts);
        var statusLabelsMap = @json($shippingStatuses);
        statusLabelsMap[4] = 'สำเร็จ';
        var statusColorsMap = {1: '#94a3b8', 2: '#ef4444', 3: '#10b981', 4: '#ec4899'};

        var donutLabels = [];
        var donutValues = [];
        var donutColors = [];

        for (var id in statusData) {
            if (statusData[id] > 0) {
                donutLabels.push(statusLabelsMap[id] || 'สถานะ ' + id);
                donutValues.push(statusData[id]);
                donutColors.push(statusColorsMap[id] || '#cbd5e1');
            }
        }

        if (donutValues.length === 0) {
            donutLabels = ['ไม่มีข้อมูล'];
            donutValues = [1];
            donutColors = ['#e2e8f0'];
        }

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: donutLabels,
                datasets: [{
                    data: donutValues,
                    backgroundColor: donutColors,
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '60%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 10,
                        callbacks: {
                            label: function(ctx) {
                                var total = ctx.dataset.data.reduce(function(a,b){return a+b;},0);
                                var pct = total > 0 ? Math.round(ctx.parsed / total * 100) : 0;
                                return ctx.label + ': ' + ctx.parsed + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    </script>
@endsection
