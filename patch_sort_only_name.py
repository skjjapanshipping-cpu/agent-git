# -*- coding: utf-8 -*-
f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Exports/CustomershippigviewHtmlExport.php'
with open(f, 'r') as fh:
    c = fh.read()

old = "->orderByRaw('delivery_fullname ASC, customerno ASC, ship_date DESC')"
new = "->orderByRaw('delivery_fullname ASC')"

c = c.replace(old, new, 1)

with open(f, 'w') as fh:
    fh.write(c)
print('DONE: sort by delivery_fullname ASC only')
