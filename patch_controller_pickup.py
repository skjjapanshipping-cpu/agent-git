# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomerShippingViewController.php'
with open(f, 'r') as fh:
    content = fh.read()

# Update type 1 handler to accept optional delivery_fullname
old_type1 = """            if ($deliveryTypeId == 1) {
                // รับเอง - clear delivery info
                $updateData['delivery_fullname'] = null;
                $updateData['delivery_mobile'] = null;
                $updateData['delivery_address'] = null;
                $updateData['delivery_subdistrict'] = null;
                $updateData['delivery_district'] = null;
                $updateData['delivery_province'] = null;
                $updateData['delivery_postcode'] = null;"""

new_type1 = """            if ($deliveryTypeId == 1) {
                // รับเอง - set pickup person name if provided, clear address info
                $updateData['delivery_fullname'] = $request->input('delivery_fullname', null);
                $updateData['delivery_mobile'] = null;
                $updateData['delivery_address'] = null;
                $updateData['delivery_subdistrict'] = null;
                $updateData['delivery_district'] = null;
                $updateData['delivery_province'] = null;
                $updateData['delivery_postcode'] = null;"""

if old_type1 not in content:
    print('ERROR: type 1 block not found')
    sys.exit(1)

content = content.replace(old_type1, new_type1)

with open(f, 'w') as fh:
    fh.write(content)

print('Controller: pickup name support added')
