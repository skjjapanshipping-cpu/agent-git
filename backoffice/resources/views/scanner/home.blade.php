<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สแกนรับเข้าสินค้า — SKJ Japan Shipping</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/latest/css/font-awesome.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Prompt', sans-serif; background: #0f172a; color: #fff; min-height: 100vh; }

        .top-bar {
            background: linear-gradient(135deg, #1e40af, #2563eb);
            padding: 12px 16px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .top-bar .title { font-size: 16px; font-weight: 700; }
        .top-bar .user-info { font-size: 12px; opacity: 0.85; }
        .top-bar .btn-logout {
            padding: 6px 14px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 8px;
            background: transparent;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
        }

        .tab-nav {
            display: flex;
            background: #1e293b;
            border-bottom: 2px solid #334155;
        }
        .tab-nav .tab {
            flex: 1;
            text-align: center;
            padding: 12px 8px;
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
        }
        .tab-nav .tab.active { color: #3b82f6; border-bottom-color: #3b82f6; }
        .tab-nav .tab i { display: block; font-size: 18px; margin-bottom: 2px; }

        .section { display: none; }
        .section.active { display: block; }

        /* ===== SCANNER SECTION ===== */
        .scanner-body { max-width: 700px; margin: 0 auto; padding: 20px 16px; }

        .scan-input-area {
            background: #1e293b;
            border-radius: 16px;
            padding: 24px;
            text-align: center;
        }
        .scan-input-area .icon { font-size: 40px; margin-bottom: 8px; }
        .scan-input-area .hint { font-size: 13px; color: #94a3b8; margin-bottom: 14px; }
        .scan-input-area input {
            width: 100%;
            padding: 18px 20px;
            border-radius: 12px;
            border: 3px solid #3b82f6;
            background: #0f172a;
            color: transparent;
            caret-color: #3b82f6;
            font-size: 24px;
            font-weight: 700;
            font-family: 'Prompt', sans-serif;
            text-align: center;
            letter-spacing: 1px;
        }
        .scan-input-area input.show-text { color: #fff; }
        .scan-input-area input:focus { outline: none; border-color: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.2); }
        .scan-input-area input::placeholder { color: #475569; font-weight: 400; font-size: 16px; }
        .scan-overlay {
            display: none;
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: #0f172a;
            border-radius: 12px;
            color: #3b82f6;
            font-size: 20px;
            font-weight: 700;
            align-items: center;
            justify-content: center;
            pointer-events: none;
        }
        .scan-overlay.active { display: flex; }

        /* ===== STATS ROW ===== */
        .stats-row {
            display: flex;
            gap: 10px;
            margin-top: 16px;
        }
        .stat-box {
            flex: 1;
            background: #1e293b;
            border-radius: 12px;
            padding: 14px 10px;
            text-align: center;
        }
        .stat-box .num { font-size: 28px; font-weight: 800; }
        .stat-box .lbl { font-size: 11px; color: #64748b; margin-top: 2px; }
        .stat-box.green .num { color: #10b981; }
        .stat-box.yellow .num { color: #f59e0b; }
        .stat-box.blue .num { color: #3b82f6; }

        /* ===== STATUS DISPLAY ===== */
        .scan-status {
            margin-top: 16px;
            padding: 14px 16px;
            border-radius: 12px;
            font-size: 15px;
            text-align: center;
            font-weight: 600;
            transition: all 0.3s;
        }
        .scan-status.ready { color: #93c5fd; background: #1e3a5f; }
        .scan-status.success { color: #34d399; background: #064e3b; }
        .scan-status.warning { color: #fbbf24; background: #78350f; }
        .scan-status.error { color: #f87171; background: #7f1d1d; }

        /* ===== LAST SCAN RESULT ===== */
        .last-scan {
            display: none;
            margin-top: 16px;
            background: #1e293b;
            border-radius: 14px;
            overflow: hidden;
        }
        .last-scan.show { display: block; }
        .last-scan-header {
            padding: 12px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .last-scan-header.ok { background: linear-gradient(135deg, #059669, #10b981); }
        .last-scan-header.dup { background: linear-gradient(135deg, #d97706, #f59e0b); }
        .last-scan-header h3 { font-size: 16px; font-weight: 700; }
        .last-scan-header .badge {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .last-scan-body { padding: 12px 16px; }
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 7px 0;
            border-bottom: 1px solid #334155;
            font-size: 13px;
        }
        .info-row:last-child { border-bottom: none; }
        .info-row .lbl { color: #94a3b8; }
        .info-row .val { font-weight: 600; }

        /* ===== HISTORY TABLE ===== */
        .history-section { padding: 16px; max-width: 900px; margin: 0 auto; }
        .history-card {
            background: #1e293b;
            border-radius: 14px;
            overflow: hidden;
        }
        .history-header {
            padding: 14px 16px;
            font-size: 15px;
            font-weight: 700;
            color: #94a3b8;
            border-bottom: 1px solid #334155;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn-clear-history {
            padding: 4px 12px;
            border-radius: 6px;
            border: 1px solid #475569;
            background: transparent;
            color: #94a3b8;
            font-size: 11px;
            cursor: pointer;
            font-family: 'Prompt', sans-serif;
        }
        .history-table { width: 100%; font-size: 12px; border-collapse: collapse; }
        .history-table th {
            padding: 10px 12px;
            text-align: left;
            font-weight: 700;
            color: #64748b;
            background: #0f172a;
            white-space: nowrap;
        }
        .history-table td {
            padding: 10px 12px;
            border-bottom: 1px solid #1e293b;
            white-space: nowrap;
        }
        .history-table tbody tr { background: #0f172a; }
        .history-table tbody tr:nth-child(even) { background: #1e293b; }
        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
        }
        .status-green { background: #059669; color: #fff; }
        .status-yellow { background: #f59e0b; color: #fff; }
        .empty-msg { padding: 40px 16px; text-align: center; color: #475569; font-size: 14px; }

        /* ===== TOAST ===== */
        .toast {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            padding: 14px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            z-index: 9999;
            max-width: 90%;
            text-align: center;
        }
        .toast.success { background: #059669; color: #fff; }
        .toast.error { background: #dc2626; color: #fff; }
    </style>
</head>
<body>

<!-- Top Bar -->
<div class="top-bar">
    <div>
        <div class="title"><i class="fa fa-barcode"></i> สแกนรับเข้าสินค้า</div>
        <div class="user-info">{{ Auth::user()->name }}</div>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="{{ url('/scanner/pickup') }}" style="padding:6px 14px;border:2px solid rgba(255,255,255,0.3);border-radius:8px;background:transparent;color:#fff;font-size:12px;font-weight:600;font-family:'Prompt',sans-serif;cursor:pointer;text-decoration:none;"><i class="fa fa-truck"></i> จ่ายของ</a>
        <form action="{{ url('/scanner/logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn-logout"><i class="fa fa-sign-out"></i> ออก</button>
        </form>
    </div>
</div>

<!-- Tab Navigation -->
<div class="tab-nav">
    <div class="tab active" onclick="showTab('scan', this)">
        <i class="fa fa-barcode"></i> สแกน
    </div>
    <div class="tab" onclick="showTab('history', this)">
        <i class="fa fa-list-alt"></i> ประวัติสแกน
    </div>
</div>

<!-- ===== SCAN TAB ===== -->
<div class="section active" id="tab-scan">
    <div class="scanner-body">
        <!-- Main Scan Input -->
        <div class="scan-input-area">
            <div class="icon">📦</div>
            <div style="margin-bottom:8px;font-size:15px;font-weight:700;color:#4ade80;">✅ ยิงบาร์โค้ด = อัพเดทสถานะ "สินค้าถึงไทยแล้ว"</div>
            <div class="hint">สแกนบาร์โค้ดกล่องพัสดุ หรือพิมพ์เลขกล่อง แล้วกด Enter เพื่อรับเข้าสินค้า</div>
            <div style="display:flex;gap:8px;position:relative;">
                <div style="position:relative;flex:1;">
                    <input type="text" id="scanInput" placeholder="รอสแกน..." autocomplete="off" autofocus>
                    <div class="scan-overlay" id="scanOverlay">กำลังรับข้อมูล...</div>
                </div>
                <button onclick="var v=scanInput.value.trim();if(v){scanInput.value='';fireScan(v);}" style="padding:0 24px;border-radius:12px;border:none;background:#3b82f6;color:#fff;font-size:18px;font-weight:700;font-family:'Prompt',sans-serif;cursor:pointer;">สแกน</button>
            </div>
        </div>

        <!-- Stats -->
        <div class="stats-row">
            <div class="stat-box green">
                <div class="num" id="countSuccess">0</div>
                <div class="lbl">สแกนสำเร็จ</div>
            </div>
            <div class="stat-box yellow">
                <div class="num" id="countDup">0</div>
                <div class="lbl">ซ้ำ</div>
            </div>
            <div class="stat-box blue">
                <div class="num" id="countTotal">0</div>
                <div class="lbl">สแกนทั้งหมด</div>
            </div>
        </div>

        <!-- Status -->
        <div class="scan-status ready" id="scanStatus">� พร้อมรับเข้าสินค้า — ยิงบาร์โค้ดได้เลย</div>

        <!-- Last Scan Result -->
        <div class="last-scan" id="lastScan">
            <div class="last-scan-header ok" id="lastScanHeader">
                <h3 id="resultBoxNo">📦 Box.1</h3>
                <span class="badge" id="resultBadge">สินค้าถึงไทยแล้ว</span>
            </div>
            <div class="last-scan-body">
                <div class="info-row"><span class="lbl">รหัสลูกค้า</span><span class="val" id="resultCustomer">-</span></div>
                <div class="info-row"><span class="lbl">เลขพัสดุ</span><span class="val" id="resultTrack">-</span></div>
                <div class="info-row"><span class="lbl">น้ำหนัก</span><span class="val" id="resultWeight">-</span></div>
                <div class="info-row"><span class="lbl">วันที่ปิดตู้</span><span class="val" id="resultEtd">-</span></div>
            </div>
        </div>
    </div>
</div>

<!-- ===== HISTORY TAB ===== -->
<div class="section" id="tab-history">
    <div class="history-section">
        <div class="history-card">
            <div class="history-header">
                <span>📋 ประวัติสแกนวันนี้</span>
                <button class="btn-clear-history" onclick="clearHistory()"><i class="fa fa-trash"></i> ล้างประวัติ</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>เวลา</th>
                            <th>เลขกล่อง</th>
                            <th>ลูกค้า</th>
                            <th>เลขพัสดุ</th>
                            <th>สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="historyBody">
                    </tbody>
                </table>
            </div>
            <div class="empty-msg" id="emptyHistory">ยังไม่มีรายการสแกน — เริ่มสแกนเลย!</div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
var apiBase = (window.location.pathname.indexOf('/skjtrack') !== -1) ? '/skjtrack' : '';
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var scanInput = document.getElementById('scanInput');
var countSuccess = 0;
var countDup = 0;
var countTotal = 0;
var scanTimer = null;
var scanOverlay = document.getElementById('scanOverlay');

// ===== TAB =====
function showTab(name, el) {
    document.querySelectorAll('.section').forEach(function(s) { s.classList.remove('active'); });
    document.querySelectorAll('.tab').forEach(function(t) { t.classList.remove('active'); });
    document.getElementById('tab-' + name).classList.add('active');
    if (el) el.closest('.tab').classList.add('active');
    if (name === 'scan') focusInput();
}

// ===== INPUT FOCUS =====
function focusInput() {
    setTimeout(function() { scanInput.focus(); }, 10);
}

document.addEventListener('click', function(e) {
    if (e.target !== scanInput && !e.target.closest('.tab-nav') && !e.target.closest('.btn-logout') && !e.target.closest('.btn-clear-history')) {
        focusInput();
    }
});

// ===== Pre-warm AudioContext =====
function warmAudio() {
    var ctx = getAudioCtx();
    if (ctx.state === 'suspended') ctx.resume();
    document.removeEventListener('click', warmAudio);
    document.removeEventListener('keydown', warmAudio);
}
document.addEventListener('click', warmAudio);
document.addEventListener('keydown', warmAudio);

// ===== FIRE SCAN (ไม่มี lock — ยิงกี่ตัวก็ได้พร้อมกัน) =====
function fireScan(raw) {
    var boxNo = raw.trim();
    if (!boxNo || !/^[\dA-Za-z.\-]+$/.test(boxNo)) {
        playErrorSound();
        showToast('❌ บาร์โค้ดผิด!', 'error');
        return;
    }
    setStatus('searching', '🔍 กล่อง ' + boxNo + '...');
    lookupBox(boxNo);
}

// ===== ENTER KEY =====
scanInput.addEventListener('keydown', function(e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        if (scanTimer) { clearTimeout(scanTimer); scanTimer = null; }
        scanOverlay.classList.remove('active');
        var raw = scanInput.value.trim();
        scanInput.value = '';
        if (raw) fireScan(raw);
    }
});

// ===== AUTO-DETECT (fallback ถ้าไม่มี Enter) =====
scanInput.addEventListener('input', function() {
    if (scanInput.value.length > 0) scanOverlay.classList.add('active');
    if (scanTimer) clearTimeout(scanTimer);
    scanTimer = setTimeout(function() {
        scanOverlay.classList.remove('active');
        var val = scanInput.value.trim();
        if (val) {
            scanInput.value = '';
            fireScan(val);
        }
    }, 300);
});

// ===== STATUS DISPLAY =====
function setStatus(type, msg) {
    var el = document.getElementById('scanStatus');
    el.innerHTML = msg;
    el.className = 'scan-status';
    if (type === 'ready') el.classList.add('ready');
    else if (type === 'success') el.classList.add('success');
    else if (type === 'warning') el.classList.add('warning');
    else if (type === 'error') el.classList.add('error');
    else if (type === 'searching') el.classList.add('warning');
}

function updateCounters() {
    document.getElementById('countSuccess').textContent = countSuccess;
    document.getElementById('countDup').textContent = countDup;
    document.getElementById('countTotal').textContent = countTotal;
}

// ===== SOUNDS (Pre-built buffers → เล่นทันทีไม่มี delay) =====
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
    var ctx = audioCtx;
    var rate = ctx.sampleRate;

    // Success: 2 tones (ding-ding สูง)
    var len1 = Math.floor(rate * 0.25);
    bufSuccess = ctx.createBuffer(1, len1, rate);
    var d1 = bufSuccess.getChannelData(0);
    for (var i = 0; i < len1; i++) {
        var t = i / rate;
        if (t < 0.12) d1[i] = Math.sin(2 * Math.PI * 1200 * t) * 0.35;
        else if (t >= 0.13) d1[i] = Math.sin(2 * Math.PI * 1600 * t) * 0.35;
    }

    // Error: 2 low tones
    var len2 = Math.floor(rate * 0.4);
    bufError = ctx.createBuffer(1, len2, rate);
    var d2 = bufError.getChannelData(0);
    for (var i = 0; i < len2; i++) {
        var t = i / rate;
        var val = 0;
        if (t < 0.18) val = (((300 * t * 2) % 2) - 1) * 0.3;
        else if (t >= 0.22) val = (((200 * t * 2) % 2) - 1) * 0.3;
        d2[i] = val;
    }

    // Warning: 3 beeps
    var len3 = Math.floor(rate * 0.5);
    bufWarning = ctx.createBuffer(1, len3, rate);
    var d3 = bufWarning.getChannelData(0);
    for (var i = 0; i < len3; i++) {
        var t = i / rate;
        for (var b = 0; b < 3; b++) {
            var start = b * 0.15;
            if (t >= start && t < start + 0.08) {
                d3[i] = Math.sin(2 * Math.PI * 800 * t) * 0.35;
            }
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

// ===== TOAST =====
function showToast(msg, type) {
    var toast = document.getElementById('toast');
    toast.textContent = msg;
    toast.className = 'toast ' + (type || 'success');
    toast.style.display = 'block';
    setTimeout(function() { toast.style.display = 'none'; }, 2500);
}

// ===== LOOKUP =====
function lookupBox(boxNo) {
    fetch(apiBase + '/qr-scan/api/box/' + encodeURIComponent(boxNo), {
        headers: { 'Accept': 'application/json' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (data.parcel.scanned_at) {
                // ซ้ำ → เสียง + UI ทันที
                playWarningSound();
                countDup++; countTotal++;
                updateCounters();
                setStatus('warning', '⚠️ กล่อง <strong>' + boxNo + '</strong> สแกนแล้ว! (' + data.parcel.customerno + ')');
                showLastScan(boxNo, data.parcel, 'dup');
                showToast('⚠️ กล่อง ' + boxNo + ' เข้าระบบแล้ว!', 'error');
                addToHistory(boxNo, data.parcel.customerno, data.parcel.track_no, '⚠️ ซ้ำ');
                return;
            }
            // สำเร็จ → เสียง + UI ทันที ไม่ต้องรอ API ตัวที่ 2
            playSuccessSound();
            countSuccess++; countTotal++;
            updateCounters();
            setStatus('success', '✅ กล่อง <strong>' + boxNo + '</strong> → สินค้าถึงไทยแล้ว (' + data.parcel.customerno + ')');
            showLastScan(boxNo, data.parcel, 'ok');
            showToast('✅ ' + boxNo + ' → สินค้าถึงไทยแล้ว', 'success');
            addToHistory(boxNo, data.parcel.customerno, data.parcel.track_no, 'สินค้าถึงไทยแล้ว');
            // บันทึกลง DB แบบ background (ไม่ต้องรอ)
            updateStatusBackground(boxNo);
        } else {
            playErrorSound();
            countTotal++;
            updateCounters();
            setStatus('error', '❌ ไม่พบกล่อง <strong>' + boxNo + '</strong> ในรอบปิดตู้ปัจจุบัน');
            showToast('ไม่พบกล่อง ' + boxNo, 'error');
        }
    })
    .catch(function(err) {
        playErrorSound();
        setStatus('error', '❌ เกิดข้อผิดพลาด: ' + err.message);
        showToast('เกิดข้อผิดพลาด', 'error');
    });
}

// บันทึก scanned_at แบบ background — ไม่บล็อก UI
function updateStatusBackground(boxNo) {
    fetch(apiBase + '/qr-scan/api/update-status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ box_no: boxNo })
    }).catch(function() {});
}

function showLastScan(boxNo, parcel, type) {
    var header = document.getElementById('lastScanHeader');
    header.className = 'last-scan-header ' + (type === 'ok' ? 'ok' : 'dup');
    document.getElementById('resultBoxNo').textContent = '📦 ' + boxNo;
    document.getElementById('resultBadge').textContent = type === 'ok' ? 'สินค้าถึงไทยแล้ว' : '⚠️ ซ้ำ';
    document.getElementById('resultCustomer').textContent = parcel.customerno || '-';
    document.getElementById('resultTrack').textContent = parcel.track_no || '-';
    document.getElementById('resultWeight').textContent = parcel.weight ? parcel.weight + ' kg' : '-';
    document.getElementById('resultEtd').textContent = parcel.etd || '-';
    document.getElementById('lastScan').classList.add('show');
}

// finishScan ไม่จำเป็นแล้ว — ทุกสแกนทำงานอิสระ

// ===== HISTORY (localStorage) =====
var HISTORY_KEY = 'scanner_history';
var historyCount = 0;

function loadHistory() {
    var history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
    if (history.length === 0) return;
    document.getElementById('emptyHistory').style.display = 'none';
    var tbody = document.getElementById('historyBody');
    tbody.innerHTML = '';
    historyCount = history.length;
    // Restore counters from today's data
    var today = new Date();
    var todayStr = ('0' + today.getDate()).slice(-2) + '/' + ('0' + (today.getMonth()+1)).slice(-2) + '/' + today.getFullYear();
    history.forEach(function(item, i) {
        if (item.date === todayStr) {
            if (item.statusText === 'สินค้าถึงไทยแล้ว' || item.statusText === 'รับเข้าแล้ว') countSuccess++;
            else if (item.statusText === '⚠️ ซ้ำ') countDup++;
            countTotal++;
        }
        var tr = document.createElement('tr');
        var badgeClass = (item.statusText === 'สินค้าถึงไทยแล้ว' || item.statusText === 'รับเข้าแล้ว') ? 'status-green' : 'status-yellow';
        tr.innerHTML = '<td>' + (history.length - i) + '</td>' +
            '<td>' + item.time + '</td>' +
            '<td style="font-weight:700;">' + item.boxNo + '</td>' +
            '<td>' + (item.customer || '-') + '</td>' +
            '<td>' + (item.trackNo || '-') + '</td>' +
            '<td><span class="status-badge ' + badgeClass + '">' + item.statusText + '</span></td>';
        tbody.appendChild(tr);
    });
    updateCounters();
}

function addToHistory(boxNo, customer, trackNo, statusText) {
    document.getElementById('emptyHistory').style.display = 'none';
    var tbody = document.getElementById('historyBody');
    var now = new Date();
    var time = ('0' + now.getHours()).slice(-2) + ':' + ('0' + now.getMinutes()).slice(-2) + ':' + ('0' + now.getSeconds()).slice(-2);
    var dateStr = ('0' + now.getDate()).slice(-2) + '/' + ('0' + (now.getMonth()+1)).slice(-2) + '/' + now.getFullYear();

    historyCount++;
    var badgeClass = (statusText === 'สินค้าถึงไทยแล้ว' || statusText === 'รับเข้าแล้ว') ? 'status-green' : 'status-yellow';
    var tr = document.createElement('tr');
    tr.innerHTML = '<td>' + historyCount + '</td>' +
        '<td>' + time + '</td>' +
        '<td style="font-weight:700;">' + boxNo + '</td>' +
        '<td>' + (customer || '-') + '</td>' +
        '<td>' + (trackNo || '-') + '</td>' +
        '<td><span class="status-badge ' + badgeClass + '">' + statusText + '</span></td>';
    tbody.insertBefore(tr, tbody.firstChild);

    var history = JSON.parse(localStorage.getItem(HISTORY_KEY) || '[]');
    history.unshift({ date: dateStr, time: time, boxNo: boxNo, customer: customer, trackNo: trackNo, statusText: statusText });
    if (history.length > 2000) history = history.slice(0, 2000);
    localStorage.setItem(HISTORY_KEY, JSON.stringify(history));
}

function clearHistory() {
    if (!confirm('ต้องการล้างประวัติสแกนทั้งหมด?')) return;
    localStorage.removeItem(HISTORY_KEY);
    document.getElementById('historyBody').innerHTML = '';
    document.getElementById('emptyHistory').style.display = 'block';
    historyCount = 0;
    countSuccess = 0; countDup = 0; countTotal = 0;
    updateCounters();
    showToast('ล้างประวัติสแกนเรียบร้อย', 'success');
}

// ===== INIT =====
loadHistory();
focusInput();
</script>

</body>
</html>
