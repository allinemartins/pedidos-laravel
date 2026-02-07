<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::get('travel-orders', [TravelOrderController::class, 'index']);
    Route::post('travel-orders', [TravelOrderController::class, 'store']);
    Route::get('travel-orders/{id}', [TravelOrderController::class, 'show']);

    Route::patch('travel-orders/{id}/status', [TravelOrderController::class, 'updateStatus'])
        ->middleware('admin');
});
