<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Auth\AuthController;
use App\Http\Controllers\Api\V1\Admin\VisitorController;


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
    Route::prefix('visitors')->controller(VisitorController::class)->group(function () {
        Route::get('/', 'index');
        Route::post('/', 'store');
        Route::post('/checkout/{id}', 'checkout');
        Route::post('/checkin/{id}', 'checkin');
    });
});

Route::prefix('v1/security')->middleware('auth:sanctum')->group(function () {

});

Route::prefix('v1/warehouse')->middleware('auth:sanctum')->group(function () {

});
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
