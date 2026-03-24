# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomerShippingViewController.php'
with open(f, 'r') as fh:
    content = fh.read()

# Add note to updateData in batchUpdateRecipient - right before the Customershipping::whereIn update
old_update = """            Customershipping::whereIn('id', $validIds)->update($updateData);"""

new_update = """            // Add note if provided
            if (!empty($request->input('note'))) {
                $updateData['note'] = $request->input('note');
            }

            Customershipping::whereIn('id', $validIds)->update($updateData);"""

if old_update not in content:
    print('ERROR: update line not found')
    sys.exit(1)

content = content.replace(old_update, new_update, 1)

with open(f, 'w') as fh:
    fh.write(content)

print('Controller: note field support added')
