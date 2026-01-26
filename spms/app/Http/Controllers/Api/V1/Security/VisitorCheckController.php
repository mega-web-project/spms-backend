<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Visitors;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\RequestCreated;
use App\Http\Resources\VisitResource;

class VisitorCheckController extends Controller
{
    // Visitor check-in
public function checkIn(Request $request)
{
    $request->validate([
        'visitor_id' => 'nullable|exists:visitors,id',
        'full_name' => 'required|string|max:255',
        'ID_number' => 'required|string|max:50',
        'phone_number' => 'required|string|max:20',
        'company' => 'nullable|string|max:255',
        'purpose' => 'required|string|max:255',
        'person_to_visit' => 'required|string|max:255',
        'department' => 'nullable|string|max:255',
        'additional_notes' => 'nullable|string',
    ]);

    DB::beginTransaction();

    try {
        // =========================
        // VISITOR: check if exists by ID_number
        // =========================
        if (!empty($request->visitor_id)) {
            $visitor = Visitors::findOrFail($request->visitor_id);
        } else {
            // Check if a visitor with the same ID_number exists (previously checked out)
            $visitor = Visitors::where('ID_number', $request->ID_number)->first();
            if (!$visitor) {
                // Create new visitor
                $visitor = Visitors::create([
                    'full_name' => $request->full_name,
                    'ID_number' => $request->ID_number,
                    'phone_number' => $request->phone_number,
                    'company' => $request->company ?? null,
                ]);
            }
        }

        // =========================
        // PREVENT DOUBLE CHECK-IN
        // =========================
        $existingVisit = Visit::where('visitor_id', $visitor->id)
            ->where('status', 'checked_in')
            ->first();

        if ($existingVisit) {
            return response()->json([
                'message' => 'Visitor is already checked in.'
            ], 400);
        }

        // =========================
        // CREATE VISIT
        // =========================
        $visit = Visit::create([
            'visit_type' => 'visitors',
            'visitor_id' => $visitor->id,
            'purpose' => $request->purpose,
            'person_to_visit' => $request->person_to_visit,
            'department' => $request->department ?? null,
            'additional_notes' => $request->additional_notes ?? null,
            'checked_in_at' => now(),
            'status' => 'checked_in',
        ]);

        DB::commit();
    } catch (\Throwable $e) {
        DB::rollBack();
        throw $e;
    }

    $visit->load('visitor');
    broadcast(new RequestCreated($visit))->toOthers();

    return response()->json([
        'message' => 'Visitor checked in successfully.',
        'visit' => $visit
    ], 201);
}



    // Visitor check-out
    public function checkOut(Request $request, $visitId)
    {
        // $validated = $request->validate([
        //     'visit_id' => 'required|exists:visits,id',
        // ]);

        $visit = Visit::findOrFail($visitId);

        if ($visit->checked_out_at) {
            return response()->json([
                'message' => 'Visitor already checked out',
            ], 400);
        }

        $visit->update([
            'checked_out_at' => now(),
            'status' => 'checked_out',
        ]);

        $visit->load('visitor');
        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Visitor checked out successfully',
            'visit' => new VisitResource($visit),
        ]);
    }

    //get checked in visitors
    public function getCheckedInVisitors()
{
    // Get visits that are currently checked in, with visitor info
    $checkedInVisitors = Visit::with('visitor') // eager load visitor
        ->where('visit_type', 'visitors')
        ->where('status', 'checked_in') // or whereNull('checked_out_at')
        ->latest('checked_in_at')
        ->get();

    // Map into a clean structure
    $data = $checkedInVisitors->map(function ($visit) {
        return [
            'visit_id' => $visit->id,
            'purpose' => $visit->purpose,
            'department' => $visit->department,
            'person_to_visit' => $visit->person_to_visit,
            'checked_in_at' => $visit->checked_in_at,
            'status' => $visit->status,
            'visitor' => [
                'id' => $visit->visitor->id,
                'full_name' => $visit->visitor->full_name,
                'ID_number' => $visit->visitor->ID_number,
                'phone_number' => $visit->visitor->phone_number,
                'company' => $visit->visitor->company,
            ]
        ];
    });

    return response()->json([
        'count' => $data->count(),
        'data' => $data,
    ]);
}

}

