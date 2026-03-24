import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/routes/web.php'
with open(f, 'r') as fh:
    content = fh.read()

old_line = "    Route::post('fetchcustomershippingsview', 'CustomerShippingViewController@fetchCustomershippingsview')->name('fetch.customershippingsview');"

new_line = """    Route::post('fetchcustomershippingsview', 'CustomerShippingViewController@fetchCustomershippingsview')->name('fetch.customershippingsview');
    Route::post('fetch-recipients', 'CustomerShippingViewController@getRecipients')->name('fetch.recipients');"""

if old_line not in content:
    print('ERROR: route line not found')
    sys.exit(1)

content = content.replace(old_line, new_line)

with open(f, 'w') as fh:
    fh.write(content)

print('Route patched successfully')
