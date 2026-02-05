<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\Drivers;
use Illuminate\Http\Request;
use App\Events\RequestCreated;
use App\Http\Controllers\Controller;



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
            // 'company' => 'nullable|string',
            'phone' => 'required|string|unique:drivers,phone',
            'license_number' => 'nullable|string',

            'id_number' => 'nullable|string',
            'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('image')) {
                $path = $request->file('image')->store('drivers', 'public');
                 $validatedData['image'] = ltrim($path, '/');
            }


        $driver = Drivers::create($validatedData);
       
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
        'full_name' => 'sometimes|string',
        'phone' => 'sometimes|string|unique:drivers,phone,' . $id,
        'license_number' => 'nullable|string',
        'id_number' => 'nullable|string',
        'image'         => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048'
        ]);

        if ($request->hasFile('image')) {
                $path = $request->file('image')->store('drivers', 'public');
                 $validatedData['image'] = ltrim($path, '/');
            }

        $driver->update($validatedData);
      

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
