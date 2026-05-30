<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\DTO\UpdateBookingDTO;
use App\Domains\Booking\Exceptions\BookingNotFoundException;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\States\CancelledState;
use App\Domains\Booking\States\CompletedState;
use App\Domains\Booking\States\NoShowState;
use Spatie\ModelStates\Exceptions\TransitionNotAllowed;

class UpdateBookingAction
{
    public function handle(string $bookingId, UpdateBookingDTO $dto): Booking
    {
        $booking = Booking::withoutGlobalScopes()->find($bookingId);

        if (! $booking) {
            throw BookingNotFoundException::withId($bookingId);
        }

        // Final states cannot be updated
        if ($booking->status instanceof CancelledState
            || $booking->status instanceof CompletedState
            || $booking->status instanceof NoShowState
        ) {
            throw new TransitionNotAllowed(
                "Cannot update booking [{$bookingId}] in final state [{$booking->status->getValue()}]."
            );
        }

        if ($dto->guestsCount !== null) {
            $booking->guests_count = $dto->guestsCount;
        }

        if ($dto->bookingStart !== null) {
            $booking->booking_start = $dto->bookingStart;
        }

        if ($dto->bookingEnd !== null) {
            $booking->booking_end = $dto->bookingEnd;
        }

        if ($dto->tableId !== null) {
            $booking->table_id = $dto->tableId;
        }

        if ($dto->comment !== null) {
            $booking->comment = $dto->comment;
        }

        $booking->save();

        return $booking->fresh();
    }
}
