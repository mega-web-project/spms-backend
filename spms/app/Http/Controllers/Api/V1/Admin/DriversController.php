<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\RequestCreated;
use App\Models\Drivers;

class DriversController extends Controller
{
    //
    public function index()
    {
        //
        $drivers = Drivers::all();
        return response()->json($drivers, 200);
    }

    //function to create a driver
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'full_name' => 'required|string',
            'company' => 'nullable|string',
            'phone' => 'required|string|unique:drivers,phone',
            'license_number' => 'nullable|string|unique:drivers,license_number',
            'address' => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
                $path = $request->file('image')->store('vehicles', 'public');
                $validatedData['image'] = $path;
            }


        $driver = Drivers::create($validatedData);
        broadcast(new RequestCreated($driver))->toOthers();
        return response()->json($driver, 201);
    }

    public function show($id)
    {
        $driver = Drivers::findOrFail($id);
        return response()->json($driver, 200);
    }

    public function update(Request $request, $id)
    {
        $driver = Drivers::findOrFail($id);

        $validatedData = $request->validate([
            'full_name' => 'sometimes|required|string',
            'company' => 'nullable|string',
            'phone' => 'sometimes|required|string|unique:drivers,phone,' . $id,
            'license_number' => 'sometimes|required|string|unique:drivers,license_number,' . $id,
            'address' => 'nullable|string',
        ]);

        $driver->update($validatedData);
        broadcast(new RequestCreated($driver))->toOthers();
        return response()->json($driver, 200);
    }

    public function destroy($id)
{
    // Finds the single driver by ID or triggers a 404
    $driver = Drivers::findOrFail($id);
    
    $driver->delete();

    return response()->json([
        'message' => "Driver with ID $id has been removed"
    ], 200);
}

}
