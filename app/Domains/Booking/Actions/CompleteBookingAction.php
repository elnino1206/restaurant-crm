<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\Exceptions\BookingNotFoundException;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\States\CompletedState;

class CompleteBookingAction
{
    public function handle(string $bookingId): Booking
    {
        $booking = Booking::withoutGlobalScopes()->find($bookingId);

        if (! $booking) {
            throw BookingNotFoundException::withId($bookingId);
        }

        $booking->status->transitionTo(CompletedState::class);

        return $booking->fresh();
    }
}
