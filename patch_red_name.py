# -*- coding: utf-8 -*-
# Change recipient name color to red on both frontend and admin pages

# Frontend
f1 = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f1, 'r') as fh:
    c = fh.read()
c = c.replace('color:#0369a1;font-weight:600;margin-top:2px', 'color:#dc2626;font-weight:600;margin-top:2px')
with open(f1, 'w') as fh:
    fh.write(c)
print('Frontend: recipient name color changed to red')

# Admin
f2 = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(f2, 'r') as fh:
    c2 = fh.read()
c2 = c2.replace('color:#0369a1;font-weight:600;margin-top:2px', 'color:#dc2626;font-weight:600;margin-top:2px')
with open(f2, 'w') as fh:
    fh.write(c2)
print('Admin: recipient name color changed to red')
