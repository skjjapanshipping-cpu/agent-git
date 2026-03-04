<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\User;
use App\Models\Customershipping;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AddressController extends Controller
{
    public function searchAddress(Request $request)
    {
        $keyword = $request->get('term');
        
        return DB::table('tambons')
            ->where('tambon', 'LIKE', "%{$keyword}%")
            ->orWhere('amphoe', 'LIKE', "%{$keyword}%")
            ->orWhere('province', 'LIKE', "%{$keyword}%")
            ->orWhere('zipcode', 'LIKE', "%{$keyword}%")
            ->select([
                'tambon',
                'amphoe',
                'province',
                'zipcode'
            ])
            ->distinct()
            ->get()
            ->map(function($item) {
                return [
                    'id' => $item->tambon.'|'.$item->amphoe.'|'.$item->province.'|'.$item->zipcode,
                    'text' => "{$item->tambon} >> {$item->amphoe} >> {$item->province} >> {$item->zipcode}"
                ];
            });
    }

    public function getAmphoes(Request $request)
    {
        $province = $request->get('province');
        
        return DB::table('tambons')
            ->where('province', $province)
            ->select('amphoe')
            ->distinct()
            ->orderBy('amphoe')
            ->get();
    }

    public function getTambons(Request $request)
    {
        $province = $request->get('province');
        $amphoe = $request->get('amphoe');
        
        return DB::table('tambons')
            ->where('province', $province)
            ->where('amphoe', $amphoe)
            ->select('tambon', 'zipcode')
            ->distinct()
            ->orderBy('tambon')
            ->get();
    }

    // เพิ่มเมธอดสำหรับดึงจังหวัดทั้งหมด
    public function getProvinces()
    {
        return DB::table('tambons')
            ->select('province')
            ->distinct()
            ->orderBy('province')
            ->get();
    }

    // เพิ่มเมธอดสำหรับดึงข้อมูลที่อยู่จาก zipcode
    public function getAddressByZipcode(Request $request)
    {
        $zipcode = $request->get('zipcode');
        
        return DB::table('tambons')
            ->where('zipcode', $zipcode)
            ->select('tambon', 'amphoe', 'province', 'zipcode')
            ->distinct()
            ->get();
    }

    public function searchCustomerAddress(Request $request)
    {
        $keyword = $request->get('term');
        $field = $request->get('field');
        
        if (!Auth::check()) {
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        $authUser = Auth::user();
        
  
            $query = Customershipping::where('customerno', $authUser->customerno);

            if ($field == 'delivery_fullname') {
                $query->where('delivery_fullname', 'LIKE', "%{$keyword}%");
            } else if ($field == 'delivery_mobile') {
                $query->where('delivery_mobile', 'LIKE', "%{$keyword}%"); 
            }

            $results = $query->select([
                    'delivery_fullname as fullname',
                    'delivery_mobile as mobile', 
                    'delivery_address as address',
                    'delivery_province as province',
                    'delivery_district as amphoe',
                    'delivery_subdistrict as tambon',
                    'delivery_postcode as zipcode'
                ])
                ->distinct()
                ->get();

            $mapped = $results->map(function($item) {
                $text = implode(' ', [
                    $item->fullname,
                    $item->mobile,
                    $item->address,
                    $item->province,
                    $item->amphoe,
                    $item->tambon,
                    $item->zipcode
                ]);

              
                return [
                    'id' => implode('|', [
                        $item->fullname,
                        $item->mobile,
                        $item->address,
                        $item->province,
                        $item->amphoe,
                        $item->tambon,
                        $item->zipcode
                    ]),
                    'text' => $text,
                    'fullname' => $item->fullname,
                    'mobile' => $item->mobile,
                    'address' => $item->address,
                    'province' => $item->province,
                    'amphoe' => $item->amphoe,
                    'tambon' => $item->tambon,
                    'zipcode' => $item->zipcode
                ];
            });

            return response()->json($mapped);

      
    }



    public function searchCustomerShippingAddress(Request $request)
    {
       
        $keyword = $request->get('term');
        $customerno = $request->get('customerno');
        $field = $request->get('field');
        
  
            $query = Customershipping::where('customerno', $customerno);

            if ($field == 'delivery_fullname') {
                $query->where('delivery_fullname', 'LIKE', "%{$keyword}%");
            } else if ($field == 'delivery_mobile') {
                $query->where('delivery_mobile', 'LIKE', "%{$keyword}%"); 
            }

            $results = $query->select([
                    'delivery_fullname as fullname',
                    'delivery_mobile as mobile', 
                    'delivery_address as address',
                    'delivery_province as province',
                    'delivery_district as amphoe',
                    'delivery_subdistrict as tambon',
                    'delivery_postcode as zipcode'
                ])
                ->distinct()
                ->get();

            $mapped = $results->map(function($item) {
                $text = implode(' ', [
                    $item->fullname,
                    $item->mobile,
                    $item->address,
                    $item->province,
                    $item->amphoe,
                    $item->tambon,
                    $item->zipcode
                ]);

              
                return [
                    'id' => implode('|', [
                        $item->fullname,
                        $item->mobile,
                        $item->address,
                        $item->province,
                        $item->amphoe,
                        $item->tambon,
                        $item->zipcode
                    ]),
                    'text' => $text,
                    'fullname' => $item->fullname,
                    'mobile' => $item->mobile,
                    'address' => $item->address,
                    'province' => $item->province,
                    'amphoe' => $item->amphoe,
                    'tambon' => $item->tambon,
                    'zipcode' => $item->zipcode
                ];
            });

            return response()->json($mapped);

      
    }
}