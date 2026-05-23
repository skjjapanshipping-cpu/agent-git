<?php
namespace App\Http\Controllers\API;
use App\Tambon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TambonController extends Controller
{
    public function getProvinces()
    {
        $provinces = Tambon::select('province')
            ->distinct()
            ->get();
        return $provinces;
    }
    public function getAmphoes(Request $request)
    {
        $province = $request->get('province');
        $amphoes = Tambon::select('amphoe')
            ->where('province', 'like', "%$province%")
            ->distinct()
            ->get();
        return $amphoes;
    }
    public function getTambons(Request $request)
    {
        $province = $request->get('province');
        $amphoe = $request->get('amphoe');
        $tambons = Tambon::select('tambon')
            ->where('province', 'like', "%$province%")
            ->where('amphoe', 'like', "%$amphoe%")
            ->distinct()
            ->get();
        return $tambons;
    }
    public function getZipcodes(Request $request)
    {
        $province = $request->get('province');
        $amphoe = $request->get('amphoe');
        $tambon = $request->get('tambon');
        $zipcodes = Tambon::select('zipcode')
            ->where('province', $province)
            ->where('amphoe', $amphoe)
            ->where('tambon', $tambon)
            ->get();
        return $zipcodes;
    }

    // ===== Quick address search =====
    // GET /api/tambons/search?q=ค้นหา&limit=30
    // ค้นหาแบบ unified: รับคำค้นเดียวแล้วลองจับกับ จังหวัด/อำเภอ/ตำบล/รหัสไปรษณีย์
    // ส่งกลับเป็น array ของชุดที่อยู่เต็ม [{province, amphoe, tambon, zipcode}, ...]
    // เรียงตามความน่าจะเป็น: exact zipcode → prefix → contains
    public function searchAddress(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $limit = min((int) $request->get('limit', 30), 100);

        if ($q === '') {
            return response()->json([]);
        }

        // ถ้า input เป็นตัวเลขล้วน 1-5 หลัก → เน้น zipcode
        if (preg_match('/^\d{1,5}$/', $q)) {
            $rows = Tambon::select('province', 'amphoe', 'tambon', 'zipcode')
                ->where('zipcode', 'like', $q . '%')
                ->groupBy('province', 'amphoe', 'tambon', 'zipcode')
                ->orderBy('zipcode')
                ->orderBy('province')
                ->orderBy('amphoe')
                ->orderBy('tambon')
                ->limit($limit)
                ->get();
            return response()->json($rows);
        }

        // ค้นหา text: ลอง match province / amphoe / tambon ด้วย LIKE %q%
        $like = '%' . $q . '%';
        $prefix = $q . '%';

        // ใช้ raw priority column เพื่อ rank: exact=0, prefix=1, contains=2
        $rows = Tambon::select('province', 'amphoe', 'tambon', 'zipcode')
            ->selectRaw(
                'MIN(CASE
                    WHEN tambon = ? OR amphoe = ? OR province = ? THEN 0
                    WHEN tambon LIKE ? OR amphoe LIKE ? OR province LIKE ? THEN 1
                    ELSE 2
                END) AS _rank',
                [$q, $q, $q, $prefix, $prefix, $prefix]
            )
            ->where(function ($qq) use ($like) {
                $qq->where('tambon', 'like', $like)
                   ->orWhere('amphoe', 'like', $like)
                   ->orWhere('province', 'like', $like);
            })
            ->groupBy('province', 'amphoe', 'tambon', 'zipcode')
            ->orderBy('_rank')
            ->orderBy('province')
            ->orderBy('amphoe')
            ->orderBy('tambon')
            ->limit($limit)
            ->get()
            ->map(function ($r) {
                return [
                    'province' => $r->province,
                    'amphoe'   => $r->amphoe,
                    'tambon'   => $r->tambon,
                    'zipcode'  => $r->zipcode,
                ];
            });

        return response()->json($rows);
    }
}
