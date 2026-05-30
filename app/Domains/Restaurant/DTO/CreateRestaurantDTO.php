<?php

namespace App\Domains\Restaurant\DTO;

use Spatie\LaravelData\Data;

class CreateRestaurantDTO extends Data
{
    public function __construct(
        public readonly string $name,
        public readonly string $slug,
        public readonly string $timezone = 'UTC',
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
    ) {}
}
