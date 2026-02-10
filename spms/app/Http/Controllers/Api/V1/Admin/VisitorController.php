<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Visitors;
use App\Models\Visit;
use App\Models\Vehicle;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class VisitorController extends Controller
{
    // list visitors
    public function index()
    {
        return response()->json(Visitors::latest()->get());
    }

    // create visitor (identity only)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'ID_number' => 'required|string|unique:visitors,ID_number',
            'phone_number' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'members' => 'nullable|array',
            'members.*.name' => 'required_with:members|string|max:255',
            'members.*.phone_number' => 'required_with:members|string|max:20',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('visitors', 'public');
            $validated['image'] = ltrim($path, '/');
        }

        $visitor = Visitors::create($validated);

        return response()->json([
            'message' => 'Visitor registered successfully',
            'visitor' => $visitor
        ], 201);
    }
}
