# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

old_note = '''<input type="text" id="batch_note" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">'''

new_note = '''<input type="text" id="batch_note" placeholder="หมายเหตุเพิ่มเติม (ถ้ามี)" style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem;">
                <small style="color:#ef4444; font-size:0.72rem;">*ระบบ Note ทุกรายการที่เลือก*</small>'''

if old_note not in content:
    print('ERROR: note input not found')
    sys.exit(1)

content = content.replace(old_note, new_note, 1)

with open(f, 'w') as fh:
    fh.write(content)

print('Note hint added')
