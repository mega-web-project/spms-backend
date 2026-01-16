<?php

namespace App\Http\Controllers\Api\V1\Admin;
use App\Models\Visitors;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Events\RequestCreated;

class VisitorController extends Controller
{
   public function index()
   {
        
       return response()->json(Visitors::all());
   }

   public function store(Request $request){
        $validatedData = $request->validate([
            'full_name' => 'required|string|max:255',
            'ID_number' => 'required|string|unique:visitors,ID_number',
            'phone_number' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'purpose_of_visit' => 'required|string|max:255',
            'person_to_visit' => 'required|string|max:255',
            'department' => 'nullable|string|max:255',
            'additional_notes' => 'nullable|string'
        ]);
        $validatedData['status'] = 'checked_in';
        $validatedData['check_in_time'] = now();

        $visitor = Visitors::create($validatedData);
        broadcast(new RequestCreated($visitor))->toOthers();


        return response()->json($visitor, 201);
   }

   //checkout visitor
   public function checkout($id){
        /*$visitor = Visitors::find($id);
        if(!$visitor){
            return response()->json(['message' => 'Visitor not found'], 404);
        }
        $visitor->delete();
        return response()->json(['message' => 'Visitor checked out successfully']);*/
        $visitor = Visitors::find($id);
        if(!$visitor){
            return response()->json(['message' => 'Visitor not found'], 404);
        }
        $visitor->status = 'checked_out';
        $visitor->check_out_time = now();
        $visitor->save();
        broadcast(new RequestCreated($visitor))->toOthers();
        return response()->json(['message' => 'Visitor checked out successfully']);
    }

    //checkin visitor
    public function checkin($id){
        $visitor = Visitors::find($id);
        if(!$visitor){
            return response()->json(['message' => 'Visitor not found'], 404);
        }
        $visitor->status = 'checked_in';
        $visitor->check_in_time = now();
        $visitor->check_out_time = null;
        $visitor->save();

        broadcast(new RequestCreated($visitor))->toOthers();
        return response()->json(['message' => 'Visitor checked in successfully']);
    }
}