# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

# Add SweetAlert2 CDN at the beginning of extra-css section
old_css = "@section('extra-css')\n    <style>"
new_css = "@section('extra-css')\n    <script src=\"https://cdn.jsdelivr.net/npm/sweetalert2@11\"></script>\n    <style>"

if old_css not in content:
    print('ERROR: extra-css section not found')
    sys.exit(1)

content = content.replace(old_css, new_css, 1)

with open(f, 'w') as fh:
    fh.write(content)

print('SweetAlert2 CDN added successfully')
