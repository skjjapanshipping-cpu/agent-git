<?php

namespace App\Http\Controllers;

use App\Models\AddressBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressBookController extends Controller
{
    /**
     * Get all addresses for current user
     */
    public function index()
    {
        $addresses = AddressBook::where('user_id', Auth::id())
            ->orderByDesc('is_default')
            ->orderBy('label')
            ->get();

        return response()->json(['success' => true, 'addresses' => $addresses]);
    }

    /**
     * Store a new address
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'nullable|string|max:100',
            'fullname' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'subdistrict' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        $userId = Auth::id();

        // If setting as default, unset others
        if ($request->is_default) {
            AddressBook::where('user_id', $userId)->update(['is_default' => false]);
        }

        $addr = AddressBook::create(array_merge(
            $request->only(['label', 'fullname', 'mobile', 'address', 'subdistrict', 'district', 'province', 'postcode', 'is_default']),
            ['user_id' => $userId]
        ));

        return response()->json(['success' => true, 'address' => $addr], 201);
    }

    /**
     * Update an address
     */
    public function update(Request $request, $id)
    {
        $addr = AddressBook::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'label' => 'nullable|string|max:100',
            'fullname' => 'required|string|max:255',
            'mobile' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:500',
            'subdistrict' => 'nullable|string|max:100',
            'district' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:100',
            'postcode' => 'nullable|string|max:10',
            'is_default' => 'nullable|boolean',
        ]);

        if ($request->is_default) {
            AddressBook::where('user_id', Auth::id())->where('id', '!=', $id)->update(['is_default' => false]);
        }

        $addr->update($request->only(['label', 'fullname', 'mobile', 'address', 'subdistrict', 'district', 'province', 'postcode', 'is_default']));

        return response()->json(['success' => true, 'address' => $addr]);
    }

    /**
     * Delete an address
     */
    public function destroy($id)
    {
        $addr = AddressBook::where('user_id', Auth::id())->findOrFail($id);
        $addr->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Set an address as default
     */
    public function setDefault($id)
    {
        $userId = Auth::id();
        AddressBook::where('user_id', $userId)->update(['is_default' => false]);
        AddressBook::where('user_id', $userId)->where('id', $id)->update(['is_default' => true]);

        return response()->json(['success' => true]);
    }
}
