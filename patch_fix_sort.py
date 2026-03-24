# -*- coding: utf-8 -*-
f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Exports/CustomershippigviewHtmlExport.php'
with open(f, 'r') as fh:
    c = fh.read()

# Remove latest('ship_date') which adds ORDER BY ship_date DESC first
old = "Customershipping::latest('ship_date')->where('excel_status','=','1')"
new = "Customershipping::where('excel_status','=','1')"

if old not in c:
    print('ERROR: latest ship_date not found')
    exit(1)
c = c.replace(old, new, 1)

# Fix orderByRaw: put empty names (รับเอง) last, then A-Z
old2 = "->orderByRaw('delivery_fullname ASC')"
new2 = "->orderByRaw(\"CASE WHEN delivery_fullname IS NULL OR TRIM(delivery_fullname) = '' THEN 1 ELSE 0 END ASC, delivery_fullname ASC\")"

if old2 not in c:
    print('ERROR: orderByRaw not found')
    exit(1)
c = c.replace(old2, new2, 1)

with open(f, 'w') as fh:
    fh.write(c)
print('DONE: removed latest(), sort A-Z with empty names last')
