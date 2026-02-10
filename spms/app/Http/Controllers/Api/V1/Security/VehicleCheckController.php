<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Vehicles;
use App\Models\Drivers;
use App\Models\Visit;
use App\Models\Alert;
use App\Models\GoodsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\RequestCreated;
use App\Http\Resources\VisitResource;

class VehicleCheckController extends Controller
{
    /**
     * VEHICLE CHECK-IN
     */
    // public function checkIn(Request $request)
    // {
    //     $request->validate([
    //         // Vehicle
    //         'vehicle_id' => 'nullable|exists:vehicles,id',
    //         'plate_number' => 'required|string',
    //         'vehicle_type' => 'required|string',
    //         'make' => 'string',
    //         'model' => 'string',
    //         'color' => 'string',
    //         'vehicle_company' => 'nullable|string',

    //         // Driver
    //         'driver_id' => 'nullable|exists:drivers,id',
    //         'driver_name' => 'required|string',
    //         'driver_phone' => 'required|string',
    //         'driver_company' => 'nullable|string',
    //         'driver_license' => 'nullable|string',

    //         // Visit
    //         'purpose' => 'nullable|string',
    //         'assigned_bay' => 'nullable|string',

    //         // Goods
    //         'items' => 'nullable|array',
    //         'items.*.description' => 'required_with:items|string',
    //         'items.*.quantity' => 'required_with:items|integer|min:1',
    //         'items.*.unit' => 'nullable|string|max:50',
    //         'items.*.reference_doc' => 'nullable|string|max:255',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         // =========================
    //         // DRIVER: check if exists by phone
    //         // =========================
    //         if (!empty($request->driver_id)) {
    //             $driver = Drivers::findOrFail($request->driver_id);
    //         } else {
    //             $driver = Drivers::where('phone', $request->driver_phone)->first();
    //             if (!$driver) {
    //                 $driver = Drivers::create([
    //                     'full_name' => $request->driver_name,
    //                     'phone' => $request->driver_phone,
    //                     'company' => $request->driver_company ?? null,
    //                     'license_number' => $request->driver_license ?? null,
    //                 ]);
    //             }
    //         }

    //         // =========================
    //         // VEHICLE: check if exists by plate_number
    //         // =========================
    //         if (!empty($request->vehicle_id)) {
    //             $vehicle = Vehicles::findOrFail($request->vehicle_id);
    //         } else {
    //             $vehicle = Vehicles::where('plate_number', $request->plate_number)->first();
    //             if (!$vehicle) {
    //                 $vehicle = Vehicles::create([
    //                     'driver_id' => $driver->id,
    //                     'plate_number' => $request->plate_number,
    //                     'vehicle_type' => $request->vehicle_type,
    //                     'make' => $request->make,
    //                     'model' => $request->model,
    //                     'color' => $request->color,
    //                     'company' => $request->vehicle_company ?? null,
    //                 ]);
    //             }
    //         }

    //         // =========================
    //         // PREVENT DOUBLE CHECK-IN
    //         // =========================
    //         $alreadyInside = Visit::where('vehicle_id', $vehicle->id)
    //             ->where('status', 'checked_in')
    //             ->first();

    //         if ($alreadyInside) {
    //             return response()->json([
    //                 'message' => 'This vehicle is already checked in.'
    //             ], 400);
    //         }

    //         // =========================
    //         // CREATE VISIT
    //         // =========================
    //         $visit = Visit::create([
    //             'visit_type' => 'vehicles',
    //             'vehicle_id' => $vehicle->id,
    //             'driver_id' => $driver->id,
    //             'purpose' => $request->purpose ?? null,
    //             'assigned_bay' => $request->assigned_bay ?? null,
    //             'checked_in_at' => now(),
    //             'status' => 'checked_in',
    //         ]);

    //         // =========================
    //         // GOODS ITEMS
    //         // =========================
    //         if (!empty($request->items)) {
    //             foreach ($request->items as $item) {
    //                 $visit->goods_items()->create([
    //                     'description' => $item['description'],
    //                     'quantity' => $item['quantity'],
    //                     'unit' => $item['unit'] ?? 'pcs',
    //                     'reference_doc' => $item['reference_doc'] ?? null,
    //                 ]);
    //             }
    //         }

    //         DB::commit();
    //     } catch (\Throwable $e) {
    //         DB::rollBack();
    //         throw $e;
    //     }

    //     $visit->load('vehicle', 'driver', 'goods_items');
    //     broadcast(new RequestCreated($visit))->toOthers();

