<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code — รอบ {{ $etd }} ({{ $parcels->count() }} รายการ)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #fff; }

        .no-print {
            padding: 16px 24px;
            background: #f1f5f9;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .no-print button {
            padding: 10px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
        }
        .btn-print { background: #2563eb; color: #fff; }
        .btn-back { background: #e2e8f0; color: #334155; }

        .label-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 16px;
            padding: 20px;
        }

        .qr-label {
            width: 260px;
            border: 2px solid #000;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            page-break-inside: avoid;
        }
        .qr-label .company {
            font-size: 10px;
            font-weight: 700;
            color: #1e40af;
            margin-bottom: 2px;
        }
        .qr-label .box-title {
            font-size: 16px;
            font-weight: 900;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
            background: #000;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
        }
        .qr-label canvas {
            display: block;
            margin: 8px auto;
        }
        .qr-label .info {
            font-size: 11px;
            color: #333;
            line-height: 1.6;
            text-align: left;
            padding: 0 4px;
        }
        .qr-label .info strong { color: #000; }
        .qr-label .scan-text {
            font-size: 9px;
            color: #999;
            margin-top: 6px;
            border-top: 1px dashed #ccc;
            padding-top: 4px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .label-container { padding: 5px; gap: 8px; }
            .qr-label { border: 2px solid #000; }
        }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ ปริ้น QR Code ({{ $parcels->count() }} รายการ)</button>
    <button class="btn-back" onclick="history.back()">← ย้อนกลับ</button>
    <span style="font-size:13px;color:#64748b;">รอบปิดตู้: {{ $etd }} — {{ $parcels->count() }} รายการ</span>
</div>

<div class="label-container">
    @foreach($parcels as $i => $parcel)
    <div class="qr-label">
        <div class="company">SKJ JAPAN SHIPPING</div>
        <div class="box-title">{{ $parcel->box_no }}</div>
        <canvas id="qr{{ $i }}"></canvas>
        <div class="info">
            <strong>ลูกค้า:</strong> {{ $parcel->customerno }}<br>
            <strong>เลขพัสดุ:</strong> {{ $parcel->track_no }}<br>
            @if($parcel->weight)<strong>น้ำหนัก:</strong> {{ number_format($parcel->weight, 2) }} kg<br>@endif
            <strong>รอบ:</strong> {{ $parcel->etd ? $parcel->etd->format('d/m/Y') : '-' }}
        </div>
        <div class="scan-text">สแกน QR เพื่อตรวจสอบสถานะ</div>
    </div>
    @endforeach
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var baseUrl = {!! json_encode(url('/qr-scan/result')) !!};
        @foreach($parcels as $i => $parcel)
        QRCode.toCanvas(document.getElementById('qr{{ $i }}'), baseUrl + '/{{ urlencode($parcel->box_no) }}', {
            width: 160, margin: 2,
            color: { dark: '#000000', light: '#ffffff' }
        });
        @endforeach
    });
</script>

</body>
</html>
