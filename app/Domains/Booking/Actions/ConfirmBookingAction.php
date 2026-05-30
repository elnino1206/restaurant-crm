<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\DTO\ConfirmBookingDTO;
use App\Domains\Booking\Events\BookingConfirmedEvent;
use App\Domains\Booking\Exceptions\BookingNotFoundException;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\States\ConfirmedState;

class ConfirmBookingAction
{
    public function handle(ConfirmBookingDTO $dto): Booking
    {
        $booking = Booking::withoutGlobalScopes()->find($dto->bookingId);

        if (! $booking) {
            throw BookingNotFoundException::withId($dto->bookingId);
        }

        $booking->status->transitionTo(ConfirmedState::class);

        event(new BookingConfirmedEvent($booking->id, $booking->restaurant_id));

        return $booking->fresh();
    }
}
