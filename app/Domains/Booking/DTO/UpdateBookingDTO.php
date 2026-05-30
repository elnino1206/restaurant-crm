<?php

namespace App\Domains\Booking\DTO;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class UpdateBookingDTO extends Data
{
    public function __construct(
        public readonly ?int $guestsCount = null,
        public readonly ?Carbon $bookingStart = null,
        public readonly ?Carbon $bookingEnd = null,
        public readonly ?string $tableId = null,
        public readonly ?string $comment = null,
    ) {}
}
