<?php

namespace App\Domains\Booking\Events;

class BookingCancelledEvent
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $restaurantId,
    ) {}
}
