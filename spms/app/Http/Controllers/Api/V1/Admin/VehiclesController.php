<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\RequestCreated;
use App\Models\Vehicles;
use App\Models\Drivers;
use Illuminate\Support\Facades\Storage;

class VehiclesController extends Controller
{
    //fuction to list all vehicles
    public function index()
    {
        $vehicles = Vehicles::with('driver')->get();
        return response()->json($vehicles, 200);
    }



    //function to create a vehicle
      public function store(Request $request)
{
    $validatedData = $request->validate([
        'driver_id'     => 'nullable|integer|exists:drivers,id',
        'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        'plate_number'  => 'required|string|unique:vehicles,plate_number',
        'vehicle_type'  => 'required|string',
        'make'          => 'required|string',
        'model'         => 'required|string',
        'color'         => 'required|string',
        'company'       => 'nullable|string',
    ]);

    // Handle image upload
    if ($request->hasFile('image')) {
        $path = $request->file('image')->store('vehicles', 'public');
        $validatedData['image'] = $path;
    }

    $vehicle = Vehicles::create($validatedData);

    return response()->json([
        'message' => 'Vehicle created successfully',
        'vehicle' => $vehicle,
    ], 201);
}



    //function to show a vehicle
    public function show($id){
        $vehicle = Vehicles::findORFail($id);
        return response()->json($vehicle, 200);
    }

    //function to update a vehicle
    public function update(Request $request, $id)
    {
        $vehicle = Vehicles::findOrFail($id);

        $validatedData = $request->validate([
            'driver_id' => 'nullable|integer|exists:drivers,id',
            'image' => 'nullable|string',
            'plate_number' => 'sometimes|required|string|unique:vehicles,plate_number,' . $id,
            'vehicle_type' => 'sometimes|required|string',
            'make' => 'sometimes|required|string',
            'model' => 'sometimes|required|string',
            'color' => 'sometimes|required|string',
            'company' => 'nullable|string',
        ]);

        $vehicle->update($validatedData);
        broadcast(new RequestCreated($vehicle))->toOthers();
        return response()->json($vehicle, 200);
    }


    //function to delete a vehicle
    public function destroy($id)
    {
        $vehicle = Vehicles::findOrFail($id);
        $vehicle->delete();
        return response()->json(['message' => 'Vehicle has been deleted'], 200);
    }
}