<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>QR Scanner - SKJ Japan Shipping</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Prompt', sans-serif;
            background: #0f172a;
            color: #fff;
            min-height: 100vh;
            overflow-x: hidden;
        }
        .header {
            background: linear-gradient(135deg, #1e40af, #1d4ed8);
            padding: 16px 20px;
            text-align: center;
            box-shadow: 0 2px 12px rgba(0,0,0,0.3);
        }
        .header h1 { font-size: 18px; font-weight: 600; }
        .header p { font-size: 12px; opacity: 0.8; margin-top: 2px; }

        .scanner-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 16px;
        }

        #reader {
            width: 100%;
            border-radius: 16px;
            overflow: hidden;
            margin-top: 16px;
            border: 3px solid #3b82f6;
        }

        .status-bar {
            margin-top: 16px;
            padding: 14px 18px;
            border-radius: 12px;
            font-size: 14px;
            text-align: center;
            font-weight: 500;
        }
        .status-bar.info { background: #1e3a5f; color: #93c5fd; }
        .status-bar.success { background: #14532d; color: #86efac; }
        .status-bar.error { background: #7f1d1d; color: #fca5a5; }
        .status-bar.loading { background: #1e3a5f; color: #fbbf24; }

        .manual-input {
            margin-top: 20px;
            background: #1e293b;
            border-radius: 16px;
            padding: 20px;
        }
        .manual-input label {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
            display: block;
        }
        .input-group {
            display: flex;
            gap: 8px;
        }
        .manual-input input {
            flex: 1;
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #334155;
            background: #0f172a;
            color: #fff;
            font-size: 16px;
            font-family: 'Prompt', sans-serif;
            outline: none;
        }
        .manual-input input:focus { border-color: #3b82f6; }
        .manual-input button {
            padding: 12px 20px;
            border-radius: 10px;
            border: none;
            background: #3b82f6;
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
            white-space: nowrap;
        }
        .manual-input button:active { background: #2563eb; }

        /* Result card */
        .result-card {
            display: none;
            margin-top: 20px;
            background: #1e293b;
            border-radius: 16px;
            overflow: hidden;
        }
        .result-header {
            background: linear-gradient(135deg, #059669, #10b981);
            padding: 16px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .result-header h2 { font-size: 18px; font-weight: 700; }
        .result-header .count {
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
        }
        .result-body { padding: 16px; }
        .parcel-item {
            background: #0f172a;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .parcel-item:last-child { margin-bottom: 0; }
        .parcel-item .customer { font-weight: 600; font-size: 14px; }
        .parcel-item .track { font-size: 12px; color: #94a3b8; margin-top: 2px; }
        .parcel-item .status-badge {
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 11px;
            font-weight: 600;
            background: #334155;
        }

        /* Update status section */
        .update-section {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #334155;
        }
        .update-section label {
            font-size: 13px;
            color: #94a3b8;
            margin-bottom: 8px;
            display: block;
        }
        .update-section select {
            width: 100%;
            padding: 12px 16px;
            border-radius: 10px;
            border: 2px solid #334155;
            background: #0f172a;
            color: #fff;
            font-size: 14px;
            font-family: 'Prompt', sans-serif;
            margin-bottom: 12px;
        }
        .btn-update {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            font-size: 16px;
            font-weight: 700;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
        }
        .btn-update:active { opacity: 0.8; }
        .btn-update:disabled { opacity: 0.5; cursor: not-allowed; }

        .btn-scan-again {
            display: block;
            width: 100%;
            margin-top: 12px;
            padding: 12px;
            border-radius: 10px;
            border: 2px solid #3b82f6;
            background: transparent;
            color: #3b82f6;
            font-size: 14px;
            font-weight: 600;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
        }

        .update-toast {
            display: none;
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #059669;
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 600;
            box-shadow: 0 4px 20px rgba(0,0,0,0.4);
            z-index: 9999;
            animation: slideUp 0.3s ease;
        }
        @keyframes slideUp {
            from { transform: translateX(-50%) translateY(20px); opacity: 0; }
            to { transform: translateX(-50%) translateY(0); opacity: 1; }
        }

        .history-section {
            margin-top: 24px;
            background: #1e293b;
            border-radius: 16px;
            padding: 16px;
        }
        .history-section h3 { font-size: 14px; color: #94a3b8; margin-bottom: 12px; }
        .history-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 14px;
            background: #0f172a;
            border-radius: 8px;
            margin-bottom: 6px;
            cursor: pointer;
            font-size: 13px;
        }
        .history-item:hover { background: #1e3a5f; }
        .history-item .time { color: #64748b; font-size: 11px; }
    </style>
</head>
<body>

<div class="header">
    <h1>📦 QR Scanner</h1>
    <p>สแกน QR Code กล่องพัสดุ</p>
</div>

<div class="scanner-container">
    <div id="reader"></div>

    <div id="statusBar" class="status-bar info">
        กำลังเปิดกล้อง... กรุณาอนุญาตการใช้กล้อง
    </div>

    <div class="manual-input">
        <label>หรือพิมพ์เลขกล่องเอง</label>
        <div class="input-group">
            <input type="text" id="manualBoxNo" placeholder="เลขกล่อง เช่น 001">
            <button onclick="searchBox()">ค้นหา</button>
        </div>
    </div>

    <div class="result-card" id="resultCard">
        <div class="result-header">
            <h2>📦 <span id="resultBoxNo"></span></h2>
            <span class="count" id="resultStatus"></span>
        </div>
        <div class="result-body">
            <div id="parcelInfo"></div>

            <div class="update-section">
                <label>อัพเดตสถานะ</label>
                <select id="newStatus">
                    @foreach(\Illuminate\Support\Facades\DB::table('shipping_statuses')->get() as $status)
                        <option value="{{ $status->id }}">{{ $status->name }}</option>
                    @endforeach
                </select>
                <button class="btn-update" id="btnUpdate" onclick="updateStatus()">
                    ✅ อัพเดตสถานะ
                </button>
            </div>

            <button class="btn-scan-again" onclick="scanAgain()">
                🔄 สแกนกล่องถัดไป
            </button>
        </div>
    </div>

    <div class="history-section" id="historySection" style="display:none;">
        <h3>📋 ประวัติสแกนล่าสุด</h3>
        <div id="historyList"></div>
    </div>
</div>

<div class="update-toast" id="updateToast"></div>

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
    let html5QrcodeScanner = null;
    let currentBoxNo = null;
    let scanHistory = JSON.parse(localStorage.getItem('qr_scan_history') || '[]');

    document.addEventListener('DOMContentLoaded', function() {
        startScanner();
        renderHistory();
    });

    function startScanner() {
        document.getElementById('statusBar').className = 'status-bar info';
        document.getElementById('statusBar').textContent = 'กำลังเปิดกล้อง... กรุณาอนุญาตการใช้กล้อง';

        html5QrcodeScanner = new Html5Qrcode("reader");
        html5QrcodeScanner.start(
            { facingMode: "environment" },
            { fps: 10, qrbox: { width: 250, height: 250 } },
            onScanSuccess,
            onScanFailure
        ).then(function() {
            document.getElementById('statusBar').className = 'status-bar info';
            document.getElementById('statusBar').innerHTML = '📸 เล็งกล้องไปที่ QR Code บนกล่อง';
        }).catch(function(err) {
            document.getElementById('statusBar').className = 'status-bar error';
            document.getElementById('statusBar').textContent = '❌ ไม่สามารถเปิดกล้องได้: ' + err;
        });
    }

    function onScanSuccess(decodedText) {
        // Extract box_no from URL or use raw text
        let boxNo = decodedText;
        let match = decodedText.match(/qr-scan\/result\/(.+)/);
        if (match) {
            boxNo = decodeURIComponent(match[1]);
        }

        // Stop scanner
        html5QrcodeScanner.stop().then(function() {
            document.getElementById('reader').style.display = 'none';
        });

        loadBoxInfo(boxNo);
    }

    function onScanFailure(error) {
        // Ignore — continuous scanning
    }

    function searchBox() {
        let boxNo = document.getElementById('manualBoxNo').value.trim();
        if (!boxNo) return;

        if (html5QrcodeScanner) {
            html5QrcodeScanner.stop().catch(function(){});
            document.getElementById('reader').style.display = 'none';
        }

        loadBoxInfo(boxNo);
    }

    // Enter key on manual input
    document.getElementById('manualBoxNo').addEventListener('keypress', function(e) {
        if (e.key === 'Enter') searchBox();
    });

    var apiBase = (window.location.hostname === 'localhost') ? '' : '/skjtrack';

    function loadBoxInfo(boxNo) {
        currentBoxNo = boxNo;
        document.getElementById('statusBar').className = 'status-bar loading';
        document.getElementById('statusBar').textContent = '🔍 กำลังค้นหากล่อง ' + boxNo + '...';

        fetch(apiBase + '/qr-scan/api/box/' + encodeURIComponent(boxNo), {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                document.getElementById('statusBar').className = 'status-bar success';
                document.getElementById('statusBar').textContent = '✅ พบกล่อง ' + boxNo;

                showResult(data);
                addHistory(boxNo, data.parcel.customerno);
            } else {
                document.getElementById('statusBar').className = 'status-bar error';
                document.getElementById('statusBar').textContent = '❌ ' + data.message;
                document.getElementById('resultCard').style.display = 'none';
            }
        })
        .catch(function(err) {
            document.getElementById('statusBar').className = 'status-bar error';
            document.getElementById('statusBar').textContent = '❌ เกิดข้อผิดพลาด: ' + err.message;
        });
    }

    function showResult(data) {
        var p = data.parcel;
        document.getElementById('resultBoxNo').textContent = data.box_no;
        document.getElementById('resultStatus').textContent = p.status;

        var html = '<div class="parcel-item" style="flex-direction:column;align-items:flex-start;gap:8px;">';
        html += '<div style="display:flex;justify-content:space-between;width:100%;align-items:center;">';
        html += '<div class="customer" style="font-size:16px;">' + p.customerno + '</div>';
        html += '<span class="status-badge">' + p.status + '</span></div>';
        html += '<div class="track">เลขพัสดุ: ' + p.track_no + '</div>';
        if (p.weight) html += '<div class="track">น้ำหนัก: ' + parseFloat(p.weight).toFixed(2) + ' kg</div>';
        html += '<div class="track">รอบปิดตู้: ' + p.etd + '</div>';
        if (p.note) html += '<div class="track">หมายเหตุ: ' + p.note + '</div>';
        html += '</div>';

        // Set status dropdown to current
        var sel = document.getElementById('newStatus');
        for (var i = 0; i < sel.options.length; i++) {
            if (sel.options[i].value == p.status_id) { sel.selectedIndex = i; break; }
        }

        document.getElementById('parcelInfo').innerHTML = html;
        document.getElementById('resultCard').style.display = 'block';
    }

    function updateStatus() {
        if (!currentBoxNo) return;

        let btn = document.getElementById('btnUpdate');
        let status = document.getElementById('newStatus').value;
        btn.disabled = true;
        btn.textContent = '⏳ กำลังอัพเดต...';

        fetch(apiBase + '/qr-scan/api/update-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ box_no: currentBoxNo, status: status })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.textContent = '✅ อัพเดตสถานะ';

            if (data.success) {
                showToast('✅ ' + data.message);
                // Reload box info to refresh statuses
                loadBoxInfo(currentBoxNo);
            } else {
                showToast('❌ ' + data.message);
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.textContent = '✅ อัพเดตสถานะ';
            showToast('❌ เกิดข้อผิดพลาด');
        });
    }

    function scanAgain() {
        document.getElementById('resultCard').style.display = 'none';
        document.getElementById('reader').style.display = 'block';
        document.getElementById('manualBoxNo').value = '';
        currentBoxNo = null;
        startScanner();
    }

    function showToast(msg) {
        let toast = document.getElementById('updateToast');
        toast.textContent = msg;
        toast.style.display = 'block';
        setTimeout(function() { toast.style.display = 'none'; }, 3000);
    }

    function addHistory(boxNo, customerno) {
        let now = new Date();
        let timeStr = now.getHours().toString().padStart(2,'0') + ':' + now.getMinutes().toString().padStart(2,'0');
        scanHistory.unshift({ box_no: boxNo, customer: customerno, time: timeStr });
        if (scanHistory.length > 30) scanHistory = scanHistory.slice(0, 30);
        localStorage.setItem('qr_scan_history', JSON.stringify(scanHistory));
        renderHistory();
    }

    function renderHistory() {
        if (scanHistory.length === 0) {
            document.getElementById('historySection').style.display = 'none';
            return;
        }
        document.getElementById('historySection').style.display = 'block';
        let html = '';
        scanHistory.forEach(function(h) {
            html += '<div class="history-item" onclick="loadBoxInfo(\'' + h.box_no + '\')">'
            html += '<span>📦 ' + h.box_no + ' — ' + (h.customer || '') + '</span>';
            html += '<span class="time">' + h.time + '</span>';
            html += '</div>';
        });
        document.getElementById('historyList').innerHTML = html;
    }
</script>

</body>
</html>
