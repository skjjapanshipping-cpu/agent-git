<html>
<style>
    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        padding: 5px;
    }
    td { text-align: center; }
    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }
</style>
<table>
    <thead>
    <tr>
        <th>NO</th>
        <th>การจัดส่ง</th>
        <th>วันที่</th>
        <th>รูปหน้ากล่อง</th>
        <th>เลขพัสดุ</th>
        <th>COD</th>
        <th>น้ำหนัก</th>
        <th>ค่านำเข้า</th>
        <th>รูปสินค้า</th>
        <th>เลขกล่อง</th>
        <th>วันที่ใส่ตู้</th>
        <th>ประเภท</th>
        <th>สถานะ</th>
        <th>ที่อยู่จัดส่ง</th>
        <th>หมายเหตุ</th>
    </tr>
    </thead>
    <tbody>
    @foreach($customershippings as $index => $customershipping)
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ \App\Models\DeliveryType::getNameById($customershipping->delivery_type_id) }}</td>
            <td>{{ $customershipping->ship_date ? \Carbon\Carbon::parse($customershipping->ship_date)->format('d/m/Y') : '' }}</td>
            <td>@if(!empty($customershipping->box_image) && $customershipping->box_image != '-')<a href="{{ $customershipping->box_image }}">box-{{ $customershipping->box_no }}</a>@else - @endif</td>
            <td>{{ $customershipping->track_no }}</td>
            <td>{{ $customershipping->cod }}</td>
            <td>{{ $customershipping->weight }}</td>
            <td>{{ $customershipping->import_cost }}</td>
            <td>@if(!empty($customershipping->product_image) && $customershipping->product_image != '-')<a href="{{ $customershipping->product_image }}">ดูรูป</a>@else - @endif</td>
            <td>{{ $customershipping->box_no }}</td>
            <td>{{ $customershipping->etd ? \Carbon\Carbon::parse($customershipping->etd)->format('d/m/Y') : '' }}</td>
            <td>{{ ($customershipping->shipping_method ?? 1) == 2 ? 'เครื่องบิน' : 'เรือ' }}</td>
            <td>{{ \App\Models\ShippingStatus::getNameById($customershipping->status) }}</td>
            <td style="text-align:left;">
            @if($customershipping->delivery_type_id==1)
                รับเอง @if(!empty($customershipping->delivery_fullname))- {{ $customershipping->delivery_fullname }}@endif
            @else
                {{ $customershipping->delivery_fullname }}
                @if(!empty($customershipping->delivery_mobile)) โทร:{{ $customershipping->delivery_mobile }}@endif
                {{ $customershipping->delivery_address }}
                @if($customershipping->delivery_province == 'กรุงเทพมหานคร')
                    แขวง{{ $customershipping->delivery_subdistrict }} เขต{{ $customershipping->delivery_district }}
                @elseif(!empty($customershipping->delivery_province))
                    ต.{{ $customershipping->delivery_subdistrict }} อ.{{ $customershipping->delivery_district }}
                @endif
                {{ $customershipping->delivery_province }} {{ $customershipping->delivery_postcode }}
            @endif
            </td>
            <td>{{ $customershipping->note }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</html>
