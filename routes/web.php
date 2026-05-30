<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RestaurantAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index']);

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('restaurants', [RestaurantAdminController::class, 'index'])->name('restaurants.index');
    Route::get('restaurants/create', [RestaurantAdminController::class, 'create'])->name('restaurants.create');
    Route::post('restaurants', [RestaurantAdminController::class, 'store'])->name('restaurants.store');
    Route::get('restaurants/{restaurant}/edit', [RestaurantAdminController::class, 'edit'])->name('restaurants.edit');
    Route::put('restaurants/{restaurant}', [RestaurantAdminController::class, 'update'])->name('restaurants.update');

    // Floors
    Route::post('restaurants/{restaurant}/floors', [RestaurantAdminController::class, 'storeFloor'])->name('restaurants.floors.store');
    Route::put('restaurants/{restaurant}/floors/{floor}', [RestaurantAdminController::class, 'updateFloor'])->name('restaurants.floors.update');
    Route::delete('restaurants/{restaurant}/floors/{floor}', [RestaurantAdminController::class, 'destroyFloor'])->name('restaurants.floors.destroy');

    // Tables
    Route::post('restaurants/{restaurant}/floors/{floor}/tables', [RestaurantAdminController::class, 'storeTable'])->name('restaurants.tables.store');
    Route::put('restaurants/{restaurant}/tables/{table}', [RestaurantAdminController::class, 'updateTable'])->name('restaurants.tables.update');
    Route::delete('restaurants/{restaurant}/tables/{table}', [RestaurantAdminController::class, 'destroyTable'])->name('restaurants.tables.destroy');
});
