<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Exceptions\BookingNotFoundException;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\States\NoShowState;

class MarkNoShowAction
{
    public function handle(string $bookingId): Booking
    {
        $booking = Booking::withoutGlobalScopes()->find($bookingId);

        if (! $booking) {
            throw BookingNotFoundException::withId($bookingId);
        }

        $booking->status->transitionTo(NoShowState::class);

        return $booking->fresh();
    }
}
