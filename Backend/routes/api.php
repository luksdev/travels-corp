<?php

declare(strict_types = 1);

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TravelRequestController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::get('user', [AuthController::class, 'getUser']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });
});

Route::middleware('auth:api')->group(function () {
    Route::apiResource('travel-requests', TravelRequestController::class);

    Route::patch('travel-requests/{travel_request}/status', [TravelRequestController::class, 'updateStatus'])
        ->name('travel-requests.update-status');
    Route::patch('travel-requests/{travel_request}/cancel', [TravelRequestController::class, 'cancel'])
        ->name('travel-requests.cancel');
});
