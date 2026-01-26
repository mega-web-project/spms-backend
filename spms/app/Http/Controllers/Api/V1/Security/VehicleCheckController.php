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
        $request->validate([
            // Vehicle
            'vehicle_id' => 'nullable|exists:vehicles,id',
            'plate_number' => 'required|string',
            'vehicle_type' => 'required|string',
            'make' => 'required|string',
            'model' => 'required|string',
            'color' => 'required|string',
            'vehicle_company' => 'nullable|string',

            // Driver
            'driver_id' => 'nullable|exists:drivers,id',
            'driver_name' => 'required|string',
            'driver_phone' => 'required|string',
            'driver_company' => 'nullable|string',
            'driver_license' => 'nullable|string',

            // Visit
            'purpose' => 'nullable|string',
            'assigned_bay' => 'nullable|string',

            // Goods
            'items' => 'nullable|array',
            'items.*.description' => 'required_with:items|string',
            'items.*.quantity' => 'required_with:items|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.reference_doc' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();

        try {
            // =========================
            // DRIVER: check if exists by phone
            // =========================
            if (!empty($request->driver_id)) {
                $driver = Drivers::findOrFail($request->driver_id);
            } else {
                $driver = Drivers::where('phone', $request->driver_phone)->first();
                if (!$driver) {
                    $driver = Drivers::create([
                        'full_name' => $request->driver_name,
                        'phone' => $request->driver_phone,
                        'company' => $request->driver_company ?? null,
                        'license_number' => $request->driver_license ?? null,
                    ]);
                }
            }

            // =========================
            // VEHICLE: check if exists by plate_number
            // =========================
            if (!empty($request->vehicle_id)) {
                $vehicle = Vehicles::findOrFail($request->vehicle_id);
            } else {
                $vehicle = Vehicles::where('plate_number', $request->plate_number)->first();
                if (!$vehicle) {
                    $vehicle = Vehicles::create([
                        'driver_id' => $driver->id,
                        'plate_number' => $request->plate_number,
                        'vehicle_type' => $request->vehicle_type,
                        'make' => $request->make,
                        'model' => $request->model,
                        'color' => $request->color,
                        'company' => $request->vehicle_company ?? null,
                    ]);
                }
            }

            // =========================
            // PREVENT DOUBLE CHECK-IN
            // =========================
            $alreadyInside = Visit::where('vehicle_id', $vehicle->id)
                ->where('status', 'checked_in')
                ->first();

            if ($alreadyInside) {
                return response()->json([
                    'message' => 'This vehicle is already checked in.'
                ], 400);
            }

            // =========================
            // CREATE VISIT
            // =========================
            $visit = Visit::create([
                'visit_type' => 'vehicles',
                'vehicle_id' => $vehicle->id,
                'driver_id' => $driver->id,
                'purpose' => $request->purpose ?? null,
                'assigned_bay' => $request->assigned_bay ?? null,
                'checked_in_at' => now(),
                'status' => 'checked_in',
            ]);

            // =========================
            // GOODS ITEMS
            // =========================
            if (!empty($request->items)) {
                foreach ($request->items as $item) {
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
    public function checkOut(Request $request, $visitId)
    {
        $validated = $request->validate([
            'goods_verified' => 'required|boolean',
            'weight_checked' => 'required|boolean',
            'photo_documented' => 'required|boolean',
            'has_discrepancies' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $visit = Visit::findOrFail($visitId);

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

    /**
     * Get currently checked-in vehicles
     */
    public function getCheckedInVehicles()
    {
        $visits = Visit::with(['vehicle', 'driver', 'goods_items'])
            ->where('visit_type', 'vehicles')
            ->where('status', 'checked_in')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'checked_in_vehicles' => VisitResource::collection($visits),
        ]);
    }

    /**
     * Get all vehicle check-ins (history)
     */
    public function getAllCheckinVehicles()
    {
        $visits = Visit::with(['vehicle', 'driver', 'goods_items'])
            ->where('visit_type', 'vehicles')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => VisitResource::collection($visits),
        ]);
    }
}