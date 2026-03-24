#!/usr/bin/env python3
"""
Update CustomershippingController to handle comma-separated delivery_type_id values.
Changes: where('delivery_type_id', $val) -> whereIn('delivery_type_id', explode(',', $val))
"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomershippingController.php'
with open(path, 'r', encoding='utf-8') as f:
    content = f.read()

old = """if (!empty($request->delivery_type_id))
                            $query->where('delivery_type_id', $request->delivery_type_id);"""

new = """if (!empty($request->delivery_type_id)) {
                            $dtIds = explode(',', $request->delivery_type_id);
                            if (count($dtIds) === 1) {
                                $query->where('delivery_type_id', $dtIds[0]);
                            } else {
                                $query->whereIn('delivery_type_id', $dtIds);
                            }
                        }"""

if old in content:
    content = content.replace(old, new)
    with open(path, 'w', encoding='utf-8') as f:
        f.write(content)
    print('SUCCESS: Updated controller to handle multiple delivery_type_id values')
else:
    print('FAILED: Could not find old code')
    # Debug
    if 'delivery_type_id' in content:
        for i, line in enumerate(content.split('\n'), 1):
            if 'delivery_type_id' in line and 'query' in line:
                print(f'  Line {i}: {line.strip()[:80]}')
