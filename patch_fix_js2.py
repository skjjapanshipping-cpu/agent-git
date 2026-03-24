# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    lines = fh.readlines()

changes = 0

new_lines = []
for i, line in enumerate(lines):
    # FIX 1: Add thai-address-search.js before extra-script's first <script> tag
    if "@section('extra-script')" in line and i + 1 < len(lines) and '<script>' in lines[i+1] and 'thai-address-search' not in lines[i+1]:
        new_lines.append(line)
        new_lines.append('    <script src="{{ asset(\'js/thai-address-search.js\') }}"></script>\n')
        changes += 1
        continue

    # FIX 2: Replace wrong API URL for customer search
    if "/skjtrack/search-customer" in line:
        line = line.replace("/skjtrack/search-customer", "/api/address/searchCustomerAddress")
        line = line.replace("q: query, field: 'name'", "term: query, field: 'delivery_fullname'")
        line = line.replace("q: query, field: 'mobile'", "term: query, field: 'delivery_mobile'")
        changes += 1

    # FIX 3: Replace JSON.parse data-customer approach with jQuery .data() approach
    # Old: $results.append('<div class="search-result-item" data-customer=\'' + JSON.stringify(c) + '\'>' + c.name + ...
    if "data-customer" in line and "JSON.stringify" in line:
        # Replace the whole line with a multi-line jQuery .data() approach
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "var $item = $('<div>').addClass('search-result-item')\n")
        new_lines.append(indent + "    .text((c.fullname || c.text || '') + ' - ' + (c.mobile || ''))\n")
        new_lines.append(indent + "    .data({fullname: c.fullname||'', mobile: c.mobile||'', address: c.address||'', province: c.province||'', amphoe: c.amphoe||'', tambon: c.tambon||'', zipcode: c.zipcode||''});\n")
        new_lines.append(indent + "$results.append($item);\n")
        changes += 1
        continue

    # FIX 4: Replace JSON.parse click handler with .data() click handler
    if "JSON.parse($(this).attr('data-customer'))" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "var $this = $(this);\n")
        changes += 1
        continue

    if "c.name || ''" in line and "batch_fullname" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_fullname').val($this.data('fullname') || '');\n")
        changes += 1
        continue

    if "c.mobile || ''" in line and "batch_mobile" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_mobile').val($this.data('mobile') || '');\n")
        changes += 1
        continue

    if "c.address || ''" in line and "batch_address" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_address').val($this.data('address') || '');\n")
        changes += 1
        continue

    if "c.tambon || c.subdistrict" in line and "batch_subdistrict" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_subdistrict').val($this.data('tambon') || '');\n")
        changes += 1
        continue

    if "c.amphoe || c.district" in line and "batch_district" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_district').val($this.data('amphoe') || '');\n")
        changes += 1
        continue

    if "c.province || ''" in line and "batch_province" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_province').val($this.data('province') || '');\n")
        changes += 1
        continue

    if "c.zipcode || c.postcode" in line and "batch_postcode" in line:
        indent = line[:len(line) - len(line.lstrip())]
        new_lines.append(indent + "$('#batch_postcode').val($this.data('zipcode') || '');\n")
        changes += 1
        continue

    new_lines.append(line)

with open(f, 'w') as fh:
    fh.writelines(new_lines)

print(f'JS fixes applied successfully ({changes} changes)')
