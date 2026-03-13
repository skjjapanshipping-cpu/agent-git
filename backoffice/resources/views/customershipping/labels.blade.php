<style>
    @page {
        sheet-size: 175mm 212mm;
        margin: 0mm;
    }
    body {
        font-family: dbhelvethaicax, sans-serif;
        margin: 0;
        padding: 0;
    }
    .page-container {
        position: relative;
        width: 175mm;
        height: 212mm;
    }
    .label-box {
        position: absolute;
        width: 80mm;
        height: 50mm;
        text-align: center;
        overflow: hidden;
    }
    .label-inner {
        position: relative;
        top: 50%;
        transform: translateY(-50%);
    }
    .label-qty {
        font-size: 14pt;
        color: #0099cc;
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }
    .label-customer {
        font-size: 36pt;
        font-weight: bold;
        color: #ff0000;
        margin: 0;
        padding: 0;
        line-height: 1.2;
    }
    .label-etd {
        font-size: 11pt;
        color: #555;
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }
    .label-status {
        font-size: 11pt;
        color: #555;
        font-style: italic;
        margin: 0;
        padding: 0;
        line-height: 1.4;
    }
</style>

@php
    // Exact positions from A15 template (mm)
    $colX = [6, 89]; // left edge of col 1 and col 2
    $rowY = [2, 55, 108, 161]; // top edge of each row
    $chunks = array_chunk($labels, 8);
@endphp

@foreach($chunks as $pageIndex => $page)
    @if($pageIndex > 0)
        <pagebreak />
    @endif
    <div class="page-container">
        @for($row = 0; $row < 4; $row++)
            @for($col = 0; $col < 2; $col++)
                @php $idx = $row * 2 + $col; @endphp
                @if(isset($page[$idx]))
                    <div class="label-box" style="left: {{ $colX[$col] }}mm; top: {{ $rowY[$row] }}mm;">
                        <div style="padding-top: 6mm;">
                            <div class="label-qty">จำนวน {{ $page[$idx]['qty'] }} ชิ้น</div>
                            <div class="label-customer">{{ $page[$idx]['customerno'] }}</div>
                            <div class="label-etd">รอบปิดตู้: {{ $page[$idx]['etd'] }}</div>
                            <div class="label-status">สถานะ: {{ $page[$idx]['delivery_type'] }}</div>
                        </div>
                    </div>
                @endif
            @endfor
        @endfor
    </div>
@endforeach
