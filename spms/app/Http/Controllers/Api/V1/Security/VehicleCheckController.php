<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Vehicles;
use App\Models\Drivers;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\RequestCreated;
use App\Http\Resources\VisitResource;

class VehicleCheckController extends Controller
{
    /**
     * VEHICLE CHECK-IN
     */
    public function checkIn(Request $request)
    {
        $validated = $request->validate([
            // vehicle
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'plate_number' => 'required_if:vehicle_id,null|string|unique:vehicles,plate_number',
            'vehicle_type' => 'required_if:vehicle_id,null|string',
            'make' => 'required_if:vehicle_id,null|string',
            'model' => 'required_if:vehicle_id,null|string',
            'color' => 'required_if:vehicle_id,null|string',
            'vehicle_company' => 'nullable|string',
            'purpose' => 'nullable|string',
            'assigned_bay' => 'nullable|string',

            // driver
            'driver_id' => 'nullable|exists:drivers,id',
            'driver_name' => 'required_if:driver_id,null|string',
            'driver_phone' => 'required_if:driver_id,null|string|unique:drivers,phone',
            'driver_company' => 'nullable|string',
            'driver_license' => 'nullable|string',

            // goods
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.reference_doc' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {

            /** =========================
             * DRIVER
             ========================== */
            if (isset($validated['driver_id'])) {
                $driver = Drivers::findOrFail($validated['driver_id']);
            } else {
                $driver = Drivers::create([
                    'full_name' => $validated['driver_name'],
                    'phone' => $validated['driver_phone'],
                    'company' => $validated['driver_company'] ?? null,
                    'license_number' => $validated['driver_license'] ?? null,
                ]);
            }

            /** =========================
             * VEHICLE
             ========================== */
            if (isset($validated['vehicle_id'])) {
                $vehicle = Vehicles::findOrFail($validated['vehicle_id']);
            } else {
                $vehicle = Vehicles::create([
                    'driver_id' => $driver->id,
                    'plate_number' => $validated['plate_number'],
                    'vehicle_type' => $validated['vehicle_type'],
                    'make' => $validated['make'],
                    'model' => $validated['model'],
                    'color' => $validated['color'],
                    'company' => $validated['vehicle_company'] ?? null,
                ]);
            }

            /** =========================
             * PREVENT DOUBLE CHECK-IN
             ========================== */
            $alreadyInside = Visit::where('vehicle_id', $vehicle->id)
                ->where('status', 'checked_in')
                ->first();

            if ($alreadyInside) {
                return response()->json([
                    'message' => 'This vehicle is already checked in.'
                ], 400);
            }

            /** =========================
             * VISIT
            */
            $visit = Visit::create([
                'visit_type' => 'vehicles',
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'purpose' => $validated['purpose'] ?? null,
                'assigned_bay' => $validated['assigned_bay'] ?? null,
                'checked_in_at' => now(),
                'status' => 'checked_in',
            ]);

            /** =========================
             * GOODS ITEMS
             */
            if (!empty($validated['items'])) {
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

        $visit->load('vehicle', 'driver', 'goods_items');
        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Vehicle checked in successfully',
            'visit' => new VisitResource($visit),
        ], 201);
    }


    /**
     * VEHICLE CHECK-OUT
     */
    public function checkOut(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'goods_verified' => 'required|boolean',
            'weight_checked' => 'required|boolean',
            'photo_documented' => 'required|boolean',
            'has_discrepancies' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $visit = Visit::findOrFail($validated['visit_id']);

        if ($visit->checked_out_at) {
            return response()->json([
                'message' => 'Vehicle already checked out',
            ], 400);
        }

        $visit->update([
            'goods_verified' => $validated['goods_verified'],
            'weight_checked' => $validated['weight_checked'],
            'photo_documented' => $validated['photo_documented'],
            'has_discrepancies' => $validated['has_discrepancies'] ?? false,
            'notes' => $validated['notes'] ?? null,
            'checked_out_at' => now(),
            'status' => 'checked_out',
        ]);

        $visit->load('vehicle', 'driver', 'goods_items');
        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Vehicle checked out successfully',
            'visit' => new VisitResource($visit),
        ]);
    }
}
