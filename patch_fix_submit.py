# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# FIX 1: Change AJAX from JSON to regular form data
old_ajax = """                $.ajax({
                    url: '{{ route("batch.update.recipient") }}',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify(data),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },"""

new_ajax = """                $.ajax({
                    url: '{{ route("batch.update.recipient") }}',
                    type: 'POST',
                    data: data,
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },"""

if old_ajax not in content:
    errors.append('AJAX block not found')
else:
    content = content.replace(old_ajax, new_ajax)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Submit fix applied successfully')
