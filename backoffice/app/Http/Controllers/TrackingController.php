<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function index()
    {
        return view('tracking.index');
    }
    public function submitTracking(Request $request)
    {
        // คำค้นหาที่ไม่ต้องแสดงผลลัพธ์
        $excludedKeywords = [
            'ไม่มีเลขพัสดุ',
            'เลขพัสดุไม่ชัด',
            'เลขพัสดุขาดครึ่ง',
            'รับตามบ้าน'
        ];
        
        // ตรวจสอบว่าคำค้นหามีคำที่ต้องยกเว้นอยู่หรือไม่ (คล้ายๆ like)
        $trackingNo = trim($request->tracking_no);
        foreach ($excludedKeywords as $keyword) {
            if (mb_stripos($trackingNo, $keyword) !== false) {
                return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล Track']);
            }
        }

        // ค้นหาครั้งเดียว ใช้ได้ทั้ง first และ aggregate
        $tracks = Track::whereRaw("replace(track_no, '-', '') = replace(?,'-','') and status = 1", [$request->tracking_no])->get();
        
        if ($tracks->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'ไม่พบข้อมูล Track']);
        }

        $track = $tracks->first();
        
        return response()->json(['success' => true, 'message' => 'บันทึกข้อมูลสำเร็จ', 'track' => [
            'customer_name' => $track->customer_name,
            'track_no' => $track->track_no,
            'cod' => $track->cod,
            'weight' => $track->weight,
            'source_date' => $track->source_date ? $track->source_date->format('d/m/Y') : null,
            'ship_date' => $track->ship_date ? $track->ship_date->format('d/m/Y') : null,
            'destination_date' => $track->destination_date ? $track->destination_date->format('d/m/Y') : null,
            'shipping_method' => $track->shipping_method ?? 1,
            'box_count' => $tracks->count(),
            'total_weight' => $tracks->sum('weight'),
            'total_cod' => $tracks->sum('cod'),
        ]]);
    }
}
