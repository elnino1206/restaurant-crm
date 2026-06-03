<?php

namespace App\Domains\Restaurant\Events;

class RestaurantCreatedEvent
{
    public function __construct(
        public readonly string $restaurantId,
    ) {}
}
