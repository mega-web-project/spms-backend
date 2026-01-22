<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1\Security\GoodsTrackingController;

// Goods Tracking Routes
Route::prefix('goods')->group(function () {
    Route::get('/', [GoodsTrackingController::class, 'index'])->name('goods.index');
    Route::post('/', [GoodsTrackingController::class, 'store'])->name('goods.store');
    Route::get('/{id}', [GoodsTrackingController::class, 'show'])->name('goods.show');
    Route::put('/{id}', [GoodsTrackingController::class, 'update'])->name('goods.update');
    Route::delete('/{id}', [GoodsTrackingController::class, 'destroy'])->name('goods.destroy');
});