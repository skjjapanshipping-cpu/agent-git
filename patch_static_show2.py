# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Replace length-container HTML ===
old_container = '''                            <!-- Show Entries Container (Filled by JS) -->
                            <div class="control-group" id="length-container">
                                <label class="control-label d-md-block d-none">SHOW:</label>
                                <!-- JS puts Length here -->
                            </div>'''

new_container = '''                            <!-- Show Entries -->
                            <div class="control-group" id="length-container">
                                <label class="control-label d-md-block d-none">SHOW:</label>
                                <select id="custom_page_length" class="unified-select">
                                    <option value="100" selected>100</option>
                                    <option value="150">150</option>
                                    <option value="200">200</option>
                                    <option value="300">300</option>
                                </select>
                            </div>'''

if old_container not in content:
    errors.append('length-container HTML not found')
else:
    content = content.replace(old_container, new_container)

# === PATCH 2: Fix initComplete - hide DT length, keep filter move ===
old_init = """                    // Move to specific containers
                    length.detach().appendTo('#length-container');
                    filter.detach().appendTo('#filter-container');"""

new_init = """                    // Hide original DataTables length (we use custom select)
                    length.hide();
                    filter.detach().appendTo('#filter-container');"""

if old_init not in content:
    errors.append('initComplete move block not found')
else:
    content = content.replace(old_init, new_init)

# === PATCH 3: Add custom_page_length change handler ===
old_recipient_handler = "    $(document).on('change', '#recipient_filter', function() {"

new_handler = """    // Custom page length handler (static select - no bounce)
    $('#custom_page_length').on('change', function() {
        var newLen = parseInt($(this).val());
        $('#dt-mant-table-1').DataTable().page.len(newLen).draw();
    });

    $(document).on('change', '#recipient_filter', function() {"""

if old_recipient_handler not in content:
    errors.append('recipient_filter handler not found')
else:
    content = content.replace(old_recipient_handler, new_handler, 1)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Static SHOW select applied successfully')
