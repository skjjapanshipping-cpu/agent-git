<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>ใบแจ้งหนี้</title>
    <style>
        body {
            font-family: 'dbhelvethaicax';
            font-size: 10pt;
            line-height: 1.1;
        
           
        }
        .font-erasbolditc{
            font-family: 'erasbolditc';
        }

        
        
        h1, h2, h3, h4, h5, h6 {
            font-family: 'dbhelvethaicax';
            font-weight: bold;
        }

        .header-table {
            width: 100%;
            /* margin-bottom: 10px; */
            border-collapse: collapse;
        }
        
        .header-table td {
            vertical-align: top;
            padding: 5px;
           
        }
        
        .logo-cell {
            width: 150px;
        }
        
        .company-info-cell {
            width: 50%;
            font-size: 12pt;
        }
        
        .company-info-cell h2 {
            font-size: 22pt;
            margin-bottom: 5px;
            font-weight: bold;
        }
        
       
        
        .invoice-details-cell {
            width: 25%;
            text-align: right;
            font-size: 14pt;
        }
        
        .invoice-details-cell p {
            margin: 3px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        
        th, td {
            /* border: 1px solid #000; */
            padding: 8px;
            font-size: 14pt;
        }
     
        th {
            background-color: #000; 
            color: #fff;
            font-weight: bold;
        }
        .total {
            text-align: right;
            font-size: 16pt;
            font-weight: bold;
        }
        .signature {
            margin-top: 15px;
            /* position: relative; */
            height: 150px;
            /* page-break-inside: avoid; */
            break-inside: avoid;
            width: 100%;
        }
        .signature p {
            font-size: 16pt;
            margin: 5px 0;
        }
        .clear { clear: both; }

        .customer-info {
            margin: 20px 0;
            font-size: 14pt;
        }
        .customer-info p {
            margin: 5px 0;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .border-top {
            border-top: 2px solid black !important;
        }
        .border-bottom {
            border-bottom: 2px solid black !important;
        }
  
        .bg-gray {
            background-color: #f0f0f0;
        }

        .bg-black {
            background-color: #000;
        }

        .text-white {
            color: #fff;
        }

        .text-red {
            color: red;
        }
        
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ public_path('img/logopdf.png') }}" style="width: 141px; height: 114px; object-fit: contain;">
            </td>
            <td class="company-info-cell">
                {{-- <h2 >SKJ.JAPAN SHIPPING COMPANY</h2> --}}
                {{-- <img src="{{ public_path('img/skjheadpdf_.png') }}" style="width:320px;"> --}}
                <p class="font-erasbolditc" style="font-size: 18pt; margin-bottom: 5px; font-weight: bold;">SKJ.JAPAN SHIPPING</p>
                <p style="margin-left: 20px !important;">36/1 หมู่ 3 ตำบลมหาสวัสดิ์ อำเภอบางกรวย จังหวัดนนทบุรี 11130</p>
                <p style="margin-left: 20px !important;">โทรศัพท์: (082)460-9940, (086)362-1048</p>
                <p style="margin-left: 20px !important;">E-mail: SKJ.JAPANShipping@gmail.com</p>
            </td>
            <td class="invoice-details-cell">
                <p>ใบแจ้งหนี้</p>
                <p>ต้นฉบับ</p>
                <br>
                <p>วันที่: {{ date('d/m/Y') }}</p>
                <p>เลขที่: {{ strtoupper($customerno) }}-{{ date('dmY', strtotime($etd_Original)) }}</p>
            </td>
        </tr>
    </table>
    {{-- <div class="clear"></div> --}}

    <div class="customer-info">
        <p>ลูกค้า / Customer: {{ $customer->name??'' }}</p>
        <p>ที่อยู่ / Address: {{ $customer->addr ??'' }}
            {{ $customer->subdistrinct ??'' }}
            {{ $customer->distrinct ??'' }}
            {{ $customer->province ??'' }}
            {{ $customer->postcode ??'' }}</p>
        <p>โทร / Mobile Phone: {{ $customer->mobile ??'' }}</p>
    </div>

    <table>
        <thead >
            <tr>
                <th width="7%" style="white-space: nowrap;">ลำดับ</th>
                <th width="38%">รายละเอียด / เลขพัสดุ</th>
                <th width="13%">น้ำหนัก / กก.</th>
                <th width="13%">หน่วยละ</th>
                <th width="13%">COD</th>
                <th width="16%">จำนวนเงิน (บาท)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                @php
                    $firstMethod = $shippings->first()->shipping_method ?? 1;
                    $methodLabel = $firstMethod == 2 ? 'ทางอากาศ ✈️' : 'ทางเรือ 🚢';
                    $etdLabel = $firstMethod == 2 ? 'รอบเที่ยวบิน' : 'รอบปิดตู้';
                @endphp
                <td colspan="6">ค่าขนส่งพัสดุ{{ $methodLabel }} จากประเทศญี่ปุ่นมาประเทศไทย ({{ $etdLabel }} {{ date('d/m/y', strtotime($etd_Original)) }})</td>
         
            </tr>
            @php 
                $total = 0; 
                $totalWeight = 0; 
            @endphp
            @foreach($shippings as $index => $shipping)
            @php 
                // ใช้ cod_rate ที่เก็บไว้ในแต่ละ record (ข้อมูลเก่าใช้ rate เดิม)
                $codRate = $shipping->cod_rate ?? 0.25;
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>Tracking No.{{ $shipping->track_no }} (Box.{{ $shipping->box_no }})</td>
                <td class="text-right">{{ $shipping->iswholeprice===1 ? 'เหมา' : number_format($shipping->weight, 2) }}</td>
                <td class="text-right">{{ $shipping->iswholeprice===1 ? 'เหมา' : number_format($shipping->unit_price, 2) }}</td>
                <td class="text-right">{{ $shipping->cod ? number_format($shipping->cod*$codRate, 2) : '-' }}</td>
                {{-- <td>{{ number_format($shipping->weight * $shipping->unit_price, 2) }}</td> --}}
                <td class="text-right">{{ number_format($shipping->import_cost, 2) }}</td>
            </tr>
            @php 
                // $total += ($shipping->weight * $shipping->unit_price) + ($shipping->cod ?? 0);
                $total += $shipping->import_cost+ ($shipping->cod*$codRate ?? 0);
                $totalWeight += $shipping->weight;
            @endphp
        
            {{-- @if($shipping->note)
            <tr>
                <td colspan="6" style="text-align: left;">หมายเหตุ: {{ $shipping->note }}</td>
            </tr>
            @endif --}}
            @endforeach
        </tbody>
        <tfoot>
            <tr >
                @php
                    if (!function_exists('convertNumberToThaiBaht')) { function convertNumberToThaiBaht($number){
                        $number = number_format($number, 2, '.', '');
                        $numberStr = (string)$number;
                        list($baht, $satang) = explode('.', $numberStr);
                        $baht = (int)$baht;
                        $satang = (int)$satang;
                        
                        $thaiNum = array('ศูนย์','หนึ่ง','สอง','สาม','สี่','ห้า','หก','เจ็ด','แปด','เก้า');
                        $thaiUnit = array('','สิบ','ร้อย','พัน','หมื่น','แสน','ล้าน','สิบล้าน','ร้อยล้าน','พันล้าน','หมื่นล้าน','แสนล้าน','ล้านล้าน');
                        
                        $result = '';
                        if($baht > 0){
                            $bahtStr = (string)$baht;
                            $len = strlen($bahtStr);
                            
                            for($i=0; $i<$len; $i++){
                                $pos = $len-$i-1;
                                $num = (int)$bahtStr[$i];
                                
                                if($num != 0){
                                    if($pos % 6 == 1 && $num == 2) {
                                        $result .= 'ยี่';
                                    }
                                    elseif($pos % 6 == 1 && $num == 1) {
                                        $result .= '';
                                    }
                                    elseif($pos % 6 == 0 && $num == 1 && $i > 0) {
                                        $result .= 'เอ็ด';
                                    }
                                    else {
                                        $result .= isset($thaiNum[$num]) ? $thaiNum[$num] : '';
                                    }
                                    
                                    $result .= isset($thaiUnit[$pos]) ? $thaiUnit[$pos] : '';
                                }
                                
                                if($pos % 6 == 0 && $pos > 0) {
                                    $result .= 'ล้าน';
                                }
                            }
                            $result .= 'บาท';
                        }
                        
                        if($satang > 0){
                            if($baht == 0) {
                                $result = 'ศูนย์บาท';
                            }
                            
                            $satangStr = sprintf("%02d", $satang);
                            $satangSib = (int)$satangStr[0];
                            $satangNuai = (int)$satangStr[1];
                            
                            if($satangSib > 0) {
                                if($satangSib == 1) {
                                    $result .= 'สิบ';
                                } else if($satangSib == 2) {
                                    $result .= 'ยี่สิบ';
                                } else {
                                    $result .= $thaiNum[$satangSib].'สิบ';
                                }
                            }
                            
                            if($satangNuai > 0) {
                                if($satangNuai == 1 && $satangSib > 0) {
                                    $result .= 'เอ็ด';
                                } else {
                                    $result .= $thaiNum[$satangNuai];
                                }
                            }
                            
                            $result .= 'สตางค์';
                        } else {
                            $result .= 'ถ้วน';
                        }
                        
                        return $result;
                    }}
                @endphp
                <td colspan="4" style="font-size: 16pt;" class="text-center border-top border-bottom bg-black text-white">{{ convertNumberToThaiBaht($total) }}</td>
                <td  class="text-right border-top border-bottom">รวมเป็นเงินทั้งสิ้น</td>
                <td  class="text-right border-top border-bottom total text-red">{{ number_format($total, 2) }}</td>
            </tr>
           <tr>
                <td colspan="6">น้ำหนักรวม: {{ number_format($totalWeight, 2) }} kg</td>
           </tr>
        </tfoot>
    </table>



    <div class="signature">
        <div style="float: left; width: 53%; text-align: center;">
            <p style="line-height: 7.0;">ได้รับสินค้าตามรายการข้างต้นที่แจ้งเรียบร้อยแล้ว</p>
            <p>.................................... วันที่ ..../..../.....</p>
            <p>ผู้รับสินค้า</p>
        </div>
        <div style="float: right; width: 45%; text-align: center;">
            <p class="font-erasbolditc" style="font-size: 16pt; line-height: 7.0;">SKJ.JAPAN SHIPPING</p>
            <p>.................................... วันที่ ..../..../.....</p>
            <p>ผู้ออกบิล</p>
        </div>
    </div>
</body>
</html> 