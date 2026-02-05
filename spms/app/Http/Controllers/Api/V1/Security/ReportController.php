<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Vehicles;
use App\Models\Visitors;
use App\Events\RequestCreated;
use App\Models\GoodsItem;
use App\Models\Visit;
use App\Models\Alert;
//use App\Models\Alert;

class ReportController extends Controller
{
    //function to get statistics for total vehicles, visitors, gooditems in the last x days
    public function getStatistics(Request $request)
    {
        $days = $request->query('days', 7); // default to last 7 days
        $startDate = Carbon::now()->subDays($days);

        $totalVehiclesOnsite = Visit::where('visit_type', 'vehicles')
        ->where('status','checked_in')
        ->whereNull('checked_out_at')
        ->count();

        $totalVisitorsOnSite = Visit::where('visit_type', 'visitors')
        ->where('status', 'checked_in')
        ->whereNull('checked_out_at')
        ->count();
        $totalGoodsItems = GoodsItem::where('created_at', '>=', $startDate)->count();
    
        $pendingCheckouts = Visit::where('status', 'checked_in')
        ->whereNull('checked_out_at')
        ->count();
        //$totalAlerts = Alert::where('created_at', '>=', $startDate)->count();
        $totalCheckouts = Visit::where('checked_out_at', '>=', $startDate)->count();
        $activeAlerts = Alert::where('resolved', false)->count();

        return response()->json([
            'VehiclesInPeriod' => $totalVehiclesOnsite ,
            'VisitorsInPeriod' => $totalVisitorsOnSite,
            'TotalGoodsItems' => $totalGoodsItems,
            'PendingCheckouts' => $pendingCheckouts,
            //'total_alerts' => $totalAlerts
            'TotalCheckouts' => $totalCheckouts,
            'ActiveAlerts' => $activeAlerts

        ]);
    }
    
}
