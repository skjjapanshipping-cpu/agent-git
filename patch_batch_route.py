import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/routes/web.php'
with open(f, 'r') as fh:
    content = fh.read()

old_line = "    Route::post('fetch-recipients', 'CustomerShippingViewController@getRecipients')->name('fetch.recipients');"

new_line = """    Route::post('fetch-recipients', 'CustomerShippingViewController@getRecipients')->name('fetch.recipients');
    Route::post('batch-update-recipient', 'CustomerShippingViewController@batchUpdateRecipient')->name('batch.update.recipient');"""

if old_line not in content:
    print('ERROR: fetch-recipients route not found')
    sys.exit(1)

content = content.replace(old_line, new_line)

with open(f, 'w') as fh:
    fh.write(content)

print('Route: batch-update-recipient added successfully')
