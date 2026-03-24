# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# Fix 1: Remove transition on select to prevent bouncing
old_css = """.dataTables_length select,
        .dataTables_filter input,
        #start_date {
            font-size: 14px !important;
            height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0 15px !important;
            background-color: white !important;
            color: #475569 !important;
            width: 100% !important; /* Expand to container */
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: all 0.2s;
        }"""

new_css = """.dataTables_length select,
        .dataTables_filter input,
        #start_date {
            font-size: 14px !important;
            height: 42px !important;
            border-radius: 10px !important;
            border: 1px solid #e2e8f0 !important;
            padding: 0 15px !important;
            background-color: white !important;
            color: #475569 !important;
            width: 100% !important; /* Expand to container */
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .dataTables_length select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23475569' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") !important;
            background-repeat: no-repeat !important;
            background-position: right 10px center !important;
            background-size: 12px !important;
            padding-right: 30px !important;
            cursor: pointer;
        }"""

if old_css not in content:
    errors.append('dataTables_length select CSS block not found')
else:
    content = content.replace(old_css, new_css)

# Fix 2: Remove the focus box-shadow change that causes the "bounce"
old_focus = """.dataTables_filter input:focus,
        .dataTables_length select:focus,
        #start_date:focus {
            border-color: #1D8AC9 !important;
            box-shadow: 0 0 0 3px rgba(29, 138, 201, 0.1) !important;
            outline: none !important;
        }"""

new_focus = """.dataTables_filter input:focus,
        .dataTables_length select:focus,
        #start_date:focus {
            border-color: #1D8AC9 !important;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
            outline: none !important;
        }"""

if old_focus not in content:
    errors.append('focus CSS block not found')
else:
    content = content.replace(old_focus, new_focus)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Select bounce fix applied')
