# -*- coding: utf-8 -*-
import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomerShippingViewController.php'
with open(f, 'r') as fh:
    content = fh.read()

# Replace the groupBy query to coalesce NULL and empty string
old_query = """$recipients = $query->select('delivery_fullname')
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('delivery_fullname')
            ->orderByRaw('cnt DESC')"""

new_query = """$recipients = $query->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('recipient_name')
            ->orderByRaw('cnt DESC')"""

if old_query not in content:
    print('ERROR: groupBy query not found')
    sys.exit(1)

content = content.replace(old_query, new_query)

# Fix the map function to use recipient_name instead of delivery_fullname
old_map = """            ->map(function($item) {
                $name = $item->delivery_fullname;
                if (empty($name) || trim($name) === '') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
                }
                return ['name' => $name, 'label' => $name, 'count' => $item->cnt, 'value' => $name];"""

new_map = """            ->map(function($item) {
                $name = $item->recipient_name;
                if ($name === '__empty__') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
                }
                return ['name' => $name, 'label' => $name, 'count' => $item->cnt, 'value' => $name];"""

if old_map not in content:
    print('ERROR: map function not found')
    sys.exit(1)

content = content.replace(old_map, new_map)

with open(f, 'w') as fh:
    fh.write(content)

print('Duplicate recipients fix applied')
