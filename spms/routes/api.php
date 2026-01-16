<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Admin\VehiclesController;
use App\Http\Controllers\Api\V1\Admin\DriversController;


Route::prefix('v1/auth')->middleware('guest:sanctum')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
});

Route::prefix('v1/profile')->middleware('auth:sanctum')->group(function () {

});

Route::prefix('v1/admin')->middleware('auth:sanctum')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'index']);

    Route::prefix('vehicles')->group(function () {
        Route::post('/', [VehiclesController::class, 'store']);
        Route::get('/', [VehiclesController::class, 'index']);
        Route::get('/{id}', [VehiclesController::class, 'show']);
        Route::put('/{id}', [VehiclesController::class, 'update']);
        Route::delete('/{id}', [VehiclesController::class, 'destroy']);
    });

    Route::prefix('drivers')->group(function () {
        Route::post('/', [DriversController::class, 'store']);
        Route::get('/', [DriversController::class, 'index']);
        Route::get('/{id}', [DriversController::class, 'show']);
        Route::put('/{id}', [DriversController::class, 'update']);
        Route::delete('/{id}', [DriversController::class, 'destroy']);
    });

});

Route::prefix('v1/security')->middleware('auth:sanctum')->group(function () {

});

Route::prefix('v1/warehouse')->middleware('auth:sanctum')->group(function () {

});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
