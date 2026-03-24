# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === FIX 1: Update recipient_filter inline style to match SHOW select ===
old_recipient = '''<select id="recipient_filter" style="padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #334155; background: white; width: 100%; cursor: pointer;">'''

new_recipient = '''<select id="recipient_filter" class="unified-select">'''

if old_recipient not in content:
    errors.append('recipient_filter inline style not found')
else:
    content = content.replace(old_recipient, new_recipient)

# === FIX 2: Add unified-select CSS class + override SHOW select to use same style ===
# Find the .dataTables_length select block and add unified class after it
old_dt_select = """        .dataTables_length select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 12px !important;
            padding-right: 30px !important;
            cursor: pointer;
        }"""

new_dt_select = """        .dataTables_length select,
        .unified-select {
            font-size: 14px !important;
            height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0 15px !important;
            padding-right: 36px !important;
            background-color: white !important;
            color: #475569 !important;
            width: 100% !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            appearance: none !important;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 12px center !important;
            background-size: 12px !important;
            cursor: pointer;
        }"""

if old_dt_select not in content:
    errors.append('.dataTables_length select block not found')
else:
    content = content.replace(old_dt_select, new_dt_select)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Unified select style applied')
