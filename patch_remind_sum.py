#!/usr/bin/env python3
"""Fix Thai shipping reminder to SUM(DISTINCT thai_bill_amount) instead of MAX"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/ShippopController.php'
with open(path, 'r') as f:
    content = f.read()

# 1. Fix getUnpaidCustomers: MAX → SUM(DISTINCT)
old1 = "DB::raw('MAX(thai_bill_amount) as bill_amount'),"
new1 = "DB::raw('SUM(DISTINCT thai_bill_amount) as bill_amount'),"
content = content.replace(old1, new1)
print('[1] getUnpaidCustomers: MAX → SUM(DISTINCT)')

# 2. Fix sendReminder: max → sum of distinct bill amounts
old2 = "$billAmount = $items->max('thai_bill_amount') ?: 0;"
new2 = "$billAmount = $items->pluck('thai_bill_amount')->filter()->unique()->sum() ?: 0;"
content = content.replace(old2, new2)
print('[2] sendReminder: max → sum of distinct amounts')

with open(path, 'w') as f:
    f.write(content)

print('Done!')
