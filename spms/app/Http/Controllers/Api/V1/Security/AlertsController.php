<?php

namespace App\Http\Controllers\Api\V1\Security;

use App\Http\Controllers\Controller;
use App\Http\Resources\AlertResource;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertsController extends Controller
{
    public function index(Request $request)
    {
        $query = Alert::query()->latest();

        if ($request->filled('resolved')) {
            $resolved = filter_var($request->query('resolved'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if (!is_null($resolved)) {
                $query->where('resolved', $resolved);
            }
        }

        if ($request->filled('type')) {
            $query->where('type', $request->query('type'));
        }

        if ($request->filled('severity')) {
            $query->where('severity', $request->query('severity'));
        }

        if ($request->filled('entity_type')) {
            $query->where('entity_type', $request->query('entity_type'));
        }

        if ($request->filled('entity_id')) {
            $query->where('entity_id', $request->query('entity_id'));
        }

        $alerts = $query->get();

        return response()->json([
            'total' => $alerts->count(),
            'records' => AlertResource::collection($alerts),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'severity' => 'required|string',
            'message' => 'required|string',
            'entity_type' => 'nullable|string',
            'entity_id' => 'nullable|integer',
        ]);

        $alert = Alert::createIfNotExists([
            'type' => $validated['type'],
            'severity' => $validated['severity'],
            'message' => $validated['message'],
            'entity_type' => $validated['entity_type'] ?? null,
            'entity_id' => $validated['entity_id'] ?? null,
            'resolved' => false,
        ]);

        return response()->json([
            'message' => 'Alert created',
            'alert' => new AlertResource($alert),
        ], 201);
    }

    public function resolve($id)
    {
        $alert = Alert::findOrFail($id);

        $alert->update([
            'resolved' => true,
            'resolved_at' => now(),
        ]);

        return response()->json([
            'message' => 'Alert resolved',
            'alert' => new AlertResource($alert),
        ]);
    }
}
