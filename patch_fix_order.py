import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershippingview/index.blade.php'
with open(f, 'r') as fh:
    content = fh.read()

errors = []

# Fix 1: Modify existing start_date change handler to reset recipient first
old_handler = "$('#start_date').on('change', function () { dataTable.ajax.reload(); });"
new_handler = "$('#start_date').on('change', function () { $('#recipient_filter').val(''); loadRecipients(); dataTable.ajax.reload(); });"

if old_handler not in content:
    errors.append('existing start_date handler not found')
else:
    content = content.replace(old_handler, new_handler)

# Fix 2: Remove the duplicated delegated handler from bottom script
old_delegated = """    // When ETD changes, reload recipients and reset filter
    $(document).on('change', '#start_date', function() {
        $('#recipient_filter').val('');
        loadRecipients();
    });

    // When recipient filter changes, reload DataTable"""

new_delegated = """    // When recipient filter changes, reload DataTable"""

if old_delegated not in content:
    errors.append('delegated start_date handler not found')
else:
    content = content.replace(old_delegated, new_delegated)

if errors:
    print('ERRORS: ' + ', '.join(errors))
    sys.exit(1)

with open(f, 'w') as fh:
    fh.write(content)

print('Event order fix applied successfully')
