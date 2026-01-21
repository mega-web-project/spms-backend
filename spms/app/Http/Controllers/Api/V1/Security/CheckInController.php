<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use Illuminate\Http\Request;
use App\Events\RequestCreated;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\VisitResource;

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
        ]);

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

        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Checked in successfully',
            'visit' => new VisitResource($visit),
            
        ], 201);
    }
}
