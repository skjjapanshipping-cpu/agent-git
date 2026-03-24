# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Exports/CustomershippingExport.php'
with open(f, 'r') as fh:
    content = fh.read()

# Change the orderBy to group by delivery address fields first
old_order = "return $query->orderByRaw('etd DESC, customerno ASC, ship_date DESC')->take(2000)->get();"

new_order = "return $query->orderByRaw('delivery_fullname ASC, delivery_address ASC, delivery_subdistrict ASC, delivery_district ASC, delivery_province ASC, delivery_postcode ASC, customerno ASC, ship_date DESC')->take(2000)->get();"

if old_order not in content:
    print('ERROR: orderByRaw not found')
    sys.exit(1)

content = content.replace(old_order, new_order, 1)

with open(f, 'w') as fh:
    fh.write(content)

print('Export sort by address grouping applied')
