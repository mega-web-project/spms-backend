<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1/auth')->middleware('guest:sanctum')->group(function () {

});

Route::prefix('v1/profile')->middleware('auth:sanctum')->group(function () {

});

Route::prefix('v1/admin')->middleware('auth:sanctum')->group(function () {

});

Route::prefix('v1/super-admin')->middleware('auth:sanctum')->group(function () {

});

Route::prefix('v1/warehouse')->middleware('auth:sanctum')->group(function () {

});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
