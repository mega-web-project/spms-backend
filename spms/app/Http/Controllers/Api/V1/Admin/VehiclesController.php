<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vehicles;
use App\Models\Drivers;

class VehiclesController extends Controller
{
    //fuction to list all vehicles
    public function index()
    {
        $vehicles = Vehicles::with('driver')->get();
        return response()->json($vehicles, 200);
    }



    //function to create a vhicle
    public function store(Request $request){

        $validatedData = $request->validate([
            'driver_id' => 'required|exists:drivers,id',
            'image' => 'nullable|string',
            'plate_number' => 'required|string|unique:vehicles,plate_number',
            'vehicle_type' => 'required|string',
            'make' => 'required|string',
            'model' => 'required|string',
            'color' => 'required|string',
            'company' => 'nullable|string',
        ]);

        $vehicle = Vehicles::create($validatedData);
        return response()->json($vehicle, 201);
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
            'driver_id' => 'required|exists:drivers,id',
            'image' => 'nullable|string',
            'plate_number' => 'required|string|unique:vehicles,plate_number,' . $id,
            'vehicle_type' => 'required|string',
            'make' => 'required|string',
            'model' => 'required|string',
            'color' => 'required|string',
            'company' => 'nullable|string',
        ]);

        $vehicle->update($validatedData);
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