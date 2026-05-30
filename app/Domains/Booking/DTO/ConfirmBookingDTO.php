<?php

namespace App\Domains\Booking\DTO;

use Spatie\LaravelData\Data;

class ConfirmBookingDTO extends Data
{
    public function __construct(
        public readonly string $bookingId,
        public readonly ?string $confirmedBy = null,
    ) {}
}
