<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\DTO\CancelBookingDTO;
use App\Domains\Booking\Events\BookingCancelledEvent;
use App\Domains\Booking\Exceptions\BookingNotFoundException;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\States\CancelledState;

class CancelBookingAction
{
    public function handle(CancelBookingDTO $dto): Booking
    {
        $booking = Booking::withoutGlobalScopes()->find($dto->bookingId);

        if (! $booking) {
            throw BookingNotFoundException::withId($dto->bookingId);
        }

        $booking->status->transitionTo(CancelledState::class);

        if ($dto->reason) {
            $booking->notes()->create([
                'content' => "Причина отмены: {$dto->reason}",
                'user_id' => $dto->cancelledBy,
            ]);
        }

        event(new BookingCancelledEvent($booking->id, $booking->restaurant_id));

        return $booking->fresh();
    }
}
