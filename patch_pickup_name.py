# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Add pickup name field (shown for type 1) before current address preview ===
old_preview = '            <!-- Current address preview (shown for type 2) -->'

new_pickup = """            <!-- Pickup name (shown for type 1 - รับเอง) -->
            <div id="batchPickupNameFields" style="display:none;">
                <div style="margin-bottom:10px;">
                    <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">ชื่อผู้รับ</label>
                    <input type="text" id="batch_pickup_name" placeholder="ชื่อผู้มารับ" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                </div>
            </div>

            <!-- Current address preview (shown for type 2) -->"""

if old_preview not in content:
    errors.append('current address preview marker not found')
else:
    content = content.replace(old_preview, new_pickup, 1)

# === PATCH 2: Update JS delivery type handler to show pickup name fields ===
old_handler = """    // Toggle fields based on delivery type
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

new_handler = """    // Toggle fields based on delivery type
    $('#batch_delivery_type').on('change', function() {
        var val = $(this).val();
        if (val === '3') {
            $('#batchRecipientFields').slideDown(200);
            $('#batchCurrentAddressPreview').slideUp(200);
            $('#batchPickupNameFields').slideUp(200);
        } else if (val === '2') {
            $('#batchRecipientFields').slideUp(200);
            $('#batchCurrentAddressPreview').slideDown(200);
            $('#batchPickupNameFields').slideUp(200);
        } else {
            $('#batchRecipientFields').slideUp(200);
            $('#batchCurrentAddressPreview').slideUp(200);
            $('#batchPickupNameFields').slideDown(200);
        }
    });"""

if old_handler not in content:
    errors.append('delivery type change handler not found')
else:
    content = content.replace(old_handler, new_handler)

# === PATCH 3: Add batch_pickup_name to form reset ===
old_reset = "$('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode, #batch_note').val('');"
new_reset = "$('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode, #batch_note, #batch_pickup_name').val('');"

if old_reset not in content:
    errors.append('form reset line not found')
else:
    content = content.replace(old_reset, new_reset)

# === PATCH 4: Add pickup name to form reset - also hide pickup fields on open (default type=3) ===
old_show = "$('#batchRecipientFields').show();"
new_show = "$('#batchRecipientFields').show();\n        $('#batchPickupNameFields').hide();\n        $('#batchCurrentAddressPreview').hide();"

if old_show not in content:
    errors.append('batchRecipientFields show not found')
else:
    content = content.replace(old_show, new_show, 1)

# === PATCH 5: Update submitBatchRecipient to send pickup name for type 1 ===
old_submit_type = "var typeName = deliveryType === '1' ? 'รับเอง' : (deliveryType === '2' ? 'ที่อยู่ปัจจุบัน' : data.delivery_fullname);"

new_submit_type = """if (deliveryType === '1') {
            var pickupName = $('#batch_pickup_name').val().trim();
            if (pickupName) { data.delivery_fullname = pickupName; }
        }

        var typeName = deliveryType === '1' ? ('รับเอง: ' + ($('#batch_pickup_name').val().trim() || '-')) : (deliveryType === '2' ? 'ที่อยู่ปัจจุบัน' : data.delivery_fullname);"""

if old_submit_type not in content:
    errors.append('typeName line not found')
else:
    content = content.replace(old_submit_type, new_submit_type)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Pickup name field added successfully')
