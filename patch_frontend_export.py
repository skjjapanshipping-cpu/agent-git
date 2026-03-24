# -*- coding: utf-8 -*-
import sys

errors = []

# ==============================
# PATCH 1: Frontend getRecipients - sort A-Z
# ==============================
f1 = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomerShippingViewController.php'
with open(f1, 'r') as fh:
    c1 = fh.read()

old1 = "->orderByRaw('cnt DESC')"
new1 = "->orderByRaw('recipient_name ASC')"

if old1 not in c1:
    errors.append('VIEW CTRL: cnt DESC not found')
else:
    c1 = c1.replace(old1, new1, 1)
    with open(f1, 'w') as fh:
        fh.write(c1)
    print('1. Frontend recipient dropdown sorted A-Z')

# ==============================
# PATCH 2: Export view - separate columns + sort A-Z
# ==============================
f2 = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/export.blade.php'
with open(f2, 'r') as fh:
    c2 = fh.read()

# Replace the entire export view with fixed column layout
new_export = '''<html>
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
        <th>ชื่อ-นามสกุล</th>
        <th>เบอร์โทร</th>
        <th>ที่อยู่จัดส่งในไทย</th>
        <th>หมายเหตุ</th>

    </tr>
    </thead>
    <tbody>
    @foreach($customershippings as $customershipping)
        <tr>
            <td>{{ $customershipping->ship_date?\\Carbon\\Carbon::parse($customershipping->ship_date)->format(\'d/m/Y\'):\'\'}}</td>
            <td class="center-image">@if(!empty($customershipping->box_image) && $customershipping->box_image != \'-\')
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
            <td class="center-image">  @if(!empty($customershipping->product_image) && $customershipping->product_image != \'-\')
                        <img src="{{ $customershipping->product_image }}" width="75" height="75" alt="">
                @else
                    <span>-</span>
                @endif
            </td>
            <td>{{ $customershipping->box_no }}</td>
            <td>{{ $customershipping->warehouse }}</td>
            <td>{{ $customershipping->etd?\\Carbon\\Carbon::parse($customershipping->etd)->format(\'d/m/Y\'):\'\'}}</td>
            <td>{{ \\App\\Models\\ShippingStatus::getNameById($customershipping->status) }}</td>
            <td>
            @if($customershipping->delivery_type_id==1)
                รับเอง{{ !empty($customershipping->delivery_fullname) ? \' (\'.$customershipping->delivery_fullname.\')\' : \'\' }}
            @else
                {{ $customershipping->delivery_fullname }}
            @endif
            </td>
            <td>
            @if($customershipping->delivery_type_id!=1)
                {{ $customershipping->delivery_mobile }}
            @endif
            </td>
            <td>
            @if($customershipping->delivery_type_id==1)
                รับเอง
            @else
                {{ $customershipping->delivery_address }}
                @if($customershipping->delivery_province == \'กรุงเทพมหานคร\')
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
</html>'''

with open(f2, 'w') as fh:
    fh.write(new_export)
print('2. Export view: separated name/phone/address columns')

# ==============================
# PATCH 3: Export class - update column widths for new columns + sort A-Z
# ==============================
f3 = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Exports/CustomershippigviewHtmlExport.php'
with open(f3, 'r') as fh:
    c3 = fh.read()

# Fix sort order - sort by delivery_fullname A-Z
old_sort = "->orderByRaw('customerno asc')->take(1000);"
new_sort = "->orderByRaw('delivery_fullname ASC, customerno ASC, ship_date DESC')->take(1000);"

if old_sort not in c3:
    errors.append('EXPORT CLASS: orderByRaw not found')
else:
    c3 = c3.replace(old_sort, new_sort, 1)

# Update column widths (now 16 columns A-Q instead of A-O)
old_widths = """    public function columnWidths(): array
    {
        return [
            'A' => 11,
            'B' => 16,
            'C' => 10,
            'D' => 15,
            'E' => 8,
            'F' => 8,
            'G' => 8,
            'H' => 8,
            'I' => 16,
            'J' => 8,
            'K' => 8,
            'L' => 11,
            'M' => 15,
            'N' => 27,
            'O' => 27
        ];
    }"""

new_widths = """    public function columnWidths(): array
    {
        return [
            'A' => 11,
            'B' => 16,
            'C' => 10,
            'D' => 15,
            'E' => 8,
            'F' => 8,
            'G' => 8,
            'H' => 8,
            'I' => 16,
            'J' => 8,
            'K' => 8,
            'L' => 11,
            'M' => 15,
            'N' => 20,
            'O' => 14,
            'P' => 30,
            'Q' => 27
        ];
    }"""

if old_widths not in c3:
    errors.append('EXPORT CLASS: columnWidths not found')
else:
    c3 = c3.replace(old_widths, new_widths, 1)

# Update AfterSheet events for new column range (A:Q instead of A:O)
c3 = c3.replace("'A:O'", "'A:Q'")
c3 = c3.replace("'A1:O'", "'A1:Q'")

# Update hidden column (K stays hidden) and wrap text on P (address) instead of N
old_wrap = "$event->sheet->getStyle('N')->getAlignment()->setWrapText(true);"
new_wrap = """$event->sheet->getStyle('P')->getAlignment()->setWrapText(true);
                $event->sheet->getStyle('N')->getAlignment()->setWrapText(true);"""

if old_wrap in c3:
    c3 = c3.replace(old_wrap, new_wrap, 1)

with open(f3, 'w') as fh:
    fh.write(c3)
print('3. Export class: updated widths + sort A-Z')

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

print('\n=== ALL PATCHES APPLIED ===')
