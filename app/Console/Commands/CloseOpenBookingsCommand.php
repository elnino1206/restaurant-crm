<?php

namespace App\Console\Commands;

use App\Domains\Booking\Models\Booking;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Models\TimeSlotConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class CloseOpenBookingsCommand extends Command
{
    protected $signature = 'bookings:close-open';

    protected $description = 'Set booking_end on open-ended bookings once the restaurant closes for the day';

    public function handle(): void
    {
        Restaurant::all()->each(function (Restaurant $restaurant) {
            $now = Carbon::now($restaurant->timezone);
            $dayOfWeek = $now->dayOfWeekIso - 1; // 0=Monday … 6=Sunday

            $config = TimeSlotConfig::withoutGlobalScopes()
                ->where('restaurant_id', $restaurant->id)
                ->where('day_of_week', $dayOfWeek)
                ->first();

            if (! $config || $config->is_day_off || ! $config->close_time) {
                return;
            }

            $closeTime = Carbon::parse($config->close_time);
            $closingAt = $now->copy()->setTime($closeTime->hour, $closeTime->minute, 0);

            if ($now->lt($closingAt)) {
                return; // Restaurant not closed yet
            }

            $updated = Booking::withoutGlobalScopes()
                ->where('restaurant_id', $restaurant->id)
                ->whereNull('booking_end')
                ->whereIn('status', ['pending', 'confirmed'])
                ->where('booking_start', '<', $closingAt)
                ->update(['booking_end' => $closingAt]);

            if ($updated > 0) {
                $this->line("Closed {$updated} open-ended booking(s) for [{$restaurant->name}]");
            }
        });
    }
}
