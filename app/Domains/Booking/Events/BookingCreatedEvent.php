<?php

namespace App\Domains\Booking\Events;

class BookingCreatedEvent
{
    public function __construct(
        public readonly string $bookingId,
        public readonly string $restaurantId,
    ) {}
}
