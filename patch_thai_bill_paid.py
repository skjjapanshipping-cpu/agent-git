#!/usr/bin/env python3
"""Add 'ชำระเงินแล้วค่าส่งไทย' button to Admin My Shipping"""
import sys

# 1. Add route
path = '/var/www/vhosts/skjjapanshipping.com/backoffice/routes/web.php'
with open(path, 'r') as f:
    content = f.read()

old = "    Route::post('/update-status-pay2', 'CustomershippingController@update_StatusByIDs2')->name('update-status-pay2');"
new_route = old + "\n    Route::post('/update-thai-bill-paid', 'CustomershippingController@updateThaiBillPaid')->name('update-thai-bill-paid');"

if 'update-thai-bill-paid' not in content:
    content = content.replace(old, new_route)
    with open(path, 'w') as f:
        f.write(content)
    print('[routes/web.php] Added route')
else:
    print('[routes/web.php] Route already exists')

# 2. Add controller method
path2 = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomershippingController.php'
with open(path2, 'r') as f:
    content2 = f.read()

method_code = '''
    /**
     * อัพเดทสถานะค่าส่งไทย → โอนแล้ว (thai_bill_status = 2)
     */
    public function updateThaiBillPaid(Request $request)
    {
        $ids = explode(',', $request->input('thai_bill_ids'));

        try {
            Customershipping::whereIn('id', $ids)->update([
                'thai_bill_status' => 2
            ]);

            return redirect()->route('customershippings.index')
                ->with('success', 'อัปเดตสถานะค่าส่งไทย → ชำระเงินแล้ว');
        } catch (\\Exception $e) {
            return redirect()->route('customershippings.index')
                ->with('error', 'เกิดข้อผิดพลาด: ' . $e->getMessage());
        }
    }
'''

if 'updateThaiBillPaid' not in content2:
    # Insert before the last closing brace of the class
    last_brace = content2.rfind('}')
    content2 = content2[:last_brace] + method_code + '\n' + content2[last_brace:]
    with open(path2, 'w') as f:
        f.write(content2)
    print('[CustomershippingController.php] Added updateThaiBillPaid method')
else:
    print('[CustomershippingController.php] Method already exists')

# 3. Add button in view
path3 = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(path3, 'r') as f:
    content3 = f.read()

# Add button after the existing "ชำระเงินแล้ว" form
old_btn = """                                <form method="POST" action="{{ route('update-status-pay2') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="track_ids2" id="trackIdsInput2" value="">
                                    <input type="submit" class="btn-modern btn-blue disabled" id="updateSelected2" value="💰 ชำระเงินแล้ว">
                                </form>"""

new_btn = old_btn + """
                                <form method="POST" action="{{ route('update-thai-bill-paid') }}" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="thai_bill_ids" id="thaiBillIdsInput" value="">
                                    <input type="submit" class="btn-modern btn-modern disabled" id="updateThaiBillPaid" value="🚚 ชำระเงินแล้วค่าส่งไทย" style="background:linear-gradient(135deg,#0ea5e9,#06b6d4) !important; color:#fff !important;">
                                </form>"""

if 'updateThaiBillPaid' not in content3:
    content3 = content3.replace(old_btn, new_btn)
    with open(path3, 'w') as f:
        f.write(content3)
    print('[index.blade.php] Added Thai bill paid button')
else:
    print('[index.blade.php] Button already exists')

# 4. Wire up JS - add thaiBillIdsInput to the click handler and enable/disable logic
with open(path3, 'r') as f:
    content3 = f.read()

# Add thaiBillIdsInput to the click handler
old_js1 = "$('#updateSelected,#updateSelected2,#updateSelected3').on('click', function(e) {"
new_js1 = "$('#updateSelected,#updateSelected2,#updateSelected3,#updateThaiBillPaid').on('click', function(e) {"

if '#updateThaiBillPaid' not in content3:
    content3 = content3.replace(old_js1, new_js1)

# Add thaiBillIdsInput value setting
old_js2 = "                    $('#trackIdsInput3').val(selectedIds.join(','));"
new_js2 = old_js2 + "\n                    $('#thaiBillIdsInput').val(selectedIds.join(','));"

if 'thaiBillIdsInput' not in content3:
    content3 = content3.replace(old_js2, new_js2)

# Add enable/disable for the new button
old_js3 = """                var $btn3 = $('#updateSelected3');"""
new_js3 = """                var $btn3 = $('#updateSelected3');
                var $btn4 = $('#updateThaiBillPaid');"""

content3 = content3.replace(old_js3, new_js3)

# Enable button when checkboxes selected
old_js4 = """                    $btn3.removeClass('disabled');"""
new_js4 = """                    $btn3.removeClass('disabled');
                    $btn4.removeClass('disabled');"""

content3 = content3.replace(old_js4, new_js4, 1)

# Disable button when no checkboxes
old_js5 = """                    $btn3.addClass('disabled');"""
new_js5 = """                    $btn3.addClass('disabled');
                    $btn4.addClass('disabled');"""

content3 = content3.replace(old_js5, new_js5, 1)

with open(path3, 'w') as f:
    f.write(content3)
print('[index.blade.php] JS wiring done')

print('All patches applied successfully!')
