# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Replace the empty length-container with a static select ===
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

# === PATCH 2: Remove the JS that moves DataTables length to container ===
old_move = '''                    var length = $('#dt-mant-table-1_wrapper .dataTables_length');
                    var filter = $('#dt-mant-table-1_wrapper .dataTables_filter');

                    // Add Placeholders Manually
                    filter.find('input').attr('placeholder', 'Search...');

                    // Move to specific containers
                    length.detach().appendTo('#length-container');
                    filter.detach().appendTo('#filter-container');'''

new_move = '''                    var filter = $('#dt-mant-table-1_wrapper .dataTables_filter');

                    // Add Placeholders Manually
                    filter.find('input').attr('placeholder', 'Search...');

                    // Hide original DataTables length dropdown (we use custom)
                    $('#dt-mant-table-1_wrapper .dataTables_length').hide();
                    // Move filter to custom container
                    filter.detach().appendTo('#filter-container');'''

if old_move not in content:
    errors.append('initComplete JS move block not found')
else:
    content = content.replace(old_move, new_move)

# === PATCH 3: Add JS handler for custom_page_length change ===
old_recipient_change = "    $(document).on('change', '#recipient_filter', function() {"

new_recipient_change = """    // Custom page length handler
    $('#custom_page_length').on('change', function() {
        var newLen = parseInt($(this).val());
        $('#dt-mant-table-1').DataTable().page.len(newLen).draw();
    });

    $(document).on('change', '#recipient_filter', function() {"""

if old_recipient_change not in content:
    errors.append('recipient_filter change handler not found')
else:
    content = content.replace(old_recipient_change, new_recipient_change, 1)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Static SHOW select applied - no more bouncing')
