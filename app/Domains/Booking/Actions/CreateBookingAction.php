<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\DTO\AllocateTableDTO;
use App\Domains\Booking\DTO\CreateBookingDTO;
use App\Domains\Booking\Events\BookingCreatedEvent;
use App\Domains\Booking\Exceptions\BookingConflictException;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\TableAllocator;
use App\Domains\Restaurant\Models\Table;
use Illuminate\Database\QueryException;

class CreateBookingAction
{
    public function handle(CreateBookingDTO $dto): Booking
    {
        $tableId = $dto->tableId ?? $this->allocateTable($dto)->id;

        try {
            $booking = Booking::create([
                'restaurant_id' => $dto->restaurantId,
                'table_id' => $tableId,
                'customer_id' => $dto->customerId,
                'created_by' => $dto->createdBy,
                'guests_count' => $dto->guestsCount,
                'booking_start' => $dto->bookingStart,
                'booking_end' => $dto->bookingEnd,
                'comment' => $dto->comment,
                'source' => $dto->source,
            ]);
        } catch (QueryException $e) {
            // PostgreSQL exclusion constraint: SQLSTATE 23P01
            if (str_contains($e->getMessage(), 'no_overlapping_bookings')) {
                throw BookingConflictException::forTable($tableId, $dto->bookingStart);
            }

            throw $e;
        }

        event(new BookingCreatedEvent($booking->id, $booking->restaurant_id));

        return $booking;
    }

    private function allocateTable(CreateBookingDTO $dto): Table
    {
        return app(TableAllocator::class)->allocate(
            AllocateTableDTO::from([
                'restaurantId' => $dto->restaurantId,
                'guestsCount' => $dto->guestsCount,
                'bookingStart' => $dto->bookingStart,
                'bookingEnd' => $dto->bookingEnd,
            ])
        );
    }
}
