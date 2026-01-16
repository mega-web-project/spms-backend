<?php

namespace App\Http\Controllers;
use App\Events\RequestCreated;
use Illuminate\Http\Request;

class TestController extends Controller
{
    //
    public function store(Request $request)
{
    $data = $request->validate([
        'title' => 'required|string',
        'description' => 'nullable|string',
    ]);

    $newRequest = Request::create($data);

    // Broadcast the event
    broadcast(new RequestCreated($newRequest))->toOthers();

    return response()->json($newRequest, 201);
}
}
