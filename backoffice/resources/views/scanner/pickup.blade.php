<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สแกนจ่ายของ — SKJ Japan Shipping</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Prompt', sans-serif; background: #0f172a; color: #fff; min-height: 100vh; }

        .top-bar {
            background: linear-gradient(135deg, #7c3aed, #9333ea);
            padding: 12px 16px;
            display: flex; align-items: center; justify-content: space-between;
        }
        .top-bar .title { font-size: 16px; font-weight: 700; }
        .top-bar .user-info { font-size: 12px; opacity: 0.85; }
        .top-bar .btn-back, .top-bar .btn-logout {
            padding: 6px 14px; border: 2px solid rgba(255,255,255,0.3); border-radius: 8px;
            background: transparent; color: #fff; font-size: 12px; font-weight: 600;
            font-family: 'Prompt', sans-serif; cursor: pointer; text-decoration: none;
        }
        .top-bar .nav-btns { display: flex; gap: 8px; }

        /* ===== STEP INDICATOR ===== */
        .steps { display: flex; background: #1e293b; padding: 10px 12px; gap: 6px; }
        .step {
            flex: 1; text-align: center; padding: 7px 4px; border-radius: 8px;
            font-size: 11px; font-weight: 600; color: #64748b; background: #0f172a;
        }
        .step.active { background: #7c3aed; color: #fff; }
        .step.done { background: #059669; color: #fff; }

        /* ===== ROUND SELECT ===== */
        .round-card {
            background: #1e293b; border-radius: 12px; padding: 14px 16px; margin-bottom: 8px;
            display: flex; align-items: center; justify-content: space-between; cursor: pointer;
            border: 2px solid transparent; transition: all 0.2s;
        }
        .round-card:active { transform: scale(0.98); }
        .round-card.selected { border-color: #7c3aed; background: #2d1b69; }
        .round-card .date { font-size: 16px; font-weight: 700; }
        .round-card .stats { font-size: 11px; color: #94a3b8; }
        .round-card .check-circle {
            width: 24px; height: 24px; border-radius: 50%; border: 2px solid #475569;
            display: flex; align-items: center; justify-content: center; font-size: 14px;
        }
        .round-card.selected .check-circle { border-color: #7c3aed; background: #7c3aed; color: #fff; }
        .btn-confirm-rounds {
            display: block; width: 100%; padding: 14px; margin-top: 16px;
            border-radius: 12px; border: none; background: #7c3aed; color: #fff;
            font-size: 16px; font-weight: 700; font-family: 'Prompt', sans-serif;
            cursor: pointer; text-align: center;
        }
        .btn-confirm-rounds:disabled { opacity: 0.4; cursor: not-allowed; }

        /* ===== SECTION ===== */
        .section { display: none; padding: 16px; }
        .section.active { display: block; }

        /* ===== CUSTOMER SELECT ===== */
        .search-box {
            background: #1e293b; border-radius: 16px; padding: 24px; margin-bottom: 16px;
        }
        .search-box .icon { font-size: 40px; text-align: center; margin-bottom: 8px; }
        .search-box .hint { text-align: center; font-size: 14px; color: #94a3b8; margin-bottom: 16px; }
        .search-box input {
            width: 100%; padding: 14px 16px; border-radius: 12px; border: 2px solid #334155;
            background: #0f172a; color: #fff; font-size: 20px; font-weight: 700;
            font-family: 'Prompt', sans-serif; text-align: center; text-transform: uppercase;
        }
        .search-box input:focus { outline: none; border-color: #7c3aed; }
        .search-box input::placeholder { color: #475569; font-weight: 400; }

        /* ===== CUSTOMER LIST ===== */
        .customer-list { max-height: 55vh; overflow-y: auto; }
        .customer-card {
            background: #1e293b; border-radius: 12px; padding: 14px 16px; margin-bottom: 8px;
            display: flex; align-items: center; justify-content: space-between; cursor: pointer;
            border: 2px solid transparent; transition: all 0.2s;
        }
        .customer-card:active { transform: scale(0.98); }
        .customer-card .name { font-size: 16px; font-weight: 700; }
        .customer-card .info { font-size: 12px; color: #94a3b8; }
        .customer-card .progress-pill {
            padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 700;
            white-space: nowrap;
        }
        .pill-pending { background: #1e40af; color: #93c5fd; }
        .pill-partial { background: #92400e; color: #fbbf24; }
        .pill-done { background: #065f46; color: #6ee7b7; }

        /* ===== SCAN AREA ===== */
        .scan-header {
            background: #1e293b; border-radius: 16px; padding: 16px; margin-bottom: 12px;
            text-align: center;
        }
        .scan-header .customer-name { font-size: 22px; font-weight: 800; color: #a78bfa; }
        .scan-header .etd-info { font-size: 12px; color: #94a3b8; margin-top: 2px; }

        /* Progress bar */
        .progress-section { margin: 12px 0; }
        .progress-bar-bg {
            width: 100%; height: 28px; background: #334155; border-radius: 14px; overflow: hidden;
            position: relative;
        }
        .progress-bar-fill {
            height: 100%; background: linear-gradient(90deg, #7c3aed, #a78bfa); border-radius: 14px;
            transition: width 0.5s ease; min-width: 0;
        }
        .progress-text {
            position: absolute; top: 0; left: 0; right: 0; bottom: 0;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: #fff; text-shadow: 0 1px 2px rgba(0,0,0,0.5);
        }
        .progress-nums {
            display: flex; justify-content: space-between; margin-top: 6px;
            font-size: 12px; color: #94a3b8;
        }

        /* Complete banner */
        .complete-banner {
            display: none; background: linear-gradient(135deg, #059669, #10b981);
            border-radius: 16px; padding: 20px; text-align: center; margin: 12px 0;
            animation: popIn 0.3s ease;
        }
        .complete-banner.show { display: block; }
        .complete-banner .big { font-size: 32px; font-weight: 800; }
        .complete-banner .sub { font-size: 14px; opacity: 0.9; margin-top: 4px; }

        @keyframes popIn { from { transform: scale(0.8); opacity: 0; } to { transform: scale(1); opacity: 1; } }

        /* Scan input */
        .scan-input-area {
            background: #1e293b; border-radius: 16px; padding: 16px; margin-bottom: 12px;
        }
        .scan-input-area input {
            width: 100%; padding: 14px 16px; border-radius: 12px; border: 2px solid #334155;
            background: #0f172a; color: #fff; font-size: 18px; font-weight: 700;
            font-family: 'Prompt', sans-serif; text-align: center;
        }
        .scan-input-area input:focus { outline: none; border-color: #7c3aed; }
        .scan-input-area input::placeholder { color: #475569; font-weight: 400; }

        /* Status */
        .scan-status {
            padding: 12px; border-radius: 12px; text-align: center;
            font-size: 14px; font-weight: 600; margin-bottom: 12px;
        }
        .scan-status.ready { background: #1e293b; color: #94a3b8; }
        .scan-status.success { background: #065f46; color: #6ee7b7; }
        .scan-status.warning { background: #92400e; color: #fbbf24; }
        .scan-status.error { background: #7f1d1d; color: #fca5a5; }

        /* Parcel list */
        .parcel-list { margin-top: 12px; }
        .parcel-item {
            background: #1e293b; border-radius: 10px; padding: 10px 14px; margin-bottom: 6px;
            display: flex; align-items: center; justify-content: space-between;
            border-left: 4px solid #334155;
        }
        .parcel-item.done { border-left-color: #10b981; opacity: 0.7; }
        .parcel-item .box { font-size: 14px; font-weight: 700; }
        .parcel-item .track { font-size: 11px; color: #94a3b8; }
        .parcel-item .check { font-size: 18px; color: #10b981; }
        .parcel-item .pending-dot { width: 18px; height: 18px; border-radius: 50%; border: 2px solid #475569; }
        .parcel-item.just-scanned { border-left-color: #a78bfa; background: #2d1b69; animation: flashScan 0.5s; }
        @keyframes flashScan { from { background: #7c3aed; } to { background: #2d1b69; } }

        /* Btn change customer */
        .btn-change {
            display: block; width: 100%; padding: 12px; margin-top: 12px;
            border-radius: 12px; border: 2px solid #475569; background: transparent;
            color: #94a3b8; font-size: 14px; font-weight: 600;
            font-family: 'Prompt', sans-serif; cursor: pointer; text-align: center;
        }
        .btn-change:active { background: #1e293b; }

        /* Toast */
        .toast {
            position: fixed; bottom: 80px; left: 50%; transform: translateX(-50%);
            padding: 12px 24px; border-radius: 12px; font-size: 14px; font-weight: 600;
            display: none; z-index: 9999; white-space: nowrap;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
        }
        .toast.success { background: #059669; color: #fff; }
        .toast.error { background: #dc2626; color: #fff; }
        .toast.warning { background: #d97706; color: #fff; }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 4px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #475569; border-radius: 4px; }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div>
        <div class="title"><i class="fa fa-truck"></i> สแกนจ่ายของ</div>
        <div class="user-info">{{ Auth::user()->name }}</div>
    </div>
    <div class="nav-btns">
        <a href="{{ url('/scanner') }}" class="btn-back"><i class="fa fa-barcode"></i> สแกน</a>
        <form action="{{ url('/scanner/logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-logout"><i class="fa fa-sign-out"></i> ออก</button>
        </form>
    </div>
</div>

<!-- Step Indicator -->
<div class="steps">
    <div class="step active" id="step0"><i class="fa fa-calendar"></i> เลือกรอบ</div>
    <div class="step" id="step1"><i class="fa fa-user"></i> ลูกค้า</div>
    <div class="step" id="step2"><i class="fa fa-barcode"></i> จ่ายของ</div>
</div>

<!-- ===== STEP 0: เลือกรอบปิดตู้ ===== -->
<div class="section active" id="sec-rounds">
    <div class="search-box">
        <div class="icon">📅</div>
        <div class="hint">เลือกรอบปิดตู้ที่ต้องการจ่ายของ (เลือกได้หลายรอบ)</div>
    </div>
    <div id="roundList">
        <div style="text-align:center;color:#64748b;padding:20px;">กำลังโหลด...</div>
    </div>
    <button class="btn-confirm-rounds" id="btnConfirmRounds" disabled onclick="confirmRounds()">
        เลือกรอบแล้ว — ถัดไป <i class="fa fa-arrow-right"></i>
    </button>
</div>

<!-- ===== STEP 1: เลือกลูกค้า ===== -->
<div class="section" id="sec-select">
    <div class="search-box">
        <div class="icon">👤</div>
        <div class="hint">พิมพ์รหัสลูกค้า หรือเลือกจากรายการด้านล่าง</div>
        <input type="text" id="customerSearch" placeholder="ANW-xxx" autocomplete="off">
    </div>
    <div class="customer-list" id="customerList">
        <div style="text-align:center;color:#64748b;padding:20px;">กำลังโหลด...</div>
    </div>
    <button class="btn-change" onclick="goToStep0()" style="margin-top:8px;"><i class="fa fa-arrow-left"></i> เปลี่ยนรอบปิดตู้</button>
</div>

<!-- ===== STEP 2: ยิงจ่ายของ ===== -->
<div class="section" id="sec-scan">
    <div class="scan-header">
        <div class="customer-name" id="selCustomer">-</div>
        <div class="etd-info" id="selEtd">-</div>
    </div>

    <!-- Progress -->
    <div class="progress-section">
        <div class="progress-bar-bg">
            <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
            <div class="progress-text" id="progressText">0 / 0</div>
        </div>
        <div class="progress-nums">
            <span>จ่ายแล้ว <strong id="numPickedUp">0</strong></span>
            <span>ทั้งหมด <strong id="numTotal">0</strong></span>
        </div>
    </div>

    <!-- Complete Banner -->
    <div class="complete-banner" id="completeBanner">
        <div class="big">🎉 ครบแล้ว!</div>
        <div class="sub">พัสดุของลูกค้าคนนี้จ่ายครบทุกชิ้นแล้ว</div>
    </div>

    <!-- Scan Input -->
    <div class="scan-input-area">
        <div style="margin-bottom:8px;text-align:center;font-size:14px;font-weight:700;color:#c084fc;">🌟 ยิงบาร์โค้ด = อัพเดทสถานะ "สำเร็จ"</div>
        <div style="display:flex;gap:8px;">
            <input type="text" id="pickupInput" placeholder="สแกนบาร์โค้ดกล่องเพื่อจ่ายของ..." autocomplete="off">
        </div>
    </div>

    <!-- Status -->
    <div class="scan-status ready" id="pickupStatus">📦 พร้อมจ่ายของ — ยิงบาร์โค้ดได้เลย</div>

    <!-- Parcel List -->
    <div class="parcel-list" id="parcelList"></div>

    <!-- Change Customer -->
    <button class="btn-change" onclick="goToStep1()"><i class="fa fa-exchange"></i> เปลี่ยนลูกค้า</button>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
var apiBase = (window.location.pathname.indexOf('/skjtrack') !== -1) ? '/skjtrack' : '';
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var selectedRounds = []; // array of 'YYYY-MM-DD' strings
var currentCustomer = null;
var parcelsData = [];
var scanTimer = null;

// ===== NAVIGATION =====
function showSection(sec) {
    document.querySelectorAll('.section').forEach(function(s) { s.classList.remove('active'); });
    document.getElementById(sec).classList.add('active');
}

function goToStep0() {
    selectedRounds = [];
    currentCustomer = null;
    showSection('sec-rounds');
    document.getElementById('step0').className = 'step active';
    document.getElementById('step1').className = 'step';
    document.getElementById('step2').className = 'step';
    loadRounds();
}

function confirmRounds() {
    if (selectedRounds.length === 0) return;
    showSection('sec-select');
    document.getElementById('step0').className = 'step done';
    document.getElementById('step1').className = 'step active';
    document.getElementById('step2').className = 'step';
    document.getElementById('customerSearch').value = '';
    document.getElementById('customerSearch').focus();
    loadCustomers();
}

function goToStep1() {
    currentCustomer = null;
    showSection('sec-select');
    document.getElementById('step0').className = 'step done';
    document.getElementById('step1').className = 'step active';
    document.getElementById('step2').className = 'step';
    document.getElementById('customerSearch').focus();
    loadCustomers();
}

function goToStep2(customerno) {
    currentCustomer = customerno;
    showSection('sec-scan');
    document.getElementById('step0').className = 'step done';
    document.getElementById('step1').className = 'step done';
    document.getElementById('step2').className = 'step active';
    document.getElementById('selCustomer').textContent = customerno;
    document.getElementById('completeBanner').classList.remove('show');
    setStatus('ready', '📦 พร้อมจ่ายของ — ยิงบาร์โค้ดได้เลย');
    loadCustomerParcels(customerno);
    setTimeout(function() { document.getElementById('pickupInput').focus(); }, 100);
}

function etdParam() {
    return selectedRounds.join(',');
}

// ===== STEP 0: LOAD ROUNDS =====
function loadRounds() {
    fetch(apiBase + '/qr-scan/api/pickup/rounds', { headers: { 'Accept': 'application/json' } })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success || !data.rounds.length) {
            document.getElementById('roundList').innerHTML = '<div style="text-align:center;color:#64748b;padding:20px;">ไม่พบรอบปิดตู้</div>';
            return;
        }
        renderRounds(data.rounds);
        // Auto-select latest round
        if (data.rounds.length > 0) {
            toggleRound(data.rounds[0].etd);
        }
    })
    .catch(function() {
        document.getElementById('roundList').innerHTML = '<div style="text-align:center;color:#ef4444;padding:20px;">เกิดข้อผิดพลาด</div>';
    });
}

var _roundsData = [];
function renderRounds(rounds) {
    _roundsData = rounds;
    var html = '';
    rounds.forEach(function(r) {
        var isSelected = selectedRounds.indexOf(r.etd) !== -1;
        html += '<div class="round-card' + (isSelected ? ' selected' : '') + '" onclick="toggleRound(\'' + r.etd + '\')">' +
            '<div>' +
                '<div class="date">📅 ' + r.etd_display + '</div>' +
                '<div class="stats">' + r.total + ' ชิ้น · สแกนแล้ว ' + r.scanned + ' · จ่ายแล้ว ' + r.picked_up + '</div>' +
            '</div>' +
            '<div class="check-circle">' + (isSelected ? '✓' : '') + '</div>' +
        '</div>';
    });
    document.getElementById('roundList').innerHTML = html;
    updateConfirmBtn();
}

function toggleRound(etd) {
    var idx = selectedRounds.indexOf(etd);
    if (idx !== -1) {
        selectedRounds.splice(idx, 1);
    } else {
        selectedRounds.push(etd);
    }
    renderRounds(_roundsData);
}

function updateConfirmBtn() {
    var btn = document.getElementById('btnConfirmRounds');
    btn.disabled = selectedRounds.length === 0;
    if (selectedRounds.length === 0) {
        btn.textContent = 'กรุณาเลือกรอบปิดตู้';
    } else if (selectedRounds.length === 1) {
        btn.innerHTML = 'เลือก 1 รอบ — ถัดไป <i class="fa fa-arrow-right"></i>';
    } else {
        btn.innerHTML = 'เลือก ' + selectedRounds.length + ' รอบ — ถัดไป <i class="fa fa-arrow-right"></i>';
    }
}

// ===== STEP 1: LOAD CUSTOMERS =====
function loadCustomers() {
    fetch(apiBase + '/qr-scan/api/pickup/customers?etd=' + encodeURIComponent(etdParam()), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success || !data.customers || !data.customers.length) {
            document.getElementById('customerList').innerHTML = '<div style="text-align:center;color:#64748b;padding:20px;">ไม่พบลูกค้าในรอบที่เลือก</div>';
            return;
        }
        renderCustomerList(data.customers);
    })
    .catch(function() {
        document.getElementById('customerList').innerHTML = '<div style="text-align:center;color:#ef4444;padding:20px;">เกิดข้อผิดพลาด</div>';
    });
}

function renderCustomerList(customers, filter) {
    var list = document.getElementById('customerList');
    var filtered = customers;
    if (filter) {
        var f = filter.toUpperCase();
        filtered = customers.filter(function(c) { return c.customerno.toUpperCase().indexOf(f) !== -1; });
    }

    if (filtered.length === 0) {
        list.innerHTML = '<div style="text-align:center;color:#64748b;padding:20px;">ไม่พบลูกค้า</div>';
        return;
    }

    var html = '';
    filtered.forEach(function(c) {
        var pillClass = 'pill-pending';
        var pillText = '0/' + c.total;
        if (c.complete) {
            pillClass = 'pill-done';
            pillText = '✅ ครบ';
        } else if (c.picked_up > 0) {
            pillClass = 'pill-partial';
            pillText = c.picked_up + '/' + c.total;
        }
        html += '<div class="customer-card" onclick="goToStep2(\'' + c.customerno + '\')">' +
            '<div><div class="name">' + c.customerno + '</div>' +
            '<div class="info">' + c.total + ' ชิ้น</div></div>' +
            '<span class="progress-pill ' + pillClass + '">' + pillText + '</span></div>';
    });
    list.innerHTML = html;
    window._allCustomers = customers;
}

// Customer search filter
document.getElementById('customerSearch').addEventListener('input', function() {
    var val = this.value.trim();
    if (window._allCustomers) {
        renderCustomerList(window._allCustomers, val);
    }
});
document.getElementById('customerSearch').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        var val = this.value.trim();
        if (val) goToStep2(val.toUpperCase());
    }
});

// ===== STEP 2: LOAD CUSTOMER PARCELS =====
function loadCustomerParcels(customerno) {
    fetch(apiBase + '/qr-scan/api/pickup/customer/' + encodeURIComponent(customerno) + '?etd=' + encodeURIComponent(etdParam()), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            setStatus('error', data.message);
            document.getElementById('parcelList').innerHTML = '';
            updateProgress(0, 0);
            return;
        }
        var roundInfo = selectedRounds.length > 1 ? (selectedRounds.length + ' รอบ') : '';
        document.getElementById('selEtd').textContent = 'รอบปิดตู้ที่เลือก' + (roundInfo ? ' (' + roundInfo + ')' : '') + ' · ' + data.total + ' ชิ้น';
        parcelsData = data.parcels;
        updateProgress(data.picked_up, data.total);
        renderParcels();
    })
    .catch(function() {
        setStatus('error', 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
    });
}

function updateProgress(picked, total) {
    var pct = total > 0 ? Math.round((picked / total) * 100) : 0;
    document.getElementById('progressFill').style.width = pct + '%';
    document.getElementById('progressText').textContent = picked + ' / ' + total;
    document.getElementById('numPickedUp').textContent = picked;
    document.getElementById('numTotal').textContent = total;

    if (picked >= total && total > 0) {
        document.getElementById('completeBanner').classList.add('show');
    } else {
        document.getElementById('completeBanner').classList.remove('show');
    }
}

function renderParcels(justScannedBox) {
    var html = '';
    parcelsData.forEach(function(p) {
        var isDone = !!p.picked_up_at;
        var extraClass = isDone ? ' done' : '';
        if (justScannedBox && p.box_no === justScannedBox) extraClass = ' just-scanned';
        var boxNum = p.box_no.replace(/^BOX-\d{8}-0*/, '');

        html += '<div class="parcel-item' + extraClass + '">' +
            '<div><div class="box">📦 Box.' + boxNum + (selectedRounds.length > 1 ? ' <span style="font-size:10px;color:#94a3b8;">(' + (p.etd || '') + ')</span>' : '') + '</div>' +
            '<div class="track">' + (p.track_no || '-') + (p.weight ? ' · ' + p.weight + 'kg' : '') + '</div></div>' +
            (isDone ? '<span class="check">✅</span>' : '<span class="pending-dot"></span>') +
            '</div>';
    });
    document.getElementById('parcelList').innerHTML = html;
}

// ===== SCAN PICKUP =====
function firePickupScan(raw) {
    var boxNo = raw.trim();
    if (!boxNo || !/^[\dA-Za-z.\-]+$/.test(boxNo)) {
        playErrorSound();
        showToast('❌ บาร์โค้ดผิด!', 'error');
        return;
    }

    setStatus('ready', '🔍 กำลังตรวจสอบ...');

    fetch(apiBase + '/qr-scan/api/pickup/scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ box_no: boxNo, customerno: currentCustomer, etd: etdParam() })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (!data.success) {
            playErrorSound();
            if (data.type === 'wrong_customer') {
                setStatus('error', data.message);
                showToast('❌ ผิดคน! ของ ' + data.actual_customer, 'error');
            } else {
                setStatus('error', data.message);
                showToast(data.message, 'error');
            }
            return;
        }

        if (data.type === 'duplicate') {
            playWarningSound();
            setStatus('warning', '⚠️ ' + data.message);
            showToast('⚠️ จ่ายแล้ว!', 'warning');
            return;
        }

        // Success
        playSuccessSound();
        var prog = data.progress;
        updateProgress(prog.picked_up, prog.total);

        // Update local data
        parcelsData.forEach(function(p) {
            if (p.box_no.indexOf(boxNo) !== -1 || p.box_no.replace(/^BOX-\d{8}-0*/, '') === boxNo) {
                p.picked_up_at = 'just now';
            }
        });
        renderParcels(data.parcel.box_no);

        if (prog.complete) {
            setStatus('success', '🎉 ครบแล้ว! จ่าย ' + prog.picked_up + '/' + prog.total + ' ชิ้น');
            showToast('🎉 ครบแล้ว!', 'success');
        } else {
            setStatus('success', '✅ จ่าย Box.' + boxNo + ' สำเร็จ → อัพเดทสถานะสำเร็จ (' + prog.picked_up + '/' + prog.total + ')');
            showToast('✅ ' + prog.picked_up + '/' + prog.total, 'success');
        }
    })
    .catch(function(err) {
        playErrorSound();
        setStatus('error', '❌ เกิดข้อผิดพลาด');
    });
}

// ===== INPUT HANDLERS =====
var pickupInput = document.getElementById('pickupInput');

pickupInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (scanTimer) { clearTimeout(scanTimer); scanTimer = null; }
        var raw = pickupInput.value.trim();
        pickupInput.value = '';
        if (raw) firePickupScan(raw);
    }
});

pickupInput.addEventListener('input', function() {
    if (scanTimer) clearTimeout(scanTimer);
    scanTimer = setTimeout(function() {
        var val = pickupInput.value.trim();
        if (val) {
            pickupInput.value = '';
            firePickupScan(val);
        }
    }, 300);
});

// Keep focus on scan input in step 2
document.addEventListener('click', function(e) {
    if (document.getElementById('sec-scan').classList.contains('active')) {
        if (!e.target.closest('.btn-change') && !e.target.closest('.btn-back') && !e.target.closest('.btn-logout')) {
            setTimeout(function() { pickupInput.focus(); }, 10);
        }
    }
});

// ===== UI HELPERS =====
function setStatus(type, msg) {
    var el = document.getElementById('pickupStatus');
    el.innerHTML = msg;
    el.className = 'scan-status ' + type;
}

function showToast(msg, type) {
    var toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast ' + (type || 'success');
    toast.style.display = 'block';
    setTimeout(function() { toast.style.display = 'none'; }, 2500);
}

// ===== SOUNDS =====
var audioCtx = null;
var bufSuccess = null, bufError = null, bufWarning = null;

function getAudioCtx() {
    if (!audioCtx) {
        audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        buildSoundBuffers();
    }
    return audioCtx;
}

function buildSoundBuffers() {
    var ctx = audioCtx, rate = ctx.sampleRate;

    var len1 = Math.floor(rate * 0.25);
    bufSuccess = ctx.createBuffer(1, len1, rate);
    var d1 = bufSuccess.getChannelData(0);
    for (var i = 0; i < len1; i++) {
        var t = i / rate;
        if (t < 0.12) d1[i] = Math.sin(2 * Math.PI * 1200 * t) * 0.35;
        else if (t >= 0.13) d1[i] = Math.sin(2 * Math.PI * 1600 * t) * 0.35;
    }

    var len2 = Math.floor(rate * 0.4);
    bufError = ctx.createBuffer(1, len2, rate);
    var d2 = bufError.getChannelData(0);
    for (var i = 0; i < len2; i++) {
        var t = i / rate;
        if (t < 0.18) d2[i] = (((300 * t * 2) % 2) - 1) * 0.3;
        else if (t >= 0.22) d2[i] = (((200 * t * 2) % 2) - 1) * 0.3;
    }

    var len3 = Math.floor(rate * 0.5);
    bufWarning = ctx.createBuffer(1, len3, rate);
    var d3 = bufWarning.getChannelData(0);
    for (var i = 0; i < len3; i++) {
        var t = i / rate;
        for (var b = 0; b < 3; b++) {
            var start = b * 0.15;
            if (t >= start && t < start + 0.08) d3[i] = Math.sin(2 * Math.PI * 800 * t) * 0.35;
        }
    }
}

function playBuffer(buf) {
    try {
        var ctx = getAudioCtx();
        if (ctx.state === 'suspended') ctx.resume();
        var src = ctx.createBufferSource();
        src.buffer = buf;
        src.connect(ctx.destination);
        src.start(0);
    } catch(e) {}
}

function playSuccessSound() { if (!bufSuccess && audioCtx) buildSoundBuffers(); if (bufSuccess) playBuffer(bufSuccess); }
function playErrorSound() { if (!bufError && audioCtx) buildSoundBuffers(); if (bufError) playBuffer(bufError); }
function playWarningSound() { if (!bufWarning && audioCtx) buildSoundBuffers(); if (bufWarning) playBuffer(bufWarning); }

// Warm audio
function warmAudio() {
    var ctx = getAudioCtx();
    if (ctx.state === 'suspended') ctx.resume();
    document.removeEventListener('click', warmAudio);
    document.removeEventListener('keydown', warmAudio);
}
document.addEventListener('click', warmAudio);
document.addEventListener('keydown', warmAudio);

// ===== INIT =====
loadRounds();
</script>

</body>
</html>
