# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# Fix pageLength default to 100
old_page = '"pageLength": 50,'
new_page = '"pageLength": 100,'
if old_page not in content:
    errors.append('pageLength not found')
else:
    content = content.replace(old_page, new_page)

# Fix lengthMenu options to 100, 150, 200, 300
old_menu = '"lengthMenu": [[10, 25, 50, 100, 150, 200], [10, 25, 50, 100, 150, 200]],'
new_menu = '"lengthMenu": [[100, 150, 200, 300], [100, 150, 200, 300]],'
if old_menu not in content:
    errors.append('lengthMenu not found')
else:
    content = content.replace(old_menu, new_menu)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('PageLength + lengthMenu updated')
