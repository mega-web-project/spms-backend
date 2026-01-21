<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Admin\VisitorController;
use App\Http\Controllers\Api\V1\Admin\VehiclesController;
use App\Http\Controllers\Api\V1\Admin\DriversController;
use App\Http\Controllers\Api\V1\Security\CheckInController;
use App\Http\Controllers\Api\V1\Admin\GoodsItemController;
use App\Http\Controllers\Api\V1\Admin\VisitController;
use App\Http\Controllers\Api\V1\Security\CheckOutController;
use App\Http\Controllers\TestController;

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
    Route::put('/update/users/{id}', [AuthController::class, 'update']);
    Route::post('/activate-or-deactivate/{id}', [AuthController::class, 'activateOrdeactivate']);

});

Route::prefix('v1/security')->middleware('auth:sanctum')->group(function () {
    Route::post('/check-in', [CheckInController::class, 'store']);
    Route::post('/check-out', [CheckOutController::class, 'checkout']);
    Route::get('/check-out/history', [CheckOutController::class, 'history']);


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

    Route::prefix('visitors')->controller(VisitorController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
    });
    

});


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/test-request', [TestController::class, 'store']);
