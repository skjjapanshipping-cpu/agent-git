#!/usr/bin/env python3
"""
Fix recipient dropdown:
1. If delivery_type_id=1 (รับเอง), add "(รับเอง)" prefix to label
2. Move "รับเอง" entries to bottom of the list
"""

path = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomershippingController.php'
with open(path, 'r') as f:
    content = f.read()

old = """        $recipients = $query->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('recipient_name')
            ->orderByRaw('recipient_name ASC')
            ->get()
            ->map(function($item) {
                $name = $item->recipient_name;
                if ($name === '__empty__') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
                }
                return ['name' => $name, 'label' => $name, 'count' => $item->cnt, 'value' => $name];
            });

        return response()->json(['recipients' => $recipients]);"""

new = """        $recipients = $query->selectRaw("COALESCE(NULLIF(TRIM(delivery_fullname), ''), '__empty__') as recipient_name")
            ->selectRaw('COUNT(id) as cnt')
            ->selectRaw('MAX(delivery_type_id) as dtype')
            ->groupBy('recipient_name')
            ->orderByRaw('recipient_name ASC')
            ->get()
            ->map(function($item) {
                $name = $item->recipient_name;
                $isPickup = ($item->dtype == 1);
                if ($name === '__empty__') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__', 'pickup' => false];
                }
                $label = $isPickup ? '(รับเอง) ' . $name : $name;
                return ['name' => $name, 'label' => $label, 'count' => $item->cnt, 'value' => $name, 'pickup' => $isPickup];
            });

        // แยก: ผู้รับปกติก่อน → รับเอง ล่างสุด
        $normal = $recipients->filter(fn($r) => !$r['pickup'])->values();
        $pickup = $recipients->filter(fn($r) => $r['pickup'])->values();
        $recipients = $normal->merge($pickup)->values();

        return response()->json(['recipients' => $recipients]);"""

content = content.replace(old, new)

with open(path, 'w') as f:
    f.write(content)
print('Done - recipient dropdown patched')
