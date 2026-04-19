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
                ->where('customerno', $customerno)
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

        // === MODE 2: Match by ETD group total (ค่านำเข้า) ===
        // Skip MODE 2 if etd=thai-bill (ค่าส่งไทย → ไป MODE 3 ตรง)
        $etdParam = $request->input('etd', '');
        $isThaiShipping = str_contains($etdParam, 'thai-bill');

        $matchedImport = false;
        $importResponse = null;

        if (!$isThaiShipping) {
            DB::transaction(function () use ($customerno, $slipAmount, $transRef, &$matchedImport, &$importResponse) {
                $shippings = Customershipping::where('customerno', $customerno)
                    ->where('excel_status', '1')
                    ->whereIn('pay_status', [1, 5])
                    ->lockForUpdate()
                    ->get();

                if ($shippings->isEmpty()) return;

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

                if ($matchedEtd) {
                    $matchedGroup = $etdGroups[$matchedEtd];
                    $updatedIds = $matchedGroup->pluck('id')->toArray();

                    Customershipping::whereIn('id', $updatedIds)
                        ->whereIn('pay_status', [1, 5])
                        ->update(['pay_status' => 2]);

                    Log::info("[CHAT-PAY] Updated pay_status=2 for {$customerno}, ETD={$matchedEtd}, amount=฿{$matchedTotal}, transRef={$transRef}, records=" . count($updatedIds));

                    $matchedImport = true;
                    $importResponse = [
                        'success' => true,
                        'message' => "อัพเดทสถานะชำระค่านำเข้าแล้ว: {$customerno} รอบ {$matchedEtd}",
                        'customerno' => $customerno,
                        'etd' => $matchedEtd,
                        'amount' => $matchedTotal,
                        'updated_count' => count($updatedIds),
                        'type' => 'import_cost',
                    ];
                }
            });
        }

        if ($matchedImport) {
            return response()->json($importResponse);
        }

        // === MODE 3: Match by thai_bill_amount (ค่าส่งไทย — บิลแยก) ===
        $matchedThai = false;
        $thaiResponse = null;

        DB::transaction(function () use ($customerno, $slipAmount, $transRef, &$matchedThai, &$thaiResponse) {
            $thaiBillShippings = Customershipping::where('customerno', $customerno)
                ->where('excel_status', '1')
                ->where('thai_bill_status', 1)
                ->whereNotNull('thai_bill_amount')
                ->where('thai_bill_amount', '>', 0)
                ->lockForUpdate()
                ->get();

            if ($thaiBillShippings->isEmpty()) return;

            $billGroups = $thaiBillShippings->groupBy('thai_bill_amount');
            $tolerance = 1.0;

            foreach ($billGroups as $billAmount => $group) {
                if (abs((float)$billAmount - $slipAmount) <= $tolerance) {
                    $updatedIds = $group->pluck('id')->toArray();

                    Customershipping::whereIn('id', $updatedIds)
                        ->where('thai_bill_status', 1)
                        ->update(['thai_bill_status' => 2]);

                    Log::info("[CHAT-PAY] Updated thai_bill_status=2 for {$customerno}, amount=฿{$billAmount}, transRef={$transRef}, records=" . count($updatedIds));

                    $matchedThai = true;
                    $thaiResponse = [
                        'success' => true,
                        'message' => "อัพเดทสถานะชำระค่าส่งไทยแล้ว: {$customerno} ฿" . number_format($billAmount, 2),
                        'customerno' => $customerno,
                        'amount' => (float)$billAmount,
                        'updated_count' => count($updatedIds),
                        'type' => 'thai_shipping',
                    ];
                    return;
                }
            }
        });

        if ($matchedThai) {
            return response()->json($thaiResponse);
        }

        // === ไม่ match ทั้ง 2 ประเภท ===
        $etdTotals = [];
        $pendingImports = Customershipping::where('customerno', $customerno)
            ->where('excel_status', '1')
            ->whereIn('pay_status', [1, 5])
            ->get();
        if ($pendingImports->isNotEmpty()) {
            $etdGroups = $pendingImports->groupBy(function ($s) {
                return $s->etd ? $s->etd->format('Y-m-d') : 'unknown';
            });
            foreach ($etdGroups as $etdKey => $group) {
                $total = $group->sum(function ($s) {
                    $codRate = $s->cod_rate ?? 0.25;
                    return $s->import_cost + ($s->cod * $codRate);
                });
                $etdTotals[] = "ค่านำเข้า {$etdKey}: ฿" . number_format($total, 2);
            }
        }
        $pendingThaiBills = Customershipping::where('customerno', $customerno)
            ->where('excel_status', '1')
            ->where('thai_bill_status', 1)
            ->whereNotNull('thai_bill_amount')
            ->where('thai_bill_amount', '>', 0)
            ->get();
        if ($pendingThaiBills->isNotEmpty()) {
            $billGroups = $pendingThaiBills->groupBy('thai_bill_amount');
            foreach ($billGroups as $billAmount => $group) {
                $etdTotals[] = "ค่าส่งไทย: ฿" . number_format($billAmount, 2);
            }
        }

        return response()->json([
            'success' => false,
            'message' => "ยอดสลิป ฿" . number_format($slipAmount, 2) . " ไม่ตรงกับยอดค้างชำระใดๆ ของ {$customerno}",
            'pending_totals' => $etdTotals,
        ]);
    }
}
