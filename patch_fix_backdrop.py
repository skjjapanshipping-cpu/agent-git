# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# FIX 1: Remove backdrop click to close modal
old_backdrop = """    $('#batchRecipientModal').on('click', function(e) {
        if (e.target === this) closeBatchRecipientModal();
    });"""

new_backdrop = """    // Backdrop click disabled - must use cancel button to close
    // $('#batchRecipientModal').on('click', function(e) {
    //     if (e.target === this) closeBatchRecipientModal();
    // });"""

if old_backdrop not in content:
    errors.append('backdrop click handler not found')
else:
    content = content.replace(old_backdrop, new_backdrop)

# FIX 2: Also remove ESC key close
old_esc = """    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') closeBatchRecipientModal();
    });"""

new_esc = """    // ESC key disabled - must use cancel button to close
    // $(document).on('keydown', function(e) {
    //     if (e.key === 'Escape') closeBatchRecipientModal();
    // });"""

if old_esc not in content:
    errors.append('ESC handler not found')
else:
    content = content.replace(old_esc, new_esc)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Backdrop + ESC close disabled')
