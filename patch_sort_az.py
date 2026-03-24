# -*- coding: utf-8 -*-
import sys

# ==============================
# PATCH 1: Admin getAdminRecipients - sort A-Z
# ==============================
f1 = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomershippingController.php'
with open(f1, 'r') as fh:
    content1 = fh.read()

errors = []

old_order = "->orderByRaw('cnt DESC')"
new_order = "->orderByRaw('recipient_name ASC')"

if old_order not in content1:
    errors.append('CONTROLLER: orderByRaw cnt DESC not found')
else:
    content1 = content1.replace(old_order, new_order, 1)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f1, 'w') as fh:
    fh.write(content1)
print('CONTROLLER: Recipient dropdown sorted A-Z')

# ==============================
# PATCH 2: Excel export - sort by delivery_fullname A-Z
# ==============================
f2 = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Exports/CustomershippingExport.php'
with open(f2, 'r') as fh:
    content2 = fh.read()

old_export_order = "return $query->orderByRaw('delivery_fullname ASC, delivery_address ASC, delivery_subdistrict ASC, delivery_district ASC, delivery_province ASC, delivery_postcode ASC, customerno ASC, ship_date DESC')->take(2000)->get();"

new_export_order = "return $query->orderByRaw('delivery_fullname ASC, delivery_address ASC, delivery_province ASC, delivery_district ASC, delivery_subdistrict ASC, delivery_postcode ASC, customerno ASC, ship_date DESC')->take(2000)->get();"

if old_export_order not in content2:
    # Already correct or different - just verify delivery_fullname ASC is first
    if 'delivery_fullname ASC' in content2:
        print('EXPORT: Already sorted by delivery_fullname ASC - OK')
    else:
        print('EXPORT ERROR: orderByRaw not found')
        sys.exit(1)
else:
    content2 = content2.replace(old_export_order, new_export_order, 1)
    with open(f2, 'w') as fh:
        fh.write(content2)
    print('EXPORT: Sort order confirmed A-Z by recipient name')

print('\n=== ALL PATCHES APPLIED ===')
