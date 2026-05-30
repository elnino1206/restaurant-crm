<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\SlotController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::middleware('throttle:30,1')->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Bookings
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::patch('/bookings/{booking}', [BookingController::class, 'update']);
        Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);
        Route::post('/bookings/{booking}/no-show', [BookingController::class, 'markNoShow']);

        // Customers
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);

        // Restaurant
        Route::get('/restaurant', [RestaurantController::class, 'show']);
        Route::patch('/restaurant', [RestaurantController::class, 'update']);
        Route::get('/restaurant/floors', [RestaurantController::class, 'floors']);
        Route::get('/restaurant/time-slot-configs', [RestaurantController::class, 'timeSlotConfigs']);

        // Available slots
        Route::get('/slots', [SlotController::class, 'index']);
    });
});
