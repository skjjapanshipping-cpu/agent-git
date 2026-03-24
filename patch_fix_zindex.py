# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

# Add SweetAlert z-index override CSS in the batch modal style block
old_css = """    @media (max-width: 768px) {
        #batchRecipientModal > div > div:nth-child(2) {"""

new_css = """    .swal2-container {
        z-index: 99999 !important;
    }
    @media (max-width: 768px) {
        #batchRecipientModal > div > div:nth-child(2) {"""

if old_css not in content:
    print('ERROR: mobile CSS block not found')
    sys.exit(1)

content = content.replace(old_css, new_css, 1)

with open(f, 'w') as fh:
    fh.write(content)

print('SweetAlert z-index fix applied')
