<?php

namespace App\Domains\Booking\DTO;

use Spatie\LaravelData\Data;

class CancelBookingDTO extends Data
{
    public function __construct(
        public readonly string $bookingId,
        public readonly ?string $reason = null,
        public readonly ?string $cancelledBy = null,
    ) {}
}
