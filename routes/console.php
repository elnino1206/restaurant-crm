<?php

use App\Domains\Booking\Jobs\AutoCancelExpiredBookingsJob;
use App\Domains\Booking\Jobs\SendBookingReminderJob;
use App\Domains\Booking\Models\Booking;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Закрыть открытые брони когда ресторан закрывается
Schedule::command('bookings:close-open')->everyMinute();

// Авто-отмена просроченных броней (pending/confirmed + 30 минут прошло)
Schedule::job(new AutoCancelExpiredBookingsJob)->everyFiveMinutes();

// Напоминания за 2 часа до бронирования
Schedule::call(function () {
    $from = now()->addHours(2)->startOfMinute();
    $to = $from->copy()->addMinute();

    Booking::withoutGlobalScopes()
        ->whereIn('status', ['pending', 'confirmed'])
        ->whereBetween('booking_start', [$from, $to])
        ->each(function (Booking $booking) {
            SendBookingReminderJob::dispatch($booking->id)->onQueue('default');
        });
})->everyMinute()->name('booking-reminders-2h');
