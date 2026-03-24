import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# === PATCH 1: Update CSS grid from 3 columns to 4 ===
old_css = "grid-template-columns: 2fr 1fr 2fr; /* Date(2) Show(1) Search(2) */"
new_css = "grid-template-columns: 2fr 2fr 1fr 2fr; /* Date(2) Recipient(2) Show(1) Search(2) */"
if old_css not in content:
    errors.append('CSS grid not found')
else:
    content = content.replace(old_css, new_css)

# === PATCH 2: Add recipient dropdown control group after length-container ===
old_controls = """                            <!-- Search Container (Filled by JS) -->
                            <div class="control-group" id="filter-container">
                                <label class="control-label d-md-block d-none">SEARCH:</label>
                                <!-- JS puts Filter here -->
                            </div>"""

new_controls = """                            <!-- Recipient Filter -->
                            <div class="control-group" id="recipient-filter-group">
                                <label class="control-label d-md-block d-none">RECIPIENT:</label>
                                <select id="recipient_filter" style="padding: 6px 10px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 13px; color: #334155; background: white; width: 100%; cursor: pointer;">
                                    <option value="">ผู้รับทั้งหมด</option>
                                </select>
                            </div>

                            <!-- Search Container (Filled by JS) -->
                            <div class="control-group" id="filter-container">
                                <label class="control-label d-md-block d-none">SEARCH:</label>
                                <!-- JS puts Filter here -->
                            </div>"""

if old_controls not in content:
    errors.append('Search container HTML not found')
else:
    content = content.replace(old_controls, new_controls)

# === PATCH 3: Add recipient_filter to DataTable AJAX data ===
old_ajax_data = """d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();"""

new_ajax_data = """d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.recipient_filter = $('#recipient_filter').val();"""

if old_ajax_data not in content:
    errors.append('AJAX data block not found')
else:
    content = content.replace(old_ajax_data, new_ajax_data)

# === PATCH 4: Add JS function to load recipients + event binding ===
# Insert after the initComplete block's closing, before "columns" definition
old_js_init = """                    filter.detach().appendTo('#filter-container');
                },
                "columns": ["""

new_js_init = """                    filter.detach().appendTo('#filter-container');

                    // Load recipients for current ETD
                    loadRecipients();
                },
                "columns": ["""

if old_js_init not in content:
    errors.append('initComplete/columns JS block not found')
else:
    content = content.replace(old_js_init, new_js_init)

# === PATCH 5: Add loadRecipients function and event handlers at end of script ===
# Find the closing </script> tag that's just before @endsection or at the very end
old_end = "@endsection"

new_end = """<script>
    // === Recipient Filter Logic ===
    function loadRecipients() {
        var etd = $('#start_date').val();
        $.ajax({
            url: "{{ route('fetch.recipients') }}",
            type: "POST",
            data: { etd: etd, _token: "{{ csrf_token() }}" },
            success: function(res) {
                var sel = $('#recipient_filter');
                var currentVal = sel.val();
                sel.find('option:not(:first)').remove();
                if (res.recipients && res.recipients.length > 0) {
                    res.recipients.forEach(function(r) {
                        sel.append('<option value="' + r.value + '">' + r.label + ' (' + r.count + ')</option>');
                    });
                }
                // Restore selection if still exists
                if (currentVal && sel.find('option[value="' + currentVal + '"]').length > 0) {
                    sel.val(currentVal);
                } else {
                    sel.val('');
                }
            }
        });
    }

    // When ETD changes, reload recipients and reset filter
    $(document).on('change', '#start_date', function() {
        $('#recipient_filter').val('');
        loadRecipients();
    });

    // When recipient filter changes, reload DataTable
    $(document).on('change', '#recipient_filter', function() {
        $('#dt-mant-table-1').DataTable().ajax.reload();
    });
</script>

@endsection"""

if old_end not in content:
    errors.append('@endsection not found')
else:
    # Replace only the LAST occurrence of @endsection
    idx = content.rfind(old_end)
    content = content[:idx] + new_end + content[idx + len(old_end):]

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Blade patched successfully')
