<?php

namespace App\Http\Controllers\Api\V1\Security;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Events\RequestCreated;
use Illuminate\Http\Request;
use App\Http\Resources\VisitResource;

class CheckOutController extends Controller
{
    /**
     * CHECK OUT (visitor OR vehicle)
     */
    public function checkout(Request $request)
    {

        $request->validate([
            'visit_type' => 'required|in:visitors,vehicles',
        ]);

         $visit = Visit::findOrFail($request->id);

        if ($visit->checked_out_at) {
        return response()->json([
            'message' => 'This ' . rtrim($request->visit_type, 's') . ' has already been checked out.',
        ], 400);
            }

        if ($request->visit_type === 'vehicles') {

            $validated = $request->validate([
                'id' => 'required|exists:visits,id',
                'goods_verified' => 'required|boolean',
                'weight_checked' => 'required|boolean',
                'photo_documented' => 'required|boolean',
                'has_discrepancies' => 'nullable|boolean',
                'notes' => 'nullable|string',
            ]);

           
            $visit->update([
                'goods_verified' => $validated['goods_verified'],
                'weight_checked' => $validated['weight_checked'],
                'photo_documented' => $validated['photo_documented'],
                'notes' => $validated['notes'] ?? null,
                'checked_out_at' => now(),
                'status' => 'checked_out',
                'has_discrepancies' => $validated['has_discrepancies'] ?? false,
            ]);

            broadcast(new RequestCreated($visit))->toOthers();

            return response()->json([
                'message' => 'Vehicle checked out successfully',
                'data' => new VisitResource($visit)
            ]);
        }

        if ($request->visit_type === 'visitors') {
            $request->validate([
                'id' => 'required|exists:visits,id',
            ]);

            $visitor = Visit::findOrFail($request->id);
            $visitor->update([
                'checked_out_at' => now(),
                'status' => 'checked_out',
            ]);

            broadcast(new RequestCreated($visitor))->toOthers();



            return response()->json([
                'message' => 'Visitor checked out successfully',
                'data' => new VisitResource($visitor)
            ]);
        }
    }


    /**
     * CHECKOUT HISTORY (combined)
     */
public function history()
{
    // VEHICLE VISITS
    $vehicleVisits = Visit::where('visit_type', 'vehicles')
        ->whereNotNull('checked_out_at')
        ->with(['vehicle', 'driver'])
        ->get()
        ->map(function ($visit) {
            $duration = null;
            if ($visit->checked_in_at && $visit->checked_out_at) {
                $duration = Carbon::parse($visit->checked_in_at)
                    ->diffForHumans(Carbon::parse($visit->checked_out_at), [
                        'parts' => 2,   // show 2 largest units (hours + minutes)
                        'short' => true, // short format
                        'syntax' => Carbon::DIFF_ABSOLUTE
                    ]);
            }

            return [
                'visit_type' => 'vehicles',
                'name' => $visit->driver?->full_name,
                'plate_number' => $visit->vehicle?->plate_number,
                'company' => $visit->vehicle?->company,
                'purpose' => $visit->purpose,
                'checked_in' => $visit->checked_in_at,
                'checked_out' => $visit->checked_out_at,
                'duration' => $duration,
                'status' => $visit->status,
                'has_discrepancies' => $visit->has_discrepancies,
            ];
        });

    // VISITOR VISITS
    $visitorVisits = Visit::where('visit_type', 'visitors')
        ->whereNotNull('checked_out_at')
        ->with('visitor')
        ->get()
        ->map(function ($visit) {
            $duration = null;
            if ($visit->checked_in_at && $visit->checked_out_at) {
                $duration = Carbon::parse($visit->checked_in_at)
                    ->diffForHumans(Carbon::parse($visit->checked_out_at), [
                        'parts' => 2,
                        'short' => true,
                        'syntax' => Carbon::DIFF_ABSOLUTE
                    ]);
            }

            return [
                'visit_type' => 'visitors',
                'name' => $visit->visitor?->full_name,
                'company' => $visit->visitor?->company,
                'purpose' => $visit->purpose_of_visit,
                'checked_in' => $visit->checked_in_at,
                'checked_out' => $visit->checked_out_at,
                'duration' => $duration,
                'status' => $visit->status,
            ];
        });

    $vehicleVisits = collect($vehicleVisits);
    $visitorVisits = collect($visitorVisits);

    return response()->json([
        'total' => $vehicleVisits->count() + $visitorVisits->count(),
        'vehicle_records' => $vehicleVisits->count(),
        'visitor_records' => $visitorVisits->count(),
        'records' => $vehicleVisits
            ->merge($visitorVisits)
            ->sortByDesc('checked_out')
            ->values(),
    ]);
}

}