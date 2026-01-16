<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\GoodsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            'goods' => 'array', // From Step 3: Goods Declaration
            'goods.*.item_name' => 'required|string',
            'goods.*.quantity' => 'required|integer',
            'reference_document' => 'nullable|string'
        ]);

        // 2. Use a Transaction to ensure everything saves correctly
        return DB::transaction(function () use ($validated) {
            
            // Create the Visit record
            $visit = Visit::create([
                'vehicle_id' => $validated['vehicle_id'],
                'driver_id' => $validated['driver_id'],
                'purpose' => $validated['purpose'],
                'assigned_bay' => $validated['assigned_bay'] ?? 'Auto-assign',
                'status' => 'checked_in'
            ]);

            // Save the Goods Items
            foreach ($validated['goods'] as $item) {
                $visit->goodsItems()->create([
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'reference_doc' => $validated['reference_document']
                ]);
            }

            return response()->json([
                'message' => 'Vehicle checked in successfully',
                'visit_id' => $visit->id
            ], 201);
        });
    }
}