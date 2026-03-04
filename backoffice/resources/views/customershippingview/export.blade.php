<html>
<style>

    table, th, td {
        border: 1px solid black;
        border-collapse: collapse;
        padding: 5px;
    }

    td {
        text-align: center;
    }


    th {  /* Adjust header row styles (optional) */
        background-color: #f2f2f2;  /* Light gray background for headers */
        font-weight: bold;        /* Bold text for headers */
    }

    /* image center */
    .center-image img {
        display: block;
        margin: 0 auto;
        max-width: 100%;
    }
</style>
<table>
    <thead>
    <tr>
        <th>วันที่</th>
        <th>รูปหน้ากล่อง</th>
        <th>รหัสลูกค้า</th>
        <th>เลขพัสดุ</th>
        <th>COD</th>
        <th>น้ำหนัก</th>
        <th>หน่วยละ</th>
        <th>ค่านำเข้า</th>
        <th>รูปสินค้า</th>
        <th>เลขกล่อง</th>
        <th>โกดัง</th>
        <th>วันที่ปิดตู้</th>
        <th>สถานะ</th>
        <th>ที่อยู่จัดส่งในไทย</th>
        <th>หมายเหตุ</th>

    </tr>
    </thead>
    <tbody>
    @foreach($customershippings as $customershipping)
        <tr>
            <td>{{ $customershipping->ship_date?\Carbon\Carbon::parse($customershipping->ship_date)->format('d/m/Y'):''}}</td>
            <td class="center-image">@if(!empty($customershipping->box_image) && $customershipping->box_image != '-')
                <a href="{{ $customershipping->box_image }}" target="_blank">
                   box-{{$customershipping->box_no}}
                </a>
                @else
                    <span>-</span>
                @endif
            </td>
            <td>{{ $customershipping->customerno }}</td>
            <td>{{ $customershipping->track_no }}</td>
            <td>{{ $customershipping->cod }}</td>
            <td>{{ $customershipping->weight }}</td>
            <td>{{ $customershipping->unit_price }}</td>
            <td>{{ $customershipping->import_cost }}</td>
            <td class="center-image">  @if(!empty($customershipping->product_image) && $customershipping->product_image != '-')
{{--                    @if(file_exists(public_path($customershipping->product_image)))--}}
                        <img src="{{ $customershipping->product_image }}" width="75" height="75" alt="">
{{--                    @else--}}
{{--                        <span>-</span> <!-- หรืออื่น ๆ ตามต้องการ -->--}}
{{--                    @endif--}}
                @else
                    <span>-</span>
                @endif
            </td>
            <td>{{ $customershipping->box_no }}</td>
            <td>{{ $customershipping->warehouse }}</td>
            <td>{{ $customershipping->etd?\Carbon\Carbon::parse($customershipping->etd)->format('d/m/Y'):''}}</td>
            <td>{{ \App\Models\ShippingStatus::getNameById($customershipping->status) }}</td>
            <td>
            @if($customershipping->delivery_type_id==1)
                รับเอง
                @else
                    @if(!empty($customershipping->delivery_mobile)) โทร:@endif {{ $customershipping->delivery_mobile }}
                    {{ $customershipping->delivery_fullname }}
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
