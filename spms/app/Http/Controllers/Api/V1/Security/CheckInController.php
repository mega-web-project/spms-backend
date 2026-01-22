<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use App\Events\RequestCreated;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VisitResource;
use App\Models\GoodsItem;

class CheckInController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_type' => 'required|in:visitors,vehicles',

            // visitor
            'visitor_id' => 'required_if:visit_type,visitors|exists:visitors,id',
            'purpose_of_visit' => 'nullable|string',
            'person_to_visit' => 'nullable|string',
            'department' => 'nullable|string',
            'additional_notes' => 'nullable|string',

            // vehicle
            'vehicle_id' => 'required_if:visit_type,vehicles|exists:vehicles,id',
            'driver_id' => 'required_if:visit_type,vehicles|exists:drivers,id',
            'purpose' => 'nullable|string',
            'assigned_bay' => 'nullable|string',

            // goods items (optional during vehicle check-in)
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.reference_doc' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $visit = Visit::create([
                'visit_type' => $validated['visit_type'],

                'visitor_id' => $validated['visitor_id'] ?? null,
                'vehicle_id' => $validated['vehicle_id'] ?? null,
                'driver_id' => $validated['driver_id'] ?? null,

                'purpose_of_visit' => $validated['purpose_of_visit'] ?? null,
                'person_to_visit' => $validated['person_to_visit'] ?? null,
                'department' => $validated['department'] ?? null,
                'additional_notes' => $validated['additional_notes'] ?? null,

                'purpose' => $validated['purpose'] ?? null,
                'assigned_bay' => $validated['assigned_bay'] ?? null,

                'checked_in_at' => now(),
                'status' => 'checked_in'
            ]);

            // create goods items if provided (vehicles)
            if (!empty($validated['items']) && $visit->visit_type === 'vehicles') {
                foreach ($validated['items'] as $item) {
                    $visit->goods_items()->create([
                        'description' => $item['description'],
                        'quantity' => $item['quantity'],
                        'unit' => $item['unit'] ?? 'pcs',
                        'reference_doc' => $item['reference_doc'] ?? null,
                    ]);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $visit->load('goods_items', 'vehicle', 'driver', 'visitor');

        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Checked in successfully',
            'visit' => new VisitResource($visit),

        ], 201);
    }
}