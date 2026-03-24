# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Add address preview div ===
old_marker = '            <!-- Recipient Fields (shown for type 3) -->\n            <div id="batchRecipientFields">'

addr_preview = r"""            <!-- Current address preview (shown for type 2) -->
            <div id="batchCurrentAddressPreview" style="display:none; background:#f0f9ff; border:1.5px solid #bae6fd; border-radius:12px; padding:16px; margin-bottom:16px;">
                <div style="font-size:0.82rem; font-weight:600; color:#0369a1; margin-bottom:8px;"><i class="fa fa-map-marker"></i> ที่อยู่ปัจจุบัน</div>
                <div style="font-size:0.88rem; color:#1e293b; line-height:1.6;">
                    <div><b>{{ \App\User::find(auth()->id())->name ?? '' }}</b></div>
                    <div>{{ \App\User::find(auth()->id())->mobile ?? '' }}</div>
                    <div>{{ \App\User::find(auth()->id())->addr ?? '' }}</div>
                    <div>{{ \App\User::find(auth()->id())->subdistrinct ?? '' }} {{ \App\User::find(auth()->id())->distrinct ?? '' }}</div>
                    <div>{{ \App\User::find(auth()->id())->province ?? '' }} {{ \App\User::find(auth()->id())->postcode ?? '' }}</div>
                </div>
            </div>

            <!-- Recipient Fields (shown for type 3) -->
            <div id="batchRecipientFields">"""

if old_marker not in content:
    errors.append('batchRecipientFields marker not found')
else:
    content = content.replace(old_marker, addr_preview, 1)

# === PATCH 2: Update JS delivery type change handler ===
old_handler = """    // Toggle fields based on delivery type
    $('#batch_delivery_type').on('change', function() {
        var val = $(this).val();
        if (val === '3') {
            $('#batchRecipientFields').slideDown(200);
        } else {
            $('#batchRecipientFields').slideUp(200);
        }
    });"""

new_handler = """    // Toggle fields based on delivery type
    $('#batch_delivery_type').on('change', function() {
        var val = $(this).val();
        if (val === '3') {
            $('#batchRecipientFields').slideDown(200);
            $('#batchCurrentAddressPreview').slideUp(200);
        } else if (val === '2') {
            $('#batchRecipientFields').slideUp(200);
            $('#batchCurrentAddressPreview').slideDown(200);
        } else {
            $('#batchRecipientFields').slideUp(200);
            $('#batchCurrentAddressPreview').slideUp(200);
        }
    });"""

if old_handler not in content:
    errors.append('delivery type change handler not found')
else:
    content = content.replace(old_handler, new_handler)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Current address preview added successfully')
