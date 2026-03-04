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

    .red {
        color: red;
    }
</style>
<table>
    <thead>
    <tr>
        {{-- <th>วันที่</th> --}}
        <th>จำนวนชิ้น</th>
        {{-- <th>รูปหน้ากล่อง</th> --}}
        <th>รหัสลูกค้า</th>
        <th>เลขกล่อง</th>
        <th>เลขพัสดุ</th>
        <th>COD</th>
        <th>น้ำหนัก</th>
        {{-- <th>หน่วยละ</th> --}}
        {{-- <th>ค่านำเข้า</th> --}}
        {{-- <th>รูปสินค้า</th> --}}
        {{-- <th>โกดัง</th> --}}
        <th>วันที่ปิดตู้</th>
        {{-- <th>สถานะ</th> --}}
        {{-- <th>สถานะชำระเงิน</th> --}}
{{--        <th>สถานะเงิน</th>--}}
        <th>จัดส่ง</th>
        <th>หมายเหตุ</th>

    </tr>
    </thead>
    <tbody>
    @php
        $currentCustomer = '';
        $isFirstRow = true;
    @endphp
    @foreach($customershippings as $customershipping)
        <tr>
            {{-- <td>{{ $customershipping->ship_date?\Carbon\Carbon::parse($customershipping->ship_date)->format('d/m/Y'):''}}</td> --}}
                {{-- จำนวนชิ้น - แสดงเฉพาะแถวแรกของแต่ละรหัสลูกค้า --}}
            @if($customershipping->customerno !== $currentCustomer)
                <td>{{ $customershipping->box_count ?? 0 }}</td>
                @php
                    $currentCustomer = $customershipping->customerno;
                    $isFirstRow = true;
                @endphp
            @else
                <td></td>
                @php $isFirstRow = false; @endphp
            @endif
            {{-- รูปหน้ากล่อง --}}
            {{-- <td class="center-image">
             
                @if(!empty($customershipping->box_image) && $customershipping->box_image != '-')

                        <a href="{{ $customershipping->box_image }}" target="_blank">
                           box-{{$customershipping->box_no}}
                        </a>
                @endif
            </td> --}}
            {{-- รหัสลูกค้า --}}
            <td>{{ $customershipping->customerno }}</td>
            {{-- เลขกล่อง --}}
            <td class="red">{{ $customershipping->box_no }}</td>
            {{-- เลขพัสดุ --}}
            <td>{{ $customershipping->track_no }}</td>
            <td>{{ $customershipping->cod }}</td>
            <td>{{ $customershipping->weight }}</td>
            {{-- หน่วยละ --}}
            {{-- <td>{{ $customershipping->unit_price }}</td> --}}
            {{-- ค่านำเข้า --}}
            {{-- <td>{{ $customershipping->import_cost }}</td> --}}
            {{-- รูปสินค้า --}}
            {{-- <td class="center-image">   @if(!empty($customershipping->product_image) && $customershipping->product_image != '-')
                        <img src="{{ $customershipping->product_image }}" width="75" height="75" alt="">

                @else
                    <span>-</span>
                @endif
            </td> --}}
           
            {{-- โกดัง --}}
            {{-- <td>{{ $customershipping->warehouse }}</td> --}}
            {{-- วันที่ปิดตู้ --}}
            <td>{{ $customershipping->etd?\Carbon\Carbon::parse($customershipping->etd)->format('d/m/Y'):''}}</td>
            {{-- สถานะ --}}
            {{-- <td>{{ \App\Models\ShippingStatus::getNameById($customershipping->status) }}</td> --}}
            {{-- สถานะชำระเงิน --}}
            {{-- <td>{{ \App\Models\PayStatus::getNameById($customershipping->pay_status) }}</td> --}}
            <td>@if($customershipping->delivery_type_id==1)
                รับเอง
                @elseif($customershipping->delivery_type_id==2)
                    ที่อยู่ปัจจุบัน
                @elseif($customershipping->delivery_type_id==3)
                    เพิ่มที่อยู่เอง
                @else
                    -
                @endif
            </td>
                    
           {{-- <td >
         
               {{ $customershipping->delivery_adress }}
               @if($customershipping->delivery_province == 'กรุงเทพมหานคร')
                   แขวง{{ $customershipping->delivery_subdistrict }} เขต{{ $customershipping->delivery_district }}
               @elseif(!empty($customershipping->delivery_province))
                    ต.{{ $customershipping->delivery_subdistrict }} อ.{{ $customershipping->delivery_district }}
               @endif
               {{ $customershipping->delivery_province }} {{ $customershipping->delivery_postcode }}</td> --}}
           <td>{{ $customershipping->note }}</td>

        </tr>
    @endforeach
    </tbody>
</table>
</html>