    //     return response()->json([
    //         'message' => 'Vehicle checked in successfully',
    //         'visit' => new VisitResource($visit),
    //     ], 201);
    // }
public function checkIn(Request $request)
{
    // =========================
    // NORMALIZE FRONTEND VALUES
    // (Fixes "N/A" bigint error)
    // =========================
    $request->merge([
        'vehicle_id' => ($request->vehicle_id === 'N/A' || $request->vehicle_id === '') 
            ? null 
            : $request->vehicle_id,

        'driver_id' => ($request->driver_id === 'N/A' || $request->driver_id === '') 
            ? null 
            : $request->driver_id,
    ]);

    // =========================
    // VALIDATION
    // =========================
    $request->validate([
        // Vehicle
        'vehicle_id' => 'nullable|integer|exists:vehicles,id',
        'plate_number' => 'required_without:vehicle_id|string',
        'vehicle_type' => 'required_without:vehicle_id|string',
        'make' => 'nullable|string',
        'model' => 'nullable|string',
        'color' => 'nullable|string',
        'vehicle_company' => 'nullable|string',

        // Driver
        'driver_id' => 'nullable|integer|exists:drivers,id',
        'driver_name' => 'required_without:driver_id|string',
        'driver_phone' => 'required_without:driver_id|string',
        // 'driver_company' => 'nullable|string',
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
        // DRIVER: auto-select or create
        // =========================
        if ($request->filled('driver_id')) {
            $driver = Drivers::findOrFail($request->driver_id);
        } else {
            $driver = Drivers::where('phone', $request->driver_phone)->first();

            if (!$driver) {
                $driver = Drivers::create([
                    'full_name' => $request->driver_name,   
                    'phone' => $request->driver_phone,
                    // 'company' => $request->driver_company,
                    'license_number' => $request->driver_license,
                ]);
            }
        }

        // =========================
        // VEHICLE: auto-select or create
        // =========================
        if ($request->filled('vehicle_id')) {
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
                    'company' => $request->vehicle_company,
                ]);
            }
        }

        // =========================
        // PREVENT DOUBLE CHECK-IN
        // =========================
        $alreadyInside = Visit::where('vehicle_id', $vehicle->id)
            ->where('status', 'checked_in')
            ->exists();

        if ($alreadyInside) {
            DB::rollBack();

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
            'purpose' => $request->purpose,
            'assigned_bay' => $request->assigned_bay,
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

    // =========================
    // LOAD & BROADCAST
    // =========================
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
            'notes' => 'nullable|string',
            'items' => 'nullable|array',
            'items.*.id' => 'required_with:items|exists:goods_items,id',
            'items.*.has_discrepancy' => 'required_with:items|boolean',
            'items.*.discrepancy_note' => 'nullable|string',
        ]);

        $visit = Visit::findOrFail($visitId);

        if ($visit->checked_out_at) {
            return response()->json([
                'message' => 'Vehicle already checked out',
            ], 400);
        }

        $items = collect($validated['items'] ?? []);

        if ($items->isNotEmpty()) {
            $itemIds = $items->pluck('id')->unique()->values();
            $validItemIds = $visit->goods_items()
                ->whereIn('id', $itemIds)
                ->pluck('id');

            if ($validItemIds->count() !== $itemIds->count()) {
                return response()->json([
                    'message' => 'One or more items do not belong to this visit.',
                ], 422);
            }
        }

        $discrepancyCount = 0;
        $hasDiscrepancies = false;

        DB::transaction(function () use ($visit, $items, $validated, &$discrepancyCount, &$hasDiscrepancies) {
            if ($items->isNotEmpty()) {
                foreach ($items as $item) {
                    $note = $item['has_discrepancy'] ? ($item['discrepancy_note'] ?? null) : null;

                    GoodsItem::where('id', $item['id'])->update([
                        'has_discrepancy' => $item['has_discrepancy'],
                        'discrepancy_note' => $note,
                    ]);
                }
            }

            $discrepancyCount = $visit->goods_items()
                ->where('has_discrepancy', true)
                ->count();
            $hasDiscrepancies = $discrepancyCount > 0;

            $visit->update([
                'goods_verified' => $validated['goods_verified'],
                'weight_checked' => $validated['weight_checked'],
                'photo_documented' => $validated['photo_documented'],
                'has_discrepancies' => $hasDiscrepancies,
                'notes' => $validated['notes'] ?? null,
                'checked_out_at' => now(),
                'status' => 'checked_out',
            ]);
        });

        if ($hasDiscrepancies) {
            $visit->loadMissing('vehicle');
            $entityType = $visit->vehicle_id ? 'vehicle' : 'visit';
            $entityId = $visit->vehicle_id ?? $visit->id;
            $message = $this->buildDiscrepancyMessage($visit, $discrepancyCount);

            $alert = Alert::createIfNotExists([
                'type' => 'discrepancy',
                'severity' => 'medium',
                'message' => $message,
                'entity_type' => $entityType,
                'entity_id' => $entityId,
                'resolved' => false,
            ]);

            if ($alert->message !== $message) {
                $alert->update(['message' => $message]);
            }
        }

        $visit->load('vehicle', 'driver', 'goods_items');
        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Vehicle checked out successfully',
            'visit' => new VisitResource($visit),
        ]);
    }

    private function buildDiscrepancyMessage(Visit $visit, int $count): string
    {
        $plate = optional($visit->vehicle)->plate_number;
        $itemLabel = $count === 1 ? 'item' : 'items';

        return $plate
            ? "Goods discrepancy reported for vehicle {$plate} ({$count} {$itemLabel})"
            : "Goods discrepancy reported for visit {$visit->id} ({$count} {$itemLabel})";
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
            ->where('status', 'checked_in')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => VisitResource::collection($visits),
        ]);
    }
}
