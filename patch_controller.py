import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomerShippingViewController.php'
with open(f, 'r') as fh:
    content = fh.read()

# 1. Add recipient_filter to queryAll
old_query = "            $queryAll = Customershipping::latest('ship_date')->where('excel_status','=','1')\n                ->where('customerno',$authUser->customerno);"

new_query = """            $queryAll = Customershipping::latest('ship_date')->where('excel_status','=','1')
                ->where('customerno',$authUser->customerno);

            // Filter by recipient name (ผู้รับ)
            if (!empty($request->recipient_filter)) {
                if ($request->recipient_filter === '__empty__') {
                    $queryAll->where(function($q) {
                        $q->whereNull('delivery_fullname')->orWhere('delivery_fullname', '');
                    });
                } else {
                    $queryAll->where('delivery_fullname', $request->recipient_filter);
                }
            }"""

if old_query not in content:
    print('ERROR: old_query not found in controller')
    sys.exit(1)

content = content.replace(old_query, new_query)

# 2. Add getRecipients method before getEtd3Month
old_method = "    public static function getEtd3Month($customerno)"

new_method = """    /**
     * Get distinct recipient names for a customer + ETD (for filter dropdown)
     */
    public function getRecipients(Request $request)
    {
        $authUser = Auth::user();
        $query = Customershipping::where('excel_status', 1)
            ->where('customerno', $authUser->customerno);

        if (!empty($request->etd)) {
            $query->whereRaw('DATE(etd) = ?', [$request->etd]);
        }

        $recipients = $query->select('delivery_fullname')
            ->selectRaw('COUNT(id) as cnt')
            ->groupBy('delivery_fullname')
            ->orderByRaw('cnt DESC')
            ->get()
            ->map(function($item) {
                $name = $item->delivery_fullname;
                if (empty($name) || trim($name) === '') {
                    return ['name' => '', 'label' => 'ยังไม่ระบุผู้รับ', 'count' => $item->cnt, 'value' => '__empty__'];
                }
                return ['name' => $name, 'label' => $name, 'count' => $item->cnt, 'value' => $name];
            });

        return response()->json(['recipients' => $recipients]);
    }

    public static function getEtd3Month($customerno)"""

if old_method not in content:
    print('ERROR: old_method not found in controller')
    sys.exit(1)

content = content.replace(old_method, new_method)

with open(f, 'w') as fh:
    fh.write(content)

print('Controller patched successfully')
