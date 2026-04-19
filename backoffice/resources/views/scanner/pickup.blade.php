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
        .parcel-item.wholeprice { border-right: 3px solid #f59e0b; }
        .badge-wp { display:inline-block; background:#92400e; color:#fbbf24; font-size:9px; font-weight:700; padding:1px 6px; border-radius:6px; margin-left:4px; }
        @keyframes flashScan { from { background: #7c3aed; } to { background: #2d1b69; } }

        /* Re-check mode */
        .recheck-banner {
            display: none; background: linear-gradient(135deg, #b45309, #d97706);
            border-radius: 16px; padding: 14px; text-align: center; margin: 12px 0;
        }
        .recheck-banner.show { display: block; }
        .recheck-banner .rc-title { font-size: 18px; font-weight: 800; }
        .recheck-banner .rc-sub { font-size: 13px; opacity: 0.9; margin-top: 2px; }
        .recheck-banner .rc-progress { font-size: 22px; font-weight: 800; margin-top: 6px; }
        .btn-recheck {
            padding: 10px 16px; border-radius: 12px; border: 2px solid #d97706;
            background: transparent; color: #fbbf24; font-size: 13px; font-weight: 700;
            font-family: 'Prompt', sans-serif; cursor: pointer; width: 100%; margin-bottom: 8px;
        }
        .btn-recheck:active { background: #92400e; }
        .btn-recheck.active-mode { background: #92400e; border-color: #f59e0b; }
        .parcel-item.rc-verified { border-left-color: #3b82f6 !important; background: #1e3a5f !important; opacity: 1 !important; }
        .parcel-item.rc-pending { border-left-color: #d97706 !important; opacity: 0.5 !important; }

        /* Pile grouping (กองแยก) */
        .pile-header {
            background: linear-gradient(135deg, #1e3a5f, #1e293b);
            border-radius: 10px; padding: 10px 14px; margin-bottom: 4px; margin-top: 12px;
            display: flex; align-items: center; justify-content: space-between;
            border-left: 4px solid var(--pile-color, #7c3aed);
        }
        .pile-header:first-child { margin-top: 0; }
        .pile-header .pile-label {
            font-size: 16px; font-weight: 800; color: var(--pile-color, #a78bfa);
        }
        .pile-header .pile-name {
            font-size: 12px; color: #cbd5e1; margin-left: 8px; font-weight: 400;
        }
        .pile-header .pile-count {
            font-size: 13px; font-weight: 700; color: #94a3b8;
            background: #0f172a; padding: 4px 10px; border-radius: 8px;
        }
        .pile-header .pile-count.pile-done { background: #065f46; color: #6ee7b7; }
        .pile-item { border-left-color: var(--pile-color, #334155) !important; }
        .pile-announce {
            display: none; position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%);
            background: rgba(0,0,0,0.9); border: 3px solid var(--pile-color, #a78bfa);
            border-radius: 24px; padding: 32px 48px; text-align: center;
            z-index: 9998; animation: popIn 0.3s ease;
            min-width: 280px;
        }
        .pile-announce.show { display: block; }
        .pile-announce .pile-num { font-size: 48px; font-weight: 900; color: var(--pile-color, #a78bfa); }
        .pile-announce .pile-boxes { font-size: 22px; font-weight: 700; color: #fff; margin-top: 4px; }
        .pile-announce .pile-recipient { font-size: 14px; color: #94a3b8; margin-top: 8px; }
        .badge-pile { display:inline-block; font-size:9px; font-weight:700; padding:1px 6px; border-radius:6px; margin-left:4px; color:#fff; }
        .badge-self { display:inline-block; background:#dc2626; color:#fff; font-size:10px; font-weight:800; padding:2px 8px; border-radius:6px; margin-right:4px; }

        /* Dimension Modal */
        .dim-overlay {
            display:none; position:fixed; top:0; left:0; right:0; bottom:0;
            background:rgba(0,0,0,0.7); z-index:10000;
            align-items:center; justify-content:center; padding:16px;
        }
        .dim-overlay.show { display:flex; }
        .dim-modal {
            background:#1e293b; border-radius:20px; padding:24px; width:100%; max-width:400px;
            animation: popIn 0.3s ease;
        }
        .dim-modal h3 { text-align:center; font-size:18px; font-weight:800; color:#fbbf24; margin-bottom:4px; }
        .dim-modal .box-label { text-align:center; font-size:14px; color:#94a3b8; margin-bottom:16px; }
        .dim-row {
            display:flex; align-items:center; gap:6px; margin-bottom:12px; justify-content:center; flex-wrap:wrap;
        }
        .dim-row input {
            width:80px; padding:10px 6px; border-radius:10px; border:2px solid #334155;
            background:#0f172a; color:#fff; font-size:18px; font-weight:700;
            font-family:'Prompt',sans-serif; text-align:center;
        }
        .dim-row input:focus { outline:none; border-color:#f59e0b; }
        .dim-row .x { font-size:18px; font-weight:800; color:#94a3b8; }
        .dim-result {
            text-align:center; margin:12px 0 16px; font-size:14px; color:#94a3b8;
        }
        .dim-result .price { font-size:24px; font-weight:800; color:#f59e0b; }
        .dim-btn-row { display:flex; gap:8px; }
        .dim-btn {
            flex:1; padding:14px; border-radius:12px; border:none;
            font-size:16px; font-weight:700; font-family:'Prompt',sans-serif; cursor:pointer;
        }
        .dim-btn-cancel { background:#334155; color:#94a3b8; }
        .dim-btn-confirm { background:#f59e0b; color:#000; }
        .dim-btn-confirm:disabled { opacity:0.4; cursor:not-allowed; }
        .dim-btn-skip { background:#7c3aed; color:#fff; }

        /* Image Preview Modal */
        .img-overlay {
            display:none; position:fixed; top:0; left:0; right:0; bottom:0;
            background:rgba(0,0,0,0.85); z-index:10001;
            align-items:center; justify-content:center; padding:16px;
            flex-direction:column;
        }
        .img-overlay.show { display:flex; }
        .img-overlay .img-title {
            font-size:16px; font-weight:800; color:#fff; margin-bottom:12px; text-align:center;
        }
        .img-overlay img {
            max-width:90vw; max-height:70vh; border-radius:12px;
            object-fit:contain; background:#1e293b;
        }
        .img-overlay .img-no {
            color:#94a3b8; font-size:14px; margin-top:16px; text-align:center;
        }
        .img-overlay .img-close {
            position:absolute; top:16px; right:16px; background:rgba(255,255,255,0.15);
            border:none; color:#fff; font-size:24px; width:40px; height:40px;
            border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center;
        }

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
        <div class="user-info">{{ Auth::guard('scanner')->user()->name ?? Auth::user()->name }}</div>
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
    <div id="selectedRoundBanner" style="background:linear-gradient(135deg,#7c3aed,#6d28d9);border-radius:12px;padding:12px 16px;margin-bottom:10px;text-align:center;">
        <div style="font-size:12px;color:rgba(255,255,255,0.7);">📅 รอบปิดตู้ที่เลือก</div>
        <div id="selectedRoundText" style="font-size:18px;font-weight:800;color:#fff;margin-top:2px;">-</div>
    </div>
    <div class="search-box">
        <div class="icon">👤</div>
        <div class="hint">พิมพ์รหัสลูกค้า หรือเลือกจากรายการด้านล่าง</div>
        <input type="text" id="customerSearch" placeholder="ANW-xxx" autocomplete="off" value="ANW-">
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

    <!-- Change Customer (top) -->
    <button class="btn-change" onclick="goToStep1()" style="margin-bottom:8px;"><i class="fa fa-exchange"></i> เปลี่ยนลูกค้า</button>
    <button class="btn-recheck" id="btnRecheck" onclick="toggleRecheckMode()"><i class="fa fa-refresh"></i> เช็คอีกรอบ</button>

    <!-- Re-check Banner -->
    <div class="recheck-banner" id="recheckBanner">
        <div class="rc-title">🔄 โหมดเช็คอีกรอบ</div>
        <div class="rc-sub">ยิงบาร์โค้ดเพื่อตรวจสอบพัสดุอีกครั้ง</div>
        <div class="rc-progress"><span id="rcChecked">0</span> / <span id="rcTotal">0</span></div>
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
        <div style="margin-bottom:8px;text-align:center;font-size:14px;font-weight:700;color:#c084fc;">🌟 ยิงบาร์โค้ด หรือ พิมพ์เลขกล่อง = อัพเดทสถานะ "สำเร็จ"</div>
        <div style="display:flex;gap:8px;">
            <input type="text" id="pickupInput" placeholder="สแกนบาร์โค้ด หรือพิมพ์เลขกล่อง..." autocomplete="off">
            <button onclick="var v=pickupInput.value.trim();if(v){pickupInput.value='';firePickupScan(v);}" style="padding:0 20px;border-radius:12px;border:none;background:#7c3aed;color:#fff;font-size:16px;font-weight:700;font-family:'Prompt',sans-serif;cursor:pointer;white-space:nowrap;">สแกน</button>
        </div>
        <div style="font-size:11px;color:#64748b;text-align:center;margin-top:8px;">💡 บาร์โค้ดไม่ชัด? พิมพ์เลขกล่อง (เช่น 42) แล้วกด Enter</div>
    </div>

    <!-- Status -->
    <div class="scan-status ready" id="pickupStatus">📦 พร้อมจ่ายของ — ยิงบาร์โค้ดได้เลย</div>

    <!-- Parcel List -->
    <div class="parcel-list" id="parcelList"></div>

</div>

<!-- Dimension Modal (ราคาเหมา) -->
<div class="dim-overlay" id="dimOverlay">
    <div class="dim-modal">
        <h3>📐 วัดขนาดกล่อง</h3>
        <div class="box-label" id="dimBoxLabel">Box.xxx — ราคาเหมา</div>
        <div class="dim-row">
            <input type="number" id="dimWidth" step="0.01" min="0" placeholder="กว้าง" inputmode="decimal">
            <span class="x">×</span>
            <input type="number" id="dimLength" step="0.01" min="0" placeholder="ยาว" inputmode="decimal">
            <span class="x">×</span>
            <input type="number" id="dimHeight" step="0.01" min="0" placeholder="สูง" inputmode="decimal">
        </div>
        <div class="dim-result">
            <span style="font-size:12px;">× 0.01 =</span><br>
            <span class="price" id="dimPrice">0.00</span> <span>บาท</span>
        </div>
        <div class="dim-btn-row">
            <button class="dim-btn dim-btn-cancel" onclick="closeDimModal()">ยกเลิก</button>
            <button class="dim-btn dim-btn-skip" onclick="confirmDim(true)">ข้ามไปก่อน</button>
            <button class="dim-btn dim-btn-confirm" id="dimConfirmBtn" onclick="confirmDim(false)" disabled>ยืนยัน</button>
        </div>
    </div>
</div>

<!-- Pile Announce (กองแยก) -->
<div class="pile-announce" id="pileAnnounce">
    <div class="pile-num" id="pileAnnounceNum">กอง 1</div>
    <div class="pile-boxes" id="pileAnnounceBoxes">= 2 กล่อง</div>
    <div class="pile-recipient" id="pileAnnounceName">Anocha Suksangsri</div>
</div>

<!-- Image Preview -->
<div class="img-overlay" id="imgOverlay" onclick="closeImgPreview(event)">
    <button class="img-close" onclick="closeImgPreview(event)">✕</button>
    <div class="img-title" id="imgTitle"></div>
    <img id="imgPreview" src="" alt="">
    <div class="img-no" id="imgNoImage" style="display:none;">📷 ไม่มีรูปพัสดุ</div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
var apiBase = (window.location.pathname.indexOf('/skjtrack') !== -1) ? '/skjtrack' : '';
var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var selectedRounds = [];
var currentCustomer = null;
var parcelsData = [];
var scanTimer = null;
var sessionAlive = true;
var pileMap = {};       // { delivery_fullname: { pileNum, count, parcels, color } }
var pileByBox = {};     // { box_no: pileNum }
var hasPiles = false;   // only true for ANW-820 with multiple recipients
var recheckMode = false;
var recheckSet = {};    // { box_no: true } — boxes verified in re-check

function isSB(name) { return name && name.indexOf('SB ') === 0; }
function sortSBLast(a, b) {
    var aSB = isSB(a), bSB = isSB(b);
    if (aSB && !bSB) return 1;
    if (!aSB && bSB) return -1;
    if (a < b) return -1;
    if (a > b) return 1;
    return 0;
}

var PILE_COLORS = [
    '#7c3aed','#059669','#dc2626','#2563eb','#d97706','#ec4899','#0891b2','#65a30d',
    '#9333ea','#e11d48','#0d9488','#ca8a04','#6366f1','#16a34a','#ea580c','#8b5cf6',
    '#0284c7','#be185d','#4f46e5','#15803d','#c2410c','#7e22ce','#0369a1','#9f1239',
    '#0e7490','#a16207','#4338ca','#166534','#9a3412','#6d28d9','#075985','#881337',
    '#155e75','#854d0e','#3730a3','#14532d','#7c2d12','#581c87','#0c4a6e','#4c0519',
    '#134e4a','#713f12','#312e81','#052e16','#431407','#3b0764','#083344','#500724',
    '#115e59','#78350f','#1e1b4b','#022c22','#7c2d12','#4a044e','#164e63','#4c0519',
    '#0f766e','#92400e','#3730a3','#064e3b','#9a3412','#86198f','#0e7490','#9f1239'
];

// ===== SESSION KEEP-ALIVE & CSRF REFRESH (ทุก 10 นาที) =====
setInterval(function() {
    fetch(apiBase + '/scanner/pickup', { method: 'GET', headers: { 'Accept': 'text/html' }, credentials: 'same-origin' })
    .then(function(r) {
        if (r.ok) {
            return r.text().then(function(html) {
                var m = html.match(/meta name="csrf-token" content="([^"]+)"/);
                if (m) { csrfToken = m[1]; document.querySelector('meta[name="csrf-token"]').setAttribute('content', csrfToken); }
                if (!sessionAlive) { sessionAlive = true; var w = document.getElementById('sessionWarning'); if (w) w.style.display = 'none'; }
            });
        } else if (r.status === 401 || r.redirected) { sessionAlive = false; showSessionWarning(); }
    }).catch(function() {});
}, 10 * 60 * 1000);

function showSessionWarning() {
    var w = document.getElementById('sessionWarning');
    if (!w) { w = document.createElement('div'); w.id = 'sessionWarning'; w.style.cssText = 'position:fixed;top:0;left:0;right:0;z-index:99999;background:#dc2626;color:#fff;padding:16px;text-align:center;font-size:16px;font-weight:700;'; w.innerHTML = '⚠️ เซสชันหมดอายุ — กรุณา <a href="javascript:location.reload()" style="color:#fef08a;text-decoration:underline;">รีเฟรชหน้านี้</a>'; document.body.prepend(w); }
    w.style.display = 'block';
}

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
    updateRoundBanner();
    var cs = document.getElementById('customerSearch');
    cs.value = 'ANW-';
    cs.focus();
    cs.setSelectionRange(cs.value.length, cs.value.length);
    loadCustomers();
}

function updateRoundBanner() {
    var labels = [];
    selectedRounds.forEach(function(etd) {
        for (var i = 0; i < _roundsData.length; i++) {
            if (_roundsData[i].etd === etd) { labels.push(_roundsData[i].etd_display); break; }
        }
    });
    document.getElementById('selectedRoundText').textContent = labels.length > 0 ? labels.join(' , ') : '-';
}

function goToStep1() {
    currentCustomer = null;
    showSection('sec-select');
    document.getElementById('step0').className = 'step done';
    document.getElementById('step1').className = 'step active';
    document.getElementById('step2').className = 'step';
    updateRoundBanner();
    var cs = document.getElementById('customerSearch');
    cs.value = 'ANW-';
    cs.focus();
    cs.setSelectionRange(cs.value.length, cs.value.length);
    loadCustomers();
}

function goToStep2(customerno) {
    currentCustomer = customerno;
    recheckMode = false;
    recheckSet = {};
    document.getElementById('btnRecheck').classList.remove('active-mode');
    document.getElementById('btnRecheck').innerHTML = '<i class="fa fa-refresh"></i> เช็คอีกรอบ';
    document.getElementById('recheckBanner').classList.remove('show');
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
        if (!data.success || !data.rounds || !data.rounds.length) {
            document.getElementById('roundList').innerHTML = '<div style="text-align:center;color:#64748b;padding:20px;">ไม่พบรอบปิดตู้</div>';
            return;
        }
        renderRounds(data.rounds);
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
        var roundLabels = [];
        selectedRounds.forEach(function(etd) {
            for (var i = 0; i < _roundsData.length; i++) {
                if (_roundsData[i].etd === etd) { roundLabels.push(_roundsData[i].etd_display); break; }
            }
        });
        var roundText = roundLabels.length > 0 ? roundLabels.join(', ') : '-';
        document.getElementById('selEtd').textContent = 'รอบปิดตู้ ' + roundText + ' · ' + data.total + ' ชิ้น';
        parcelsData = data.parcels;
        buildPileMap(data.customerno, data.parcels);
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

function buildPileMap(customerno, parcels) {
    pileMap = {};
    pileByBox = {};
    hasPiles = false;

    var names = {};
    parcels.forEach(function(p) {
        var name = p.delivery_fullname || '';
        if (name) names[name] = true;
    });
    var uniqueNames = Object.keys(names).sort(sortSBLast);

    if (customerno && customerno.toUpperCase().indexOf('ANW-820') === 0 && uniqueNames.length > 1) {
        hasPiles = true;
        uniqueNames.forEach(function(name, idx) {
            pileMap[name] = { pileNum: idx + 1, count: 0, picked: 0, color: PILE_COLORS[idx % PILE_COLORS.length] };
        });
        parcels.forEach(function(p) {
            var name = p.delivery_fullname || '';
            if (pileMap[name]) {
                pileMap[name].count++;
                if (p.picked_up_at) pileMap[name].picked++;
                pileByBox[p.box_no] = pileMap[name].pileNum;
            }
        });
    }
}

function renderParcels(justScannedBox) {
    var html = '';

    if (hasPiles) {
        var grouped = {};
        parcelsData.forEach(function(p) {
            var name = p.delivery_fullname || 'ไม่ระบุ';
            if (!grouped[name]) grouped[name] = [];
            grouped[name].push(p);
        });

        var sortedNames = Object.keys(grouped).sort(sortSBLast);
        sortedNames.forEach(function(name, idx) {
            var pile = pileMap[name] || { pileNum: idx+1, count: grouped[name].length, picked: 0, color: PILE_COLORS[idx % PILE_COLORS.length] };
            var picked = 0;
            grouped[name].forEach(function(p) { if (p.picked_up_at) picked++; });
            var allDone = picked >= grouped[name].length;
            var countClass = allDone ? ' pile-done' : '';
            var isSelfPickup = isSB(name);
            var selfBadge = isSelfPickup ? '<span class="badge-self">รับเอง</span> ' : '';

            html += '<div class="pile-header" style="--pile-color:' + pile.color + ';">' +
                '<div><span class="pile-label" style="color:' + pile.color + ';">' + selfBadge + 'กอง ' + pile.pileNum + '</span>' +
                '<span class="pile-name">' + name + '</span></div>' +
                '<span class="pile-count' + countClass + '">' + (allDone ? '✅ ครบ' : picked + '/' + grouped[name].length) + '</span></div>';

            grouped[name].forEach(function(p) {
                html += renderParcelItem(p, justScannedBox, pile.color, pile.pileNum);
            });
        });
    } else {
        parcelsData.forEach(function(p) {
            html += renderParcelItem(p, justScannedBox, null, null);
        });
    }

    document.getElementById('parcelList').innerHTML = html;
}

function renderParcelItem(p, justScannedBox, pileColor, pileNum) {
    var isDone = !!p.picked_up_at;
    var extraClass = isDone ? ' done' : '';
    if (p.iswholeprice === 1) extraClass += ' wholeprice';
    if (pileColor) extraClass += ' pile-item';
    if (justScannedBox && p.box_no === justScannedBox) extraClass += ' just-scanned';
    var boxNum = p.box_no.replace(/^BOX-\d{8}-0*/, '');
    var wpBadge = p.iswholeprice === 1 ? '<span class="badge-wp">ราคาเหมา</span>' : '';
    var pileBadge = (hasPiles && pileNum) ? '<span class="badge-pile" style="background:' + pileColor + ';">กอง' + pileNum + '</span>' : '';
    var dimInfo = '';
    if (p.iswholeprice === 1 && p.import_cost && parseFloat(p.import_cost) > 0) {
        dimInfo = ' · ค่านำเข้า ' + parseFloat(p.import_cost).toFixed(2) + '฿';
    } else if (p.iswholeprice === 1 && isDone && (!p.import_cost || parseFloat(p.import_cost) === 0)) {
        dimInfo = ' · <span style="color:#fbbf24;">ยังไม่ได้วัดขนาด</span>';
    }

    var pileStyle = pileColor ? ' style="--pile-color:' + pileColor + ';cursor:pointer;"' : ' style="cursor:pointer;"';
    return '<div class="parcel-item' + extraClass + '"' + pileStyle + ' onclick="showParcelImage(\'' + p.box_no.replace(/'/g, "\\'") + '\')">' +
        '<div><div class="box">📦 Box.' + boxNum + wpBadge + pileBadge + (selectedRounds.length > 1 ? ' <span style="font-size:10px;color:#94a3b8;">(' + (p.etd || '') + ')</span>' : '') + '</div>' +
        '<div class="track">' + (p.track_no || '-') + (p.weight ? ' · ' + p.weight + 'kg' : '') + dimInfo + '</div></div>' +
        (isDone ? '<span class="check">✅</span>' : '<span class="pending-dot"></span>') +
        '</div>';
}

// ===== DIMENSION MODAL (ราคาเหมา) =====
var _pendingDimScan = null;

function findParcelByBarcode(raw) {
    var parsed = raw.match(/^(\d{2})(\d{2})-(\d+)$/);
    if (parsed) {
        var boxNum = parseInt(parsed[3], 10).toString();
        for (var i = 0; i < parcelsData.length; i++) {
            if (parcelsData[i].box_no === boxNum) return parcelsData[i];
        }
    }
    var plain = raw.replace(/^0+/, '') || '0';
    for (var i = 0; i < parcelsData.length; i++) {
        if (parcelsData[i].box_no === plain || parcelsData[i].box_no === raw) return parcelsData[i];
    }
    return null;
}

function openDimModal(boxNo, parcel) {
    _pendingDimScan = boxNo;
    var boxNum = parcel ? parcel.box_no.replace(/^BOX-\d{8}-0*/, '') : boxNo;
    document.getElementById('dimBoxLabel').textContent = '📦 Box.' + boxNum + ' — ราคาเหมา';
    document.getElementById('dimWidth').value = '';
    document.getElementById('dimLength').value = '';
    document.getElementById('dimHeight').value = '';
    document.getElementById('dimPrice').textContent = '0.00';
    document.getElementById('dimConfirmBtn').disabled = true;
    document.getElementById('dimOverlay').classList.add('show');
    setTimeout(function() { document.getElementById('dimWidth').focus(); }, 200);
}

function closeDimModal() {
    document.getElementById('dimOverlay').classList.remove('show');
    _pendingDimScan = null;
    setTimeout(function() { document.getElementById('pickupInput').focus(); }, 100);
}

function calcDim() {
    var w = parseFloat(document.getElementById('dimWidth').value) || 0;
    var l = parseFloat(document.getElementById('dimLength').value) || 0;
    var h = parseFloat(document.getElementById('dimHeight').value) || 0;
    var result = w * l * h * 0.01;
    document.getElementById('dimPrice').textContent = result.toFixed(2);
    document.getElementById('dimConfirmBtn').disabled = !(w > 0 && l > 0 && h > 0);
}

document.getElementById('dimWidth').addEventListener('input', calcDim);
document.getElementById('dimLength').addEventListener('input', calcDim);
document.getElementById('dimHeight').addEventListener('input', calcDim);

['dimWidth','dimLength','dimHeight'].forEach(function(id, idx) {
    document.getElementById(id).addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var next = ['dimWidth','dimLength','dimHeight','dimConfirmBtn'][idx + 1];
            if (next === 'dimConfirmBtn') {
                var btn = document.getElementById(next);
                if (!btn.disabled) confirmDim(false);
            } else {
                document.getElementById(next).focus();
            }
        }
    });
});

function confirmDim(skip) {
    if (!_pendingDimScan) return;
    var boxNo = _pendingDimScan;
    document.getElementById('dimOverlay').classList.remove('show');

    var dims = null;
    if (!skip) {
        var w = parseFloat(document.getElementById('dimWidth').value) || 0;
        var l = parseFloat(document.getElementById('dimLength').value) || 0;
        var h = parseFloat(document.getElementById('dimHeight').value) || 0;
        if (w > 0 && l > 0 && h > 0) {
            dims = { width: w, length: l, height: h };
        }
    }

    _pendingDimScan = null;
    doPickupScan(boxNo, dims);
}

// ===== RE-CHECK MODE =====
function toggleRecheckMode() {
    recheckMode = !recheckMode;
    var btn = document.getElementById('btnRecheck');
    var banner = document.getElementById('recheckBanner');
    if (recheckMode) {
        recheckSet = {};
        btn.classList.add('active-mode');
        btn.innerHTML = '<i class="fa fa-times"></i> ออกจากโหมดเช็ค';
        banner.classList.add('show');
        document.getElementById('rcTotal').textContent = parcelsData.length;
        document.getElementById('rcChecked').textContent = '0';
        setStatus('warning', '🔄 โหมดเช็คอีกรอบ — ยิงบาร์โค้ดเพื่อตรวจสอบ');
        renderRecheckParcels();
        playWarningSound();
        showToast('🔄 เข้าโหมดเช็คอีกรอบ', 'warning');
    } else {
        btn.classList.remove('active-mode');
        btn.innerHTML = '<i class="fa fa-refresh"></i> เช็คอีกรอบ';
        banner.classList.remove('show');
        setStatus('ready', '📦 พร้อมจ่ายของ — ยิงบาร์โค้ดได้เลย');
        renderParcels();
    }
    setTimeout(function() { document.getElementById('pickupInput').focus(); }, 100);
}

function renderRecheckParcels() {
    var list = document.getElementById('parcelList');
    var html = '';
    parcelsData.forEach(function(p) {
        var verified = recheckSet[p.box_no] ? true : false;
        var cls = verified ? 'rc-verified' : 'rc-pending';
        var icon = verified ? '<span class="check">✅</span>' : '<span class="pending-dot" style="border-color:#d97706;"></span>';
        html += '<div class="parcel-item ' + cls + '">' +
            '<div><div class="box">กล่อง ' + p.box_no + '</div>' +
            '<div class="track">' + (p.tracking || '-') + '</div></div>' +
            icon + '</div>';
    });
    list.innerHTML = html;
}

function doRecheckScan(boxNo) {
    var found = null;
    parcelsData.forEach(function(p) {
        if (p.box_no === boxNo || p.box_no.replace(/^BOX-\d{8}-0*/, '') === boxNo) {
            found = p;
        }
    });
    if (!found) {
        playErrorSound();
        setStatus('error', '❌ กล่อง ' + boxNo + ' ไม่อยู่ในรายการของลูกค้านี้!');
        showToast('❌ ไม่พบกล่อง ' + boxNo, 'error');
        return;
    }
    if (recheckSet[found.box_no]) {
        playWarningSound();
        setStatus('warning', '⚠️ กล่อง ' + found.box_no + ' ตรวจสอบแล้ว');
        showToast('⚠️ ตรวจสอบแล้ว', 'warning');
        return;
    }
    recheckSet[found.box_no] = true;
    playSuccessSound();
    var checked = Object.keys(recheckSet).length;
    var total = parcelsData.length;
    document.getElementById('rcChecked').textContent = checked;
    setStatus('success', '✅ กล่อง ' + found.box_no + ' ผ่าน! (' + checked + '/' + total + ')');
    showToast('✅ กล่อง ' + found.box_no + ' ผ่าน!', 'success');
    renderRecheckParcels();
    if (checked >= total) {
        setStatus('success', '🎉 เช็คครบทุกชิ้นแล้ว! (' + checked + '/' + total + ')');
        showToast('🎉 เช็คครบแล้ว!', 'success');
    }
}

// ===== SCAN PICKUP =====
function firePickupScan(raw) {
    var boxNo = raw.trim();
    if (!boxNo || !/^[\dA-Za-z.\-]+$/.test(boxNo)) {
        playErrorSound();
        showToast('❌ รูปแบบไม่ถูกต้อง!', 'error');
        return;
    }

    if (recheckMode) {
        doRecheckScan(boxNo);
        return;
    }

    var parcel = findParcelByBarcode(boxNo);
    if (parcel && parcel.iswholeprice === 1 && !parcel.picked_up_at) {
        openDimModal(boxNo, parcel);
        return;
    }

    doPickupScan(boxNo, null);
}

function doPickupScan(boxNo, dims) {
    setStatus('ready', '🔍 กำลังตรวจสอบ กล่อง ' + boxNo + '...');

    var payload = { box_no: boxNo, customerno: currentCustomer, etd: etdParam() };
    if (dims) {
        payload.width = dims.width;
        payload.length = dims.length;
        payload.height = dims.height;
    }

    fetch(apiBase + '/qr-scan/api/pickup/scan', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
    })
    .then(function(r) {
        if (r.status === 401 || r.status === 419 || (r.redirected && r.url.indexOf('login') !== -1)) { sessionAlive = false; showSessionWarning(); throw new Error('SESSION_EXPIRED'); }
        return r.json();
    })
    .then(function(data) {
        if (!data.success) {
            playErrorSound();
            if (data.type === 'not_received') {
                setStatus('error', data.message);
                showToast('❌ ยังไม่ได้สแกนรับเข้า!', 'error');
            } else if (data.type === 'wrong_customer') {
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
            var dupPileInfo = '';
            if (hasPiles && data.parcel && data.parcel.delivery_fullname && pileMap[data.parcel.delivery_fullname]) {
                var dp = pileMap[data.parcel.delivery_fullname];
                dupPileInfo = ' [กอง' + dp.pileNum + ']';
                var pileIsComplete = dp.picked >= dp.count;
                if (pileIsComplete) {
                    dupPileInfo += ' ✅ ครบแล้ว!';
                }
                setTimeout(function() {
                    announcePile(dp.pileNum, dp.count, data.parcel.delivery_fullname, dp.color, dp.picked);
                }, 500);
            }
            setStatus('warning', '⚠️ ' + data.message + dupPileInfo);
            showToast('⚠️ จ่ายแล้ว!' + dupPileInfo, 'warning');
            return;
        }

        // Success
        playSuccessSound();
        var prog = data.progress;
        updateProgress(prog.picked_up, prog.total);

        // Update local data — match by server-confirmed box_no or raw input
        var serverBoxNo = data.parcel.box_no || '';
        var scannedParcel = null;
        parcelsData.forEach(function(p) {
            if (p.box_no === serverBoxNo || p.box_no === boxNo || p.box_no.replace(/^BOX-\d{8}-0*/, '') === boxNo) {
                p.picked_up_at = 'just now';
                if (data.parcel.import_cost) p.import_cost = data.parcel.import_cost;
                scannedParcel = p;
            }
        });

        // Update pile picked count
        var pileName = (scannedParcel && scannedParcel.delivery_fullname) || data.parcel.delivery_fullname || '';
        if (hasPiles && pileName && pileMap[pileName]) {
            pileMap[pileName].picked++;
        }

        renderParcels(data.parcel.box_no);

        // Pile announcement (TTS + visual) — delay 500ms so success sound finishes first
        if (hasPiles) {
            var dName = (scannedParcel && scannedParcel.delivery_fullname) || data.parcel.delivery_fullname || '';
            if (dName && pileMap[dName]) {
                (function(dn) {
                    setTimeout(function() {
                        announcePile(pileMap[dn].pileNum, pileMap[dn].count, dn, pileMap[dn].color, pileMap[dn].picked);
                    }, 500);
                })(dName);
            }
        }

        if (prog.complete) {
            setStatus('success', '🎉 ครบแล้ว! จ่าย ' + prog.picked_up + '/' + prog.total + ' ชิ้น');
            showToast('🎉 ครบแล้ว!', 'success');
        } else {
            var extra = data.parcel.import_cost ? ' (ค่านำเข้า ' + parseFloat(data.parcel.import_cost).toFixed(2) + '฿)' : '';
            var pileInfo = '';
            if (hasPiles) {
                var dN = (scannedParcel && scannedParcel.delivery_fullname) || data.parcel.delivery_fullname || '';
                if (dN && pileMap[dN]) pileInfo = ' [กอง' + pileMap[dN].pileNum + ']';
            }
            setStatus('success', '✅ จ่าย Box.' + boxNo + ' สำเร็จ' + pileInfo + extra + ' (' + prog.picked_up + '/' + prog.total + ')');
            showToast('✅ ' + prog.picked_up + '/' + prog.total + pileInfo, 'success');
        }
    })
    .catch(function(err) {
        if (err.message === 'SESSION_EXPIRED') { playErrorSound(); setStatus('error', '⚠️ เซสชันหมดอายุ — กรุณารีเฟรชหน้า'); return; }
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

var isBarcodeFormat = /^\d{4}-\d+$/;
pickupInput.addEventListener('input', function() {
    if (scanTimer) clearTimeout(scanTimer);
    scanTimer = setTimeout(function() {
        var val = pickupInput.value.trim();
        if (val && isBarcodeFormat.test(val)) {
            pickupInput.value = '';
            firePickupScan(val);
        }
    }, 500);
});

// Keep focus on scan input in step 2 (skip when modals are open)
document.addEventListener('click', function(e) {
    if (document.getElementById('sec-scan').classList.contains('active')) {
        if (e.target.closest('.dim-modal') || e.target.closest('.dim-overlay') || e.target.closest('.img-overlay')) return;
        if (!e.target.closest('.btn-change') && !e.target.closest('.btn-back') && !e.target.closest('.btn-logout')) {
            setTimeout(function() {
                if (!document.getElementById('dimOverlay').classList.contains('show') && !document.getElementById('imgOverlay').classList.contains('show')) {
                    pickupInput.focus();
                }
            }, 10);
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

// ===== PILE ANNOUNCEMENT (กองแยก) =====
var _pileAnnounceTimer = null;

function announcePile(pileNum, totalBoxes, recipientName, color, pickedCount) {
    var el = document.getElementById('pileAnnounce');
    var selfPickup = isSB(recipientName);
    var pileComplete = pickedCount >= totalBoxes;
    el.style.setProperty('--pile-color', color || '#a78bfa');

    var prefix = selfPickup ? '📦 รับเอง · ' : '';
    document.getElementById('pileAnnounceNum').textContent = prefix + 'กอง ' + pileNum;

    if (pileComplete) {
        document.getElementById('pileAnnounceBoxes').textContent = '= ' + totalBoxes + ' กล่อง ✅ ครบแล้ว!';
    } else {
        document.getElementById('pileAnnounceBoxes').textContent = pickedCount + ' / ' + totalBoxes + ' กล่อง';
    }
    document.getElementById('pileAnnounceName').textContent = recipientName || '';
    el.classList.add('show');

    if (_pileAnnounceTimer) clearTimeout(_pileAnnounceTimer);
    _pileAnnounceTimer = setTimeout(function() { el.classList.remove('show'); }, pileComplete ? 4500 : 3000);

    speakPile(pileNum, totalBoxes, selfPickup, pileComplete);
}

function speakPile(pileNum, totalBoxes, selfPickup, pileComplete) {
    var text;
    if (pileComplete) {
        text = (selfPickup ? 'รับเอง ' : '') + 'กอง ' + pileNum + ' เท่ากับ ' + totalBoxes + ' กล่อง ครบแล้วค่ะ';
    } else {
        text = (selfPickup ? 'รับเอง ' : '') + 'กอง ' + pileNum;
    }
    console.log('[TTS] speakPile:', text, 'pileComplete:', pileComplete);
    var url = apiBase + '/qr-scan/api/tts?q=' + encodeURIComponent(text);

    fetch(url, { credentials: 'same-origin' })
        .then(function(r) {
            if (!r.ok) throw new Error('TTS ' + r.status);
            return r.blob();
        })
        .then(function(blob) {
            var blobUrl = URL.createObjectURL(blob);
            var audio = new Audio(blobUrl);
            audio.volume = 1.0;
            audio.play().then(function() {
                console.log('[TTS] playing:', text);
                audio.onended = function() { URL.revokeObjectURL(blobUrl); };
            }).catch(function(e) {
                console.error('[TTS] play failed:', e);
                URL.revokeObjectURL(blobUrl);
            });
        })
        .catch(function(e) { console.error('[TTS] fetch failed:', e); });
}

// ===== IMAGE PREVIEW =====
function showParcelImage(boxNo) {
    var parcel = null;
    for (var i = 0; i < parcelsData.length; i++) {
        if (parcelsData[i].box_no === boxNo) { parcel = parcelsData[i]; break; }
    }
    if (!parcel) return;

    var boxNum = parcel.box_no.replace(/^BOX-\d{8}-0*/, '');
    document.getElementById('imgTitle').textContent = '📦 Box.' + boxNum + ' — ' + (parcel.track_no || '-');

    var imgEl = document.getElementById('imgPreview');
    var noImgEl = document.getElementById('imgNoImage');

    var rawImg = (parcel.box_image || '').trim();
    if (rawImg && rawImg !== '-') {
        var imgSrc = rawImg.indexOf('http') === 0 ? rawImg : (apiBase + '/' + rawImg);
        imgEl.src = imgSrc;
        imgEl.style.display = 'block';
        noImgEl.style.display = 'none';
        imgEl.onerror = function() {
            imgEl.style.display = 'none';
            noImgEl.style.display = 'block';
        };
    } else {
        imgEl.style.display = 'none';
        noImgEl.style.display = 'block';
    }

    document.getElementById('imgOverlay').classList.add('show');
}

function closeImgPreview(e) {
    if (e && e.target !== e.currentTarget && !e.target.classList.contains('img-close')) return;
    document.getElementById('imgOverlay').classList.remove('show');
    document.getElementById('imgPreview').src = '';
    setTimeout(function() { document.getElementById('pickupInput').focus(); }, 100);
}

// ===== INIT =====
loadRounds();
</script>

</body>
</html>
