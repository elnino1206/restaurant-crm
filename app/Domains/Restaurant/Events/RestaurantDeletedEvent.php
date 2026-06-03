<?php

namespace App\Domains\Restaurant\Events;

class RestaurantDeletedEvent
{
    public function __construct(
        public readonly string $restaurantId,
    ) {}
}
