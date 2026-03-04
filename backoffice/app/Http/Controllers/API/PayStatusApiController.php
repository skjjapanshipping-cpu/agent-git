<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customershipping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PayStatusApiController extends Controller
{
    /**
     * Update pay_status from SKJ Chat when slip matches invoice amount
     * Called by SKJ Chat slip verification system
     */
    public function updateFromChat(Request $request)
    {
        // API Key auth
        $apiKey = $request->header('X-API-Key');
        if ($apiKey !== 'skjchat-invoice-2026') {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $request->validate([
            'customerno' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'trans_ref' => 'nullable|string',
            'etd' => 'nullable|string',
            'shipping_ids' => 'nullable|array',
            'shipping_ids.*' => 'integer',
        ]);

        $customerno = strtolower($request->input('customerno'));
        $slipAmount = (float) $request->input('amount');
        $transRef = $request->input('trans_ref', '');
        $shippingIds = $request->input('shipping_ids');

        // === MODE 1: Direct update by shipping_ids (preferred) ===
        if (!empty($shippingIds) && is_array($shippingIds)) {
            $updated = Customershipping::whereIn('id', $shippingIds)
                ->whereIn('pay_status', [1, 5])
                ->update(['pay_status' => 2]);

            Log::info("[CHAT-PAY] Direct update pay_status=2 for {$customerno}, ids=[" . implode(',', $shippingIds) . "], transRef={$transRef}, updated={$updated}");

            return response()->json([
                'success' => true,
                'message' => "อัพเดทสถานะชำระเงินแล้ว: {$customerno} ({$updated} รายการ)",
                'customerno' => $customerno,
                'updated_count' => $updated,
            ]);
        }

        // === MODE 2: Fallback — match by ETD group total ===
        $query = Customershipping::where('customerno', $customerno)
            ->where('excel_status', '1')
            ->whereIn('pay_status', [1, 5]);

        $shippings = $query->get();

        if ($shippings->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => "ไม่พบรายการค้างชำระของ {$customerno}",
            ]);
        }

        $etdGroups = $shippings->groupBy(function ($s) {
            return $s->etd ? $s->etd->format('Y-m-d') : 'unknown';
        });

        $matchedEtd = null;
        $matchedTotal = 0;
        $tolerance = 1.0;

        foreach ($etdGroups as $etdKey => $group) {
            $total = $group->sum(function ($s) {
                $codRate = $s->cod_rate ?? 0.25;
                return $s->import_cost + ($s->cod * $codRate);
            });

            if (abs($total - $slipAmount) <= $tolerance) {
                $matchedEtd = $etdKey;
                $matchedTotal = $total;
                break;
            }
        }

        if (!$matchedEtd) {
            $etdTotals = [];
            foreach ($etdGroups as $etdKey => $group) {
                $total = $group->sum(function ($s) {
                    $codRate = $s->cod_rate ?? 0.25;
                    return $s->import_cost + ($s->cod * $codRate);
                });
                $etdTotals[] = "{$etdKey}: ฿" . number_format($total, 2);
            }

            return response()->json([
                'success' => false,
                'message' => "ยอดสลิป ฿" . number_format($slipAmount, 2) . " ไม่ตรงกับรอบปิดตู้ใดๆ ของ {$customerno}",
                'pending_totals' => $etdTotals,
            ]);
        }

        $matchedGroup = $etdGroups[$matchedEtd];
        $updatedIds = $matchedGroup->pluck('id')->toArray();

        Customershipping::whereIn('id', $updatedIds)
            ->whereIn('pay_status', [1, 5])
            ->update(['pay_status' => 2]);

        Log::info("[CHAT-PAY] Updated pay_status=2 for {$customerno}, ETD={$matchedEtd}, amount=฿{$matchedTotal}, transRef={$transRef}, records=" . count($updatedIds));

        return response()->json([
            'success' => true,
            'message' => "อัพเดทสถานะชำระเงินแล้ว: {$customerno} รอบ {$matchedEtd}",
            'customerno' => $customerno,
            'etd' => $matchedEtd,
            'amount' => $matchedTotal,
            'updated_count' => count($updatedIds),
        ]);
    }
}
