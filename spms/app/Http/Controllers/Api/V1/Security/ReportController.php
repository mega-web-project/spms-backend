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
//use App\Models\Alert;

class ReportController extends Controller
{
    //function to get statistics for total vehicles, visitors, gooditems in the last x days
    public function getStatistics(Request $request)
    {
        $days = $request->query('days', 7); // default to last 7 days
        $startDate = Carbon::now()->subDays($days);

        $totalVehicles = Vehicles::where('created_at', '>=', $startDate)->count();
        $totalVisitors = Visitors::where('created_at', '>=', $startDate)->count();
        $totalGoodsItems = GoodsItem::where('created_at', '>=', $startDate)->count();
        $totalVisits = Visit::where('created_at', '>=', $startDate)->count();
        //$totalAlerts = Alert::where('created_at', '>=', $startDate)->count();
        $totalCheckouts = Visit::where('checked_out_at', '>=', $startDate)->count();

        return response()->json([
            'Vehicles In Period' => $totalVehicles,
            'Visitors In Period' => $totalVisitors,
            'Total Goods Items' => $totalGoodsItems,
            'Total Visits' => $totalVisits,
            //'total_alerts' => $totalAlerts
            'Total Checkouts' => $totalCheckouts

        ]);
    }
    
}