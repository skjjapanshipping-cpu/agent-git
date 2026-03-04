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

    th {
        background-color: #f2f2f2;
        font-weight: bold;
    }

    /* image center */
    .center-image img {
        display: block;
        margin: 0 auto;
        max-width: 100%;
    }
    
    .title-row {
        text-align: left;
        font-weight: bold;
        color: #FF0000;
        border: none;
    }
</style>
<table>
    <tr>
        <td colspan="17" class="title-row" style="border: none; text-align: left; font-weight: bold; color: #FF0000; font-size: 16px;">
            @php
                // หาวันที่น้อยที่สุดและมากที่สุดจากข้อมูล
                $minDate = $customerorders->min('order_date');
                $maxDate = $customerorders->max('order_date');
                
                $startDateFormatted = !empty($minDate) ? \Carbon\Carbon::parse($minDate)->format('d/m/Y H:i') : \Carbon\Carbon::now()->format('d/m/Y H:i');
                $endDateFormatted = !empty($maxDate) ? \Carbon\Carbon::parse($maxDate)->format('d/m/Y H:i') : \Carbon\Carbon::now()->format('d/m/Y H:i');
            @endphp
            สรุปรายการสั่งสินค้า ระหว่างวันที่ {{ $startDateFormatted }} ถึง วันที่ {{ $endDateFormatted }}
        </td>
    </tr>
    <thead>
    <tr>
        <th>No</th>
        <th>วันที่</th>
        <th>รหัสลูกค้า</th>
        <th>รูปภาพ</th>
        <th>URL</th>
        <th>จำนวน</th>
        <th>เงินเยน</th>
        <th>เรท</th>
        <th>เงินบาท</th>
        <th>Buyer Status</th>
        <th>เลขพัสดุ</th>
        <th>รอบปิดตู้</th>
        <th>สถานะขนส่ง</th>
        <th>หมายเหตุ</th>
        <th>Note Admin</th>
        <th>Items2</th>
        <th>Boss</th>
    </tr>
    </thead>
    <tbody>
    @php
        $rowNumber = 1; // ตัวนับลำดับ
        // ฟังก์ชันดึงชื่อ domain จาก URL (ประกาศนอก loop เพื่อไม่ให้เกิดการ redeclare)
        if (!function_exists('getNameFromDomain')) {
            function getNameFromDomain($urlData) {
                try {
                    $url = parse_url($urlData);
                    $hostname = $url['host'] ?? '';
                    
                    if (empty($hostname)) {
                        return $urlData;
                    }
                    
                    $parts = explode('.', $hostname);
                    $numParts = count($parts);
                    
                    // Define common TLDs and SLDs
                    $commonTLDs = ['jp', 'co', 'com', 'net', 'org', 'gov', 'edu', 'th', 'co.th'];
                    $commonSLDs = ['co', 'ac', 'ne', 'or', 'com', 'net', 'org', 'edu', 'th'];
                    
                    $domainName = '';
                    
                    if ($numParts > 2) {
                        $tld = $parts[$numParts - 1];
                        $sld = $parts[$numParts - 2];
                        $secondLastPart = $parts[$numParts - 3];
                        
                        if (in_array($tld, $commonTLDs)) {
                            if (in_array($sld, $commonSLDs)) {
                                // For cases like "example.co.jp" or "example.ac.jp"
                                $domainName = $secondLastPart;
                            } else {
                                // For domains like "example.jp" or "example.com"
                                $domainName = $sld;
                            }
                        } else {
                            // Handle other complex domains
                            $domainName = $sld;
                        }
                    } else if ($numParts === 2) {
                        // Handle simpler domains with only two parts
                        $domainName = $parts[0];
                    } else {
                        // Fallback to the full hostname
                        $domainName = $hostname;
                    }
                    
                    // Remove "www" prefix if present
                    if (strpos($domainName, 'www.') === 0) {
                        $domainName = substr($domainName, 4);
                    }
                    
                    // Capitalize first letter
                    $domainName = ucfirst($domainName);
                    
                    return $domainName;
                } catch (Exception $e) {
                    return $urlData;
                }
            }
        }
    @endphp
    @foreach($customerorders as $customerorder)
        <tr>
            <td>{{ $rowNumber++ }}</td>
            <td>{{ $customerorder->order_date ? \Carbon\Carbon::parse($customerorder->order_date)->format('d/m/Y H:i') : '' }}</td>
            <td>{{ $customerorder->customerno }}{{ $customerorder->itemno ? '-' . $customerorder->itemno : '' }}</td>
            <td class="center-image">
                @if(isset($include_image) && $include_image)
                    @if(!empty($customerorder->image_link) && $customerorder->image_link != '-')
                        @php
                            // ใช้วิธีเดียวกับ customershippingview - ใช้ relative path 'uploads/filename.jpg'
                            // PhpSpreadsheet จะแปลงเป็น absolute path อัตโนมัติ
                            $imagePath = 'uploads/' . $customerorder->image_link;
                        @endphp
                        <img src="{{ $imagePath }}" width="75" height="75" alt="">
                    @else
                        <span>-</span>
                    @endif
                @else
                    <span>-</span>
                @endif
            </td>
            <td>
                @if(!empty($customerorder->link))
                    @php
                        $domainName = getNameFromDomain($customerorder->link);
                    @endphp
                    <a href="{{ $customerorder->link }}" target="_blank">{{ $domainName }}</a>
                @else
                    <span>-</span>
                @endif
            </td>
            <td>{{ $customerorder->quantity ?? '-' }}</td>
            <td>{{ $customerorder->product_cost_yen ?? '-' }}</td>
            <td>{{ $customerorder->rateprice ?? '-' }}</td>
            <td>{{ $customerorder->product_cost_baht ?? '-' }}</td>
            <td>{{ \App\Models\SupplierStatus::getNameById($customerorder->supplier_status_id) ?? '-' }}</td>
            <td>{{ $customerorder->tracking_number ?? '-' }}</td>
            <td>{{ $customerorder->cutoff_date ? \Carbon\Carbon::parse($customerorder->cutoff_date)->format('d/m/Y') : '-' }}</td>
            <td>{{ \App\Models\ShippingStatus::getNameById($customerorder->shipping_status) }}</td>
            <td>{{ $customerorder->note ?? '-' }}</td>
            <td>{{ $customerorder->note_admin ?? '-' }}</td>
            <td>{{ $customerorder->itemno2 ?? '-' }}</td>
            <td>{{ \App\Models\Boss::getNameById($customerorder->boss_id) ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
</html>

