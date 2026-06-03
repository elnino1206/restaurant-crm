<?php

namespace App\Domains\Booking\DTO;

use App\Domains\Booking\Enums\BookingSource;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class CreateBookingDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly int $guestsCount,
        public readonly Carbon $bookingStart,
        public readonly ?Carbon $bookingEnd = null,
        public readonly BookingSource $source = BookingSource::Telegram,
        public readonly ?string $tableId = null,
        public readonly ?string $customerId = null,
        public readonly ?string $createdBy = null,
        public readonly ?string $comment = null,
    ) {}
}
