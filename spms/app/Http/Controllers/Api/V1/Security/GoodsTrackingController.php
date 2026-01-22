<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Visit;
use App\Models\GoodsItem;
use App\Events\RequestCreated;
use App\Http\Resources\VisitResource;
use App\Http\Resources\GoodsItemResource;

class GoodsTrackingController extends Controller
{
    // List shipments (visits that have goods items) + stats
    public function index(Request $request)
    {
        $query = Visit::whereHas('goods_items')->with(['goods_items', 'vehicle', 'driver', 'visitor'])->latest();

        if ($request->filled('visit_type')) {
            $query->where('visit_type', $request->visit_type);
        }

        $visits = $query->get();

        $total = $visits->count();
        $pending = $visits->where('goods_verified', false)->count();
        $verified = $visits->where('goods_verified', true)->count();
        $discrepancies = $visits->where('has_discrepancies', true)->count();

        return response()->json([
            'total' => $total,
            'pending_verification' => $pending,
            'verified' => $verified,
            'discrepancies' => $discrepancies,
            'records' => VisitResource::collection($visits),
        ]);
    }

    // Add goods items to an existing visit (after check-in)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:50',
            'items.*.reference_doc' => 'nullable|string|max:255',
        ]);

        $visit = Visit::findOrFail($validated['visit_id']);

        DB::transaction(function () use ($validated, $visit) {
            foreach ($validated['items'] as $item) {
                $visit->goods_items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? 'pcs',
                    'reference_doc' => $item['reference_doc'] ?? null,
                ]);
            }
        });

        $visit->load('goods_items', 'vehicle', 'driver', 'visitor');
        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Goods items added successfully',
            'visit' => new VisitResource($visit),
        ], 201);
    }

    // Show a single visit with goods items
    public function show($id)
    {
        $visit = Visit::with(['goods_items', 'vehicle', 'driver', 'visitor'])->findOrFail($id);
        return response()->json(new VisitResource($visit));
    }

    // Verify goods for a visit (optional endpoint; checkout also updates these)
    public function verify(Request $request)
    {
        $validated = $request->validate([
            'id' => 'required|exists:visits,id',
            'goods_verified' => 'required|boolean',
            'weight_checked' => 'required|boolean',
            'photo_documented' => 'required|boolean',
            'has_discrepancies' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        $visit = Visit::findOrFail($validated['id']);

        $visit->update([
            'goods_verified' => $validated['goods_verified'],
            'weight_checked' => $validated['weight_checked'],
            'photo_documented' => $validated['photo_documented'],
            'has_discrepancies' => $validated['has_discrepancies'] ?? false,
            'notes' => $validated['notes'] ?? null,
        ]);

        $visit->load('goods_items', 'vehicle', 'driver', 'visitor');
        broadcast(new RequestCreated($visit))->toOthers();

        return response()->json([
            'message' => 'Goods verification updated',
            'visit' => new VisitResource($visit),
        ]);
    }

    // Delete a single goods item
    public function destroyItem($id)
    {
        $item = GoodsItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Goods item removed'], 200);
    }
}