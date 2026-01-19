<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Models\Visit;
use App\Models\GoodsItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CheckOutController extends Controller
{
    public function update(Request $request, $id)
    {
        $visit = Visit::findOrFail($id);

        $validated = $request->validate([
            'goods_verified' => 'required|boolean',
            'weight_checked' => 'required|boolean',
            'photo_documented' => 'required|boolean',
            'notes' => 'nullable|string'
        ]);

        $visit->update([
            'goods_verified' => $validated['goods_verified'],
            'weight_checked' => $validated['weight_checked'],
            'photo_documented' => $validated['photo_documented'],
            'notes' => $validated['notes'] ?? null,
            'check_out_at' => now(),
            'status' => 'completed'
        ]);

        return response()->json(['message' => 'Vehicle checked out successfully']);
    }
}
