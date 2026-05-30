<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BookingController;
use App\Http\Controllers\Api\V1\CustomerController;
use App\Http\Controllers\Api\V1\FloorController;
use App\Http\Controllers\Api\V1\RestaurantController;
use App\Http\Controllers\Api\V1\SlotController;
use App\Http\Controllers\Api\V1\TableController;
use App\Http\Controllers\Api\V1\TimeSlotConfigController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {

    // Public
    Route::middleware('throttle:30,1')->group(function (): void {
        Route::post('/auth/login', [AuthController::class, 'login']);
    });

    Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function (): void {
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // ── Bookings ──────────────────────────────────────────────────────────
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{booking}', [BookingController::class, 'show']);
        Route::patch('/bookings/{booking}', [BookingController::class, 'update']);
        Route::post('/bookings/{booking}/confirm', [BookingController::class, 'confirm']);
        Route::post('/bookings/{booking}/cancel', [BookingController::class, 'cancel']);
        Route::post('/bookings/{booking}/complete', [BookingController::class, 'complete']);
        Route::post('/bookings/{booking}/no-show', [BookingController::class, 'markNoShow']);

        // ── Customers ─────────────────────────────────────────────────────────
        Route::get('/customers', [CustomerController::class, 'index']);
        Route::post('/customers', [CustomerController::class, 'store']);
        Route::get('/customers/{customer}', [CustomerController::class, 'show']);
        Route::patch('/customers/{customer}', [CustomerController::class, 'update']);

        // ── Текущий ресторан (owner / manager) ────────────────────────────────
        Route::get('/restaurant', [RestaurantController::class, 'show']);
        Route::patch('/restaurant', [RestaurantController::class, 'update']);
        Route::get('/restaurant/floors', [RestaurantController::class, 'floors']);
        Route::get('/restaurant/time-slot-configs', [RestaurantController::class, 'timeSlotConfigs']);

        // ── Свободные слоты ───────────────────────────────────────────────────
        Route::get('/slots', [SlotController::class, 'index']);

        // ── super_admin: все рестораны ────────────────────────────────────────
        Route::get('/restaurants', [RestaurantController::class, 'index']);
        Route::post('/restaurants', [RestaurantController::class, 'store']);
        Route::get('/restaurants/{restaurant}', [RestaurantController::class, 'showById']);
        Route::patch('/restaurants/{restaurant}', [RestaurantController::class, 'updateById']);
        Route::get('/restaurants/{restaurant}/floors', [RestaurantController::class, 'floorsById']);
        Route::get('/restaurants/{restaurant}/time-slot-configs', [TimeSlotConfigController::class, 'index']);
        Route::put('/restaurants/{restaurant}/time-slot-configs', [TimeSlotConfigController::class, 'bulkUpdate']);

        // ── Floors ────────────────────────────────────────────────────────────
        Route::post('/restaurants/{restaurant}/floors', [FloorController::class, 'store']);
        Route::patch('/restaurants/{restaurant}/floors/{floor}', [FloorController::class, 'update']);
        Route::delete('/restaurants/{restaurant}/floors/{floor}', [FloorController::class, 'destroy']);

        // ── Tables ────────────────────────────────────────────────────────────
        Route::post('/restaurants/{restaurant}/floors/{floor}/tables', [TableController::class, 'store']);
        Route::patch('/restaurants/{restaurant}/tables/{table}', [TableController::class, 'update']);
        Route::delete('/restaurants/{restaurant}/tables/{table}', [TableController::class, 'destroy']);
    });
});
