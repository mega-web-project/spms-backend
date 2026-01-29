<?php

namespace App\Http\Controllers\Api\V1\Security;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Events\RequestCreated;
use Illuminate\Http\Request;
use App\Http\Resources\VisitResource;

class CheckOutHistoryController extends Controller
{
  

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
                    'id'=>$visit->id
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
                    'id'=>$visit->id
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