    <?php

use App\Http\Controllers\BookingPageController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RestaurantAdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Public booking page
Route::get('/book/{slug}', [BookingPageController::class, 'show'])->name('booking.show');
Route::post('/book/{slug}/slots', [BookingPageController::class, 'slots'])->name('booking.slots');
Route::post('/book/{slug}', [BookingPageController::class, 'book'])->name('booking.store');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('restaurants', [RestaurantAdminController::class, 'index'])->name('restaurants.index');
    Route::get('restaurants/create', [RestaurantAdminController::class, 'create'])->name('restaurants.create');
    Route::post('restaurants', [RestaurantAdminController::class, 'store'])->name('restaurants.store');
    Route::get('restaurants/{restaurant}/edit', [RestaurantAdminController::class, 'edit'])->name('restaurants.edit');
    Route::put('restaurants/{restaurant}', [RestaurantAdminController::class, 'update'])->name('restaurants.update');

    // Schedule
    Route::put('restaurants/{restaurant}/schedule', [RestaurantAdminController::class, 'updateSchedule'])->name('restaurants.schedule.update');

    // Floors
    Route::post('restaurants/{restaurant}/floors', [RestaurantAdminController::class, 'storeFloor'])->name('restaurants.floors.store');
    Route::put('restaurants/{restaurant}/floors/{floor}', [RestaurantAdminController::class, 'updateFloor'])->name('restaurants.floors.update');
    Route::delete('restaurants/{restaurant}/floors/{floor}', [RestaurantAdminController::class, 'destroyFloor'])->name('restaurants.floors.destroy');

    // Tables
    Route::post('restaurants/{restaurant}/floors/{floor}/tables', [RestaurantAdminController::class, 'storeTable'])->name('restaurants.tables.store');
    Route::put('restaurants/{restaurant}/tables/{table}', [RestaurantAdminController::class, 'updateTable'])->name('restaurants.tables.update');
    Route::delete('restaurants/{restaurant}/tables/{table}', [RestaurantAdminController::class, 'destroyTable'])->name('restaurants.tables.destroy');

    // Users
    Route::post('restaurants/{restaurant}/users', [RestaurantAdminController::class, 'storeUser'])->name('restaurants.users.store');
    Route::delete('restaurants/{restaurant}/users/{user}', [RestaurantAdminController::class, 'destroyUser'])->name('restaurants.users.destroy');
});
