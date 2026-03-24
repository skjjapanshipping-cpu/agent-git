#!/usr/bin/env python3
"""Fix: Add thaiBillIdsInput to JS click handler"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/resources/views/customershipping/index.blade.php'
with open(path, 'r') as f:
    content = f.read()

old = "                    $('#trackIdsInput3').val(selectedIds.join(','));"
new = old + "\n                    $('#thaiBillIdsInput').val(selectedIds.join(','));"

if 'thaiBillIdsInput' not in content.split("$('#trackIdsInput3')")[1][:200]:
    content = content.replace(old, new, 1)
    with open(path, 'w') as f:
        f.write(content)
    print('Fixed: thaiBillIdsInput added to click handler')
else:
    print('Already fixed')
