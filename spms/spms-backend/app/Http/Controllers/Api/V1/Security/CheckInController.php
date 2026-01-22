<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\GoodsItem;
use App\Models\Visit;
use Illuminate\Http\Request;
use App\Http\Resources\GoodsItemResource;

class GoodsTrackingController extends Controller
{
    // List all goods items for a specific visit
    public function index($visitId)
    {
        $goodsItems = GoodsItem::where('visit_id', $visitId)->get();
        return response()->json(GoodsItemResource::collection($goodsItems), 200);
    }

    // Create a new goods item
    public function store(Request $request)
    {
        $validated = $request->validate([
            'visit_id' => 'required|exists:visits,id',
            'description' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'reference_doc' => 'nullable|string|max:255',
        ]);

        $goodsItem = GoodsItem::create($validated);

        return response()->json([
            'message' => 'Goods item created successfully',
            'goods_item' => new GoodsItemResource($goodsItem)
        ], 201);
    }

    // Update an existing goods item
    public function update(Request $request, $id)
    {
        $goodsItem = GoodsItem::findOrFail($id);

        $validated = $request->validate([
            'description' => 'sometimes|required|string|max:255',
            'quantity' => 'sometimes|required|integer|min:1',
            'unit' => 'nullable|string|max:50',
            'reference_doc' => 'nullable|string|max:255',
        ]);

        $goodsItem->update($validated);

        return response()->json([
            'message' => 'Goods item updated successfully',
            'goods_item' => new GoodsItemResource($goodsItem)
        ], 200);
    }

    // Delete a goods item
    public function destroy($id)
    {
        $goodsItem = GoodsItem::findOrFail($id);
        $goodsItem->delete();

        return response()->json([
            'message' => 'Goods item deleted successfully'
        ], 200);
    }
}