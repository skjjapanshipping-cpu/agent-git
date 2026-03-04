<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>พิมพ์สติ๊กเกอร์กล่อง — SKJ Japan Shipping</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { font-family: 'Prompt', sans-serif; background: #f1f5f9; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

        /* ===== SETUP PANEL (screen only) ===== */
        .setup-panel {
            max-width: 560px;
            margin: 30px auto;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .setup-header {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: #fff;
            padding: 20px 24px;
        }
        .setup-header h2 { font-size: 18px; font-weight: 700; }
        .setup-header p { font-size: 12px; opacity: 0.85; margin-top: 4px; }
        .setup-body { padding: 24px; }
        .form-group { margin-bottom: 14px; }
        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 4px;
        }
        .form-group input {
            width: 100%;
            padding: 10px 14px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 16px;
            font-family: 'Prompt', sans-serif;
            transition: border-color 0.2s;
        }
        .form-group input:focus { outline: none; border-color: #7c3aed; }
        .form-row { display: flex; gap: 14px; }
        .form-row .form-group { flex: 1; }

        .summary-box {
            background: #f0fdf4;
            border: 2px solid #bbf7d0;
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 16px;
            text-align: center;
        }
        .summary-box .big { font-size: 26px; font-weight: 800; color: #059669; }
        .summary-box .sub { font-size: 13px; color: #64748b; margin-top: 2px; }

        .btn-row { display: flex; gap: 10px; }
        .btn {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 700;
            font-family: 'Prompt', sans-serif;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.85; }
        .btn-primary { background: #7c3aed; color: #fff; }
        .btn-success { background: #059669; color: #fff; }
        .btn-back { background: #e5e7eb; color: #374151; text-decoration: none; text-align: center; display: inline-flex; align-items: center; justify-content: center; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        .loading-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255,255,255,0.9);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .loading-overlay.active { display: flex; }
        .spinner {
            width: 48px; height: 48px;
            border: 5px solid #e5e7eb;
            border-top-color: #7c3aed;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
            margin-bottom: 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        .loading-text { font-size: 14px; color: #64748b; font-weight: 500; }
        .loading-progress { font-size: 20px; color: #7c3aed; font-weight: 700; margin-top: 4px; }

        .hint-box {
            margin-top: 14px;
            padding: 10px 14px;
            background: #fef3c7;
            border-radius: 8px;
            font-size: 11px;
            color: #92400e;
            line-height: 1.6;
        }

        .preview-note {
            display: none;
            text-align: center;
            padding: 12px 16px;
            font-size: 13px;
            color: #64748b;
            background: #fff;
        }
        .preview-note.active { display: block; }

        /* ===== LABEL TABLE ===== */
        .labels-container { display: none; }
        .labels-container.generated { display: block; }

        .label-table {
            width: 210mm;
            margin: 10px auto;
            border-collapse: collapse;
            table-layout: fixed;
            background: #fff;
        }

        .label-table td {
            width: 70mm;
            height: 40mm;
            border: 2.5px solid #000;
            text-align: center;
            vertical-align: middle;
            padding: 0 2mm 2mm 2mm;
            overflow: hidden;
        }

        .cell-inner {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            gap: 0;
            height: 100%;
        }

        .lbl-anw {
            background: #e91e8c !important;
            color: #fff !important;
            font-weight: 700;
            font-size: 11pt;
            padding: 1.5mm 0;
            width: calc(100% + 4mm);
            margin-left: -2mm;
            margin-right: -2mm;
            letter-spacing: 3px;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .lbl-fields {
            display: flex;
            width: 90%;
            gap: 3mm;
        }

        .lbl-field {
            flex: 1;
            text-align: center;
        }

        .lbl-field-header {
            font-size: 7pt;
            color: #555;
            font-weight: 600;
        }

        .lbl-field-blank {
            border-bottom: 0.5pt solid #000;
            height: 10mm;
            margin-top: 0.5mm;
        }

        .lbl-field-value {
            font-size: 9pt;
            font-weight: 700;
            color: #000;
            height: 10mm;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            padding-bottom: 1mm;
            border-bottom: 0.5pt solid #000;
            margin-top: 0.5mm;
        }

        .lbl-barcode {
            width: 85%;
            margin-top: 1mm;
        }

        .lbl-number {
            font-size: 24pt;
            font-weight: 800;
            color: #000;
            letter-spacing: 0;
            margin-top: auto;
        }
        .lbl-prefix {
            font-size: 12pt;
            font-weight: 600;
        }
        .lbl-etd-date {
            font-size: 9pt;
            font-weight: 600;
            color: #333;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: A4 portrait;
                margin: 5mm 0 0 0;
            }
            html, body {
                background: #fff;
            }
            .no-print { display: none !important; }
            .labels-container { display: block !important; }
            .label-table {
                margin: 0;
                page-break-after: always;
            }
            .label-table:last-child {
                page-break-after: auto;
            }
        }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <div class="loading-text">กำลังสร้างสติ๊กเกอร์...</div>
    <div class="loading-progress" id="loadingProgress">0%</div>
</div>

<!-- Setup Panel -->
<div class="no-print">
    <div class="setup-panel">
        <div class="setup-header">
            <h2>🏷️ พิมพ์สติ๊กเกอร์บาร์โค้ดกล่อง</h2>
            <p>A4 แนวตั้ง — 18 ดวง/แผ่น (3 คอลัมน์ × 6 แถว)</p>
        </div>
        <div class="setup-body">
            <div class="form-group">
                <label>เลือกรอบปิดตู้</label>
                <select id="etdSelect" style="width:100%;padding:10px 14px;border:2px solid #e5e7eb;border-radius:8px;font-size:16px;font-family:'Prompt',sans-serif;background:#fff;cursor:pointer;">
                    <option value="" data-prefix="" data-next="1">-- เลือกรอบปิดตู้ --</option>
                    @foreach($rounds as $round)
                    <option value="{{ $round['etd'] }}" data-prefix="{{ $round['prefix'] }}" data-next="{{ $round['next_start'] }}">{{ $round['etd_display'] }} (ปริ้นแล้ว {{ $round['next_start'] - 1 }} ดวง, ต่อจาก {{ $round['next_start'] }})</option>
                    @endforeach
                    <option value="__new__" data-prefix="" data-next="1">➕ สร้างรอบใหม่...</option>
                </select>
            </div>
            <div class="form-group" id="newRoundGroup" style="display:none;">
                <label>เลือกวันปิดตู้ (วันจันทร์)</label>
                <input type="date" id="newRoundDate" style="width:100%;padding:10px 14px;border:2px solid #7c3aed;border-radius:8px;font-size:16px;font-family:'Prompt',sans-serif;background:#faf5ff;">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>เลขเริ่มต้น</label>
                    <input type="number" id="startNum" value="1" min="1">
                </div>
                <div class="form-group">
                    <label>จำนวนแผ่น A4</label>
                    <input type="number" id="sheetCount" value="1" min="1" max="500">
                </div>
            </div>

            <div class="summary-box">
                <div class="big" id="totalDisplay">18 ดวง</div>
                <div class="sub" id="rangeDisplay">Box.1 — Box.18</div>
            </div>

            <div class="btn-row">
                <a href="{{ route('home') }}" class="btn btn-back">← กลับหน้าหลัก</a>
                <button class="btn btn-primary" id="btnGenerate" onclick="generateLabels()">🏷️ สร้างสติ๊กเกอร์</button>
                <button class="btn btn-success" id="btnPrint" onclick="doPrint()" disabled>🖨️ พิมพ์</button>
            </div>

            <div class="hint-box">
                <strong>วิธีใช้:</strong> เลือกรอบปิดตู้ (หรือสร้างรอบใหม่) → ใส่จำนวนแผ่น → กด "สร้างสติ๊กเกอร์" → กด "พิมพ์"<br>
                <strong>หมายเหตุ:</strong> เลขกล่องรีเซ็ตทุกรอบ — ระบบจำเลขล่าสุดของแต่ละรอบให้อัตโนมัติ<br>
                <strong>ตัวอย่าง:</strong> วันนี้ปริ้น 5 แผ่น (75 ดวง) พรุ่งนี้เลือกรอบเดิมระบบจะเริ่มต่อจาก 76 อัตโนมัติ
            </div>
        </div>
    </div>

    <div class="preview-note" id="previewNote">
        ⬇️ ตัวอย่างสติ๊กเกอร์ด้านล่าง — กดปุ่ม "พิมพ์" เมื่อพร้อม
    </div>
</div>

<!-- Labels Output -->
<div class="labels-container" id="labelsContainer"></div>

<script>
(function() {
    var startInput = document.getElementById('startNum');
    var sheetInput = document.getElementById('sheetCount');
    var etdSelect = document.getElementById('etdSelect');
    var LABELS_PER_PAGE = 18;
    var newRoundGroup = document.getElementById('newRoundGroup');
    var newRoundDate = document.getElementById('newRoundDate');
    var currentEtd = '';
    var currentPrefix = '';

    function getPrefix() {
        return currentPrefix;
    }

    function getEtd() {
        return currentEtd;
    }

    function padNum(n) {
        if (n < 10) return '00' + n;
        if (n < 100) return '0' + n;
        return '' + n;
    }

    function formatLabel(prefix, num) {
        return prefix ? (prefix + '-' + padNum(num)) : ('Box.' + num);
    }

    function dateToPrefixAndEtd(dateStr) {
        if (!dateStr) return { prefix: '', etd: '' };
        var parts = dateStr.split('-');
        return {
            etd: dateStr,
            prefix: parts[2] + parts[1]
        };
    }

    function getEtdDisplay() {
        if (!currentEtd) return '';
        var parts = currentEtd.split('-');
        return parts[2] + '/' + parts[1] + '/' + parts[0];
    }

    startInput.addEventListener('input', updateSummary);
    startInput.addEventListener('change', updateSummary);
    sheetInput.addEventListener('input', updateSummary);
    sheetInput.addEventListener('change', updateSummary);

    etdSelect.addEventListener('change', function() {
        var opt = etdSelect.options[etdSelect.selectedIndex];
        if (opt.value === '__new__') {
            newRoundGroup.style.display = 'block';
            newRoundDate.value = '';
            currentEtd = '';
            currentPrefix = '';
            startInput.value = 1;
        } else {
            newRoundGroup.style.display = 'none';
            if (opt.value) {
                currentEtd = opt.value;
                currentPrefix = opt.getAttribute('data-prefix') || '';
                var nextStart = parseInt(opt.getAttribute('data-next')) || 1;
                startInput.value = nextStart;
            } else {
                currentEtd = '';
                currentPrefix = '';
                startInput.value = 1;
            }
        }
        updateSummary();
    });

    newRoundDate.addEventListener('change', function() {
        var info = dateToPrefixAndEtd(newRoundDate.value);
        currentEtd = info.etd;
        currentPrefix = info.prefix;
        startInput.value = 1;
        updateSummary();
    });

    function updateSummary() {
        var start = parseInt(startInput.value) || 1;
        var sheets = parseInt(sheetInput.value) || 1;
        var total = sheets * LABELS_PER_PAGE;
        var end = start + total - 1;
        var prefix = getPrefix();
        document.getElementById('totalDisplay').textContent = total.toLocaleString() + ' ดวง';
        document.getElementById('rangeDisplay').textContent = 'Box.' + start + ' — Box.' + end;
    }

    window.generateLabels = function() {
        var start = parseInt(startInput.value) || 1;
        var sheets = parseInt(sheetInput.value) || 1;
        var total = sheets * LABELS_PER_PAGE;
        var prefix = getPrefix();
        var container = document.getElementById('labelsContainer');

        // Show loading
        var overlay = document.getElementById('loadingOverlay');
        overlay.classList.add('active');
        document.getElementById('btnGenerate').disabled = true;
        document.getElementById('btnPrint').disabled = true;

        // Clear previous
        container.innerHTML = '';
        container.classList.remove('generated');

        setTimeout(function() {
            var html = '';
            var idx = 0;
            for (var p = 0; p < sheets; p++) {
                html += '<table class="label-table">';
                for (var r = 0; r < 6; r++) {
                    html += '<tr>';
                    for (var c = 0; c < 3; c++) {
                        var num = start + idx;
                        var barcodeVal = prefix ? (prefix + '-' + padNum(num)) : ('Box.' + num);
                        var etdDisplay = getEtdDisplay();
                        html += '<td><div class="cell-inner">' +
                            '<div class="lbl-anw">ANW</div>' +
                            '<div class="lbl-fields">' +
                                '<div class="lbl-field"><div class="lbl-field-header">WEIGHT</div><div class="lbl-field-blank"></div></div>' +
                                '<div class="lbl-field"><div class="lbl-field-header">' + (etdDisplay ? 'ETD' : 'D/M/Y') + '</div>' + (etdDisplay ? '<div class="lbl-field-value">' + etdDisplay + '</div>' : '<div class="lbl-field-blank"></div>') + '</div>' +
                            '</div>' +
                            '<svg class="lbl-barcode" id="bc-' + idx + '"></svg>' +
                            '<div class="lbl-number"><span class="lbl-prefix">Box.</span>' + num + '</div>' +
                            '</div></td>';
                        idx++;
                    }
                    html += '</tr>';
                }
                html += '</table>';
            }
            container.innerHTML = html;

            // Generate barcodes in batches
            var batchSize = 100;
            var processed = 0;

            function processBatch() {
                var end = Math.min(processed + batchSize, total);
                for (var j = processed; j < end; j++) {
                    var n = start + j;
                    var barcodeVal = prefix ? (prefix + '-' + padNum(n)) : ('Box.' + n);
                    try {
                        JsBarcode('#bc-' + j, barcodeVal, {
                            format: 'CODE128',
                            width: 1.6,
                            height: 28,
                            displayValue: false,
                            margin: 0,
                            background: 'transparent'
                        });
                    } catch(e) {}
                }
                processed = end;
                var pct = Math.round((processed / total) * 100);
                document.getElementById('loadingProgress').textContent = pct + '%';

                if (processed < total) {
                    setTimeout(processBatch, 0);
                } else {
                    // Done
                    container.classList.add('generated');
                    overlay.classList.remove('active');
                    document.getElementById('btnGenerate').disabled = false;
                    document.getElementById('btnPrint').disabled = false;
                    document.getElementById('previewNote').classList.add('active');
                    container.scrollIntoView({ behavior: 'smooth' });
                }
            }

            processBatch();
        }, 50);
    };

    window.doPrint = function() {
        var start = parseInt(startInput.value) || 1;
        var sheets = parseInt(sheetInput.value) || 1;
        var total = sheets * LABELS_PER_PAGE;
        var nextStart = start + total;
        var prefix = getPrefix();

        // Set PDF filename
        var end = start + total - 1;
        var oldTitle = document.title;
        var etdDots = getEtdDisplay().replace(/\//g, '.');
        document.title = etdDots ? ('ETD ' + etdDots + ' สติ๊กเกอร์กล่อง ' + start + '-' + end) : ('สติ๊กเกอร์กล่อง ' + start + '-' + end);

        // Save next start number to server (per ETD round)
        var apiBase = (window.location.pathname.indexOf('/skjtrack') !== -1) ? '/skjtrack' : '';
        var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        var selectedEtd = getEtd();

        fetch(apiBase + '/qr-scan/print-labels/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ next_start: nextStart, etd: selectedEtd })
        }).then(function(r) { return r.json(); })
        .then(function(data) {
            window.print();
            document.title = oldTitle;
        }).catch(function(err) {
            console.warn('Save counter failed:', err);
            window.print();
            document.title = oldTitle;
        });
    };

    // Init
    updateSummary();
})();
</script>

</body>
</html>
