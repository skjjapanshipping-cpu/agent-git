# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === FIX 1: Add /skjtrack prefix to API URL ===
old_url = "$.get('/api/address/searchCustomerAddress'"
new_url = "$.get('/skjtrack/api/address/searchCustomerAddress'"
count = content.count(old_url)
if count == 0:
    errors.append('searchCustomerAddress URL not found')
else:
    content = content.replace(old_url, new_url)
    print(f'Fixed API URL: {count} occurrences')

# === FIX 2: Add note field to batch modal - after postcode row, before </div> closing batchRecipientFields ===
old_fields_end = """                </div>
            </div>
        </div>

        <!-- Footer -->
        <div style="padding:16px 28px 24px; border-top:1px solid #f1f5f9; display:flex; gap:10px; justify-content:flex-end;">"""

new_fields_end = """                </div>
            </div>

            <!-- Note field (always visible) -->
            <div style="margin-bottom:10px; margin-top:16px;">
                <label style="display:block; font-size:0.82rem; font-weight:600; color:#374151; margin-bottom:6px;">หมายเหตุ <span style="font-weight:400; color:#94a3b8;">(ไม่บังคับ)</span></label>
                <input type="text" id="batch_note" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
            </div>
        </div>

        <!-- Footer -->
        <div style="padding:16px 28px 24px; border-top:1px solid #f1f5f9; display:flex; gap:10px; justify-content:flex-end;">"""

if old_fields_end not in content:
    errors.append('fields end / footer block not found')
else:
    content = content.replace(old_fields_end, new_fields_end)

# === FIX 3: Add batch_note to reset form in openBatchRecipientModal ===
old_reset = "$('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode').val('');"
new_reset = "$('#batch_fullname, #batch_mobile, #batch_address, #batch_subdistrict, #batch_district, #batch_province, #batch_postcode, #batch_note').val('');"
if old_reset not in content:
    errors.append('form reset line not found')
else:
    content = content.replace(old_reset, new_reset)

# === FIX 4: Add note to data object in submitBatchRecipient ===
old_data_build = """        var data = {
            ids: batchSelectedIds,
            delivery_type_id: parseInt(deliveryType),
            _token: '{{ csrf_token() }}'
        };"""

new_data_build = """        var batchNote = $('#batch_note').val().trim();
        var data = {
            ids: batchSelectedIds,
            delivery_type_id: parseInt(deliveryType),
            _token: '{{ csrf_token() }}'
        };
        if (batchNote) { data.note = batchNote; }"""

if old_data_build not in content:
    errors.append('data build block not found')
else:
    content = content.replace(old_data_build, new_data_build)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('API URL fix + Note field added successfully')
