# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# Remove ALL transitions from the select element
old_transition = """            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .dataTables_length select {"""

new_transition = """            transition: none !important;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .dataTables_length select {"""

if old_transition not in content:
    errors.append('transition block not found')
else:
    content = content.replace(old_transition, new_transition)

# Also ensure the label container doesn't shift
# Add fixed positioning style for #length-container
old_control = """.control-group {
            display: flex;
            flex-direction: column;"""

new_control = """.control-group {
            display: flex;
            flex-direction: column;
            min-height: 68px;"""

if old_control not in content:
    errors.append('control-group CSS not found')
else:
    content = content.replace(old_control, new_control, 1)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Arrow bounce final fix applied')
