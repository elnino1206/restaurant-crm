<?php

namespace App\Domains\Booking\Jobs;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\States\CancelledState;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class AutoCancelExpiredBookingsJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    /** РђРІС‚Рѕ-РѕС‚РјРµРЅР° Р±СЂРѕРЅРµР№ Сѓ РєРѕС‚РѕСЂС‹С… booking_start РїСЂРѕС€С‘Р» N РјРёРЅСѓС‚ РЅР°Р·Р°Рґ Рё РЅРµС‚ check-in */
    private const EXPIRY_MINUTES = 30;

    public function handle(): void
    {
        $expiredAt = now()->subMinutes(self::EXPIRY_MINUTES);

        $bookings = Booking::withoutGlobalScopes()
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_start', '<', $expiredAt)
            ->whereNull('booking_end')
            ->get();

        $cancelled = 0;

        foreach ($bookings as $booking) {
            try {
                $booking->status->transitionTo(CancelledState::class);
                $cancelled++;
            } catch (\Throwable $e) {
                Log::warning('AutoCancelExpiredBookingsJob: could not cancel booking', [
                    'booking_id' => $booking->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($cancelled > 0) {
            Log::info("AutoCancelExpiredBookingsJob: cancelled {$cancelled} expired bookings");
        }
    }

    public function failed(\Throwable $e): void
    {
        Log::error('AutoCancelExpiredBookingsJob failed', ['error' => $e->getMessage()]);
    }
}
