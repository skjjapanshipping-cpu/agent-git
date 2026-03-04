@extends('layouts.app')

@section('template_title')
    QR Scan - {{ $box_no }}
@endsection

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-6 offset-md-3">
            <div class="card" style="border-radius:12px;overflow:hidden;">
                <div class="card-header" style="background:linear-gradient(135deg,#1e40af,#3b82f6);color:#fff;padding:20px 24px;">
                    <h4 style="margin:0;font-weight:700;">📦 {{ $box_no }}</h4>
                    <p style="margin:4px 0 0;opacity:0.85;font-size:14px;">ข้อมูลพัสดุ</p>
                </div>
                <div class="card-body" style="padding:24px;">

                    @if(!$parcel)
                        <div class="alert alert-warning" style="border-radius:8px;">ไม่พบพัสดุกล่อง {{ $box_no }}</div>
                    @else
                        <table class="table" style="font-size:14px;margin-bottom:0;">
                            <tr><td style="width:120px;font-weight:600;color:#64748b;">เลขกล่อง</td><td style="font-weight:700;font-size:16px;">{{ $parcel->box_no }}</td></tr>
                            <tr><td style="font-weight:600;color:#64748b;">รหัสลูกค้า</td><td><strong>{{ $parcel->customerno }}</strong></td></tr>
                            <tr><td style="font-weight:600;color:#64748b;">เลขพัสดุ</td><td>{{ $parcel->track_no }}</td></tr>
                            <tr><td style="font-weight:600;color:#64748b;">น้ำหนัก</td><td>{{ $parcel->weight ? number_format($parcel->weight, 2) . ' kg' : '-' }}</td></tr>
                            <tr><td style="font-weight:600;color:#64748b;">รอบปิดตู้</td><td>{{ $parcel->etd ? $parcel->etd->format('d/m/Y') : '-' }}</td></tr>
                            <tr>
                                <td style="font-weight:600;color:#64748b;">สถานะ</td>
                                <td>
                                    <span id="currentStatus" class="badge" style="background:#e2e8f0;color:#334155;padding:6px 14px;border-radius:8px;font-size:13px;font-weight:600;">
                                        {{ \App\Models\Customershipping::getShippingStatusNameById($parcel->status)->name }}
                                    </span>
                                </td>
                            </tr>
                            @if($parcel->note)
                            <tr><td style="font-weight:600;color:#64748b;">หมายเหตุ</td><td>{{ $parcel->note }}</td></tr>
                            @endif
                        </table>

                        <hr style="margin:16px 0;">

                        <div style="background:#f8fafc;border-radius:10px;padding:16px;">
                            <h5 style="margin:0 0 10px;font-weight:700;font-size:14px;">⚡ อัพเดตสถานะ</h5>
                            <div class="form-group" style="margin-bottom:10px;">
                                <select id="newStatus" class="form-control" style="max-width:280px;">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status->id }}" {{ $parcel->status == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-warning" id="btnUpdate" onclick="updateStatus()" style="font-weight:700;">
                                ✅ อัพเดตสถานะ
                            </button>
                            <span id="updateResult" style="margin-left:10px;font-size:13px;"></span>
                        </div>
                    @endif

                    <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
                        <a href="{{ url('/qr-scan/print/' . urlencode($box_no)) }}" class="btn btn-info" style="font-weight:600;">
                            🖨️ ปริ้น QR
                        </a>
                        <a href="{{ url('/qr-scan/scanner') }}" class="btn btn-default" style="font-weight:600;">
                            📸 สแกนกล่องถัดไป
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
function updateStatus() {
    var btn = document.getElementById('btnUpdate');
    var status = document.getElementById('newStatus').value;
    var result = document.getElementById('updateResult');
    btn.disabled = true;
    btn.innerHTML = '⏳ กำลังอัพเดต...';
    result.innerHTML = '';

    var xhr = new XMLHttpRequest();
    var url = window.location.hostname === 'localhost' ? '/qr-scan/api/update-status' : '/skjtrack/qr-scan/api/update-status';
    xhr.open('POST', url, true);
    xhr.setRequestHeader('Content-Type', 'application/json');
    xhr.setRequestHeader('X-CSRF-TOKEN', document.querySelector('meta[name=csrf-token]').content);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            btn.disabled = false;
            btn.innerHTML = '✅ อัพเดตสถานะ';
            try {
                var data = JSON.parse(xhr.responseText);
                if (data.success) {
                    result.innerHTML = '<span style="color:#059669;font-weight:600;">' + data.message + '</span>';
                    document.getElementById('currentStatus').textContent = data.status_name;
                    document.getElementById('currentStatus').style.background = '#059669';
                    document.getElementById('currentStatus').style.color = '#fff';
                } else {
                    result.innerHTML = '<span style="color:#dc2626;">' + data.message + '</span>';
                }
            } catch(e) {
                result.innerHTML = '<span style="color:#dc2626;">เกิดข้อผิดพลาด</span>';
            }
        }
    };
    xhr.send(JSON.stringify({ box_no: '{{ $box_no }}', status: status }));
}
</script>
