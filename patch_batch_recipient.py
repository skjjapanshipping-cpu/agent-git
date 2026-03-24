import sys

f = '/var/www/vhosts/skjjapanshipping.com/backoffice/app/Http/Controllers/CustomerShippingViewController.php'
with open(f, 'r') as fh:
    content = fh.read()

# Add batchUpdateRecipient method before getRecipients
old_marker = """    /**
     * Get distinct recipient names for a customer + ETD (for filter dropdown)
     */
    public function getRecipients(Request $request)"""

new_method = """    /**
     * Batch update recipient/delivery info for multiple items at once
     */
    public function batchUpdateRecipient(Request $request)
    {
        $authUser = Auth::user();

        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer',
            'delivery_type_id' => 'required|integer|in:1,2,3',
        ]);

        $ids = $request->input('ids');
        $deliveryTypeId = $request->input('delivery_type_id');

        // Security: only update items belonging to this customer
        $validIds = Customershipping::whereIn('id', $ids)
            ->where('customerno', $authUser->customerno)
            ->where('excel_status', 1)
            ->pluck('id')
            ->toArray();

        if (empty($validIds)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรายการที่เลือก'], 404);
        }

        try {
            $updateData = ['delivery_type_id' => $deliveryTypeId];

            if ($deliveryTypeId == 1) {
                // รับเอง - clear delivery info
                $updateData['delivery_fullname'] = null;
                $updateData['delivery_mobile'] = null;
                $updateData['delivery_address'] = null;
                $updateData['delivery_subdistrict'] = null;
                $updateData['delivery_district'] = null;
                $updateData['delivery_province'] = null;
                $updateData['delivery_postcode'] = null;
            } elseif ($deliveryTypeId == 2) {
                // ที่อยู่ปัจจุบัน - use auth user's address
                $updateData['delivery_fullname'] = $authUser->name;
                $updateData['delivery_mobile'] = $authUser->mobile;
                $updateData['delivery_address'] = $authUser->addr;
                $updateData['delivery_subdistrict'] = $authUser->subdistrinct;
                $updateData['delivery_district'] = $authUser->distrinct;
                $updateData['delivery_province'] = $authUser->province;
                $updateData['delivery_postcode'] = $authUser->postcode;
            } else {
                // เพิ่มที่อยู่เอง - use provided data
                $request->validate([
                    'delivery_fullname' => 'required|string|max:255',
                    'delivery_mobile' => 'required|string|max:50',
                    'delivery_address' => 'required|string|max:255',
                    'delivery_subdistrict' => 'required|string|max:255',
                    'delivery_district' => 'required|string|max:255',
                    'delivery_province' => 'required|string|max:255',
                    'delivery_postcode' => 'required|string|max:10',
                ]);

                $updateData['delivery_fullname'] = $request->input('delivery_fullname');
                $updateData['delivery_mobile'] = str_replace([' ', '-'], '', $request->input('delivery_mobile'));
                $updateData['delivery_address'] = $request->input('delivery_address');
                $updateData['delivery_subdistrict'] = $request->input('delivery_subdistrict');
                $updateData['delivery_district'] = $request->input('delivery_district');
                $updateData['delivery_province'] = $request->input('delivery_province');
                $updateData['delivery_postcode'] = $request->input('delivery_postcode');
            }

            Customershipping::whereIn('id', $validIds)->update($updateData);

            return response()->json([
                'success' => true,
                'message' => 'อัพเดทผู้รับสำเร็จ ' . count($validIds) . ' รายการ',
                'updated_count' => count($validIds),
            ]);

        } catch (\\Exception $e) {
            return response()->json(['success' => false, 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get distinct recipient names for a customer + ETD (for filter dropdown)
     */
    public function getRecipients(Request $request)"""

if old_marker not in content:
    print('ERROR: getRecipients marker not found')
    sys.exit(1)

content = content.replace(old_marker, new_method)

with open(f, 'w') as fh:
    fh.write(content)

print('Controller: batchUpdateRecipient added successfully')
