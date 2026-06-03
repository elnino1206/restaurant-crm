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
        $excludeTableIds = [];
        $maxAttempts = 3;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $tableId = $dto->tableId ?? $this->allocateTable($dto, $excludeTableIds)->id;

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

                event(new BookingCreatedEvent($booking->id, $booking->restaurant_id));

                return $booking;
            } catch (QueryException $e) {
                // SQLSTATE 23P01 — PostgreSQL exclusion constraint violation
                if ($e->getCode() !== '23P01') {
                    throw $e;
                }

                if ($dto->tableId !== null) {
                    throw BookingConflictException::forTable($tableId, $dto->bookingStart);
                }

                $excludeTableIds[] = $tableId;
            }
        }

        throw NoTablesAvailableException::forRequest(
            AllocateTableDTO::from([
                'restaurantId' => $dto->restaurantId,
                'guestsCount' => $dto->guestsCount,
                'bookingStart' => $dto->bookingStart,
                'bookingEnd' => $dto->bookingEnd,
            ])
        );
    }

    private function allocateTable(CreateBookingDTO $dto, array $excludeTableIds = []): Table
    {
        return app(TableAllocator::class)->allocate(
            AllocateTableDTO::from([
                'restaurantId' => $dto->restaurantId,
                'guestsCount' => $dto->guestsCount,
                'bookingStart' => $dto->bookingStart,
                'bookingEnd' => $dto->bookingEnd,
                'excludeTableIds' => $excludeTableIds,
            ])
        );
    }
}
