<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\GoodsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class CheckInController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validate the combined data from all 4 steps
        $validated = $request->validate([
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_id' => 'required|exists:drivers,id',
            'purpose' => 'required|string',
            'assigned_bay' => 'nullable|string',
            'goods_items' => 'array', // From Step 3: Goods Declaration
            'goods_items.*.description' => 'required|string',
            'goods_items.*.quantity' => 'required|integer',
            'goods_items.*.reference_doc' => 'nullable|string',
            'goods_items.*.unit' => 'nullable|string'
        ]);

        // 2. Use a Transaction to ensure everything saves correctly
        return DB::transaction(function () use ($validated, $request) {
            
            // Create the Visit record
            $visit = Visit::create([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'purpose' => $validated['purpose'],
                'assigned_bay' => $validated['assigned_bay'] ?? 'Auto-assign',
                'status' => 'checked_in'
            ]);

            // Save the Goods Items

            $items = $request->input('goods_items', []);

            foreach ($items as $item) {
                $visit->goods_items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'pcs',
                    'reference_doc' => $item['reference_doc'] ?? null
                ]);
            }

            return response()->json([
                'message' => 'Vehicle checked in successfully',
                'visit_id' => $visit->id
            ], 201);
        });
    }
}