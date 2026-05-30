<?php

namespace App\Domains\Booking;

use App\Domains\Booking\DTO\AllocateTableDTO;
use App\Domains\Booking\Exceptions\NoTablesAvailableException;
use App\Domains\Restaurant\Models\Table;

class TableAllocator
{
    public function allocate(AllocateTableDTO $dto): Table
    {
        $query = Table::withoutGlobalScopes()
            ->where('restaurant_id', $dto->restaurantId)
            ->where('is_active', true)
            ->where('capacity', '>=', $dto->guestsCount)
            ->where('min_capacity', '<=', $dto->guestsCount)
            ->whereNull('deleted_at')
            ->whereDoesntHave('bookings', function ($q) use ($dto) {
                $q->whereIn('status', ['pending', 'confirmed'])
                    ->where('booking_start', '<', $dto->bookingEnd)
                    ->where('booking_end', '>', $dto->bookingStart);
            })
            ->orderBy('capacity');

        if ($dto->preferredTableId) {
            $query->where('id', $dto->preferredTableId);
        }

        $table = $query->first();

        if (! $table) {
            throw NoTablesAvailableException::forRequest($dto);
        }

        return $table;
    }
}
