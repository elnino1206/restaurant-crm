<?php

namespace App\Domains\Restaurant\DTO;

use Spatie\LaravelData\Data;

class UpdateRestaurantDTO extends Data
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $timezone = null,
        public readonly ?string $phone = null,
        public readonly ?string $address = null,
        public readonly ?array $settings = null,
        public readonly ?bool $isActive = null,
    ) {}
}
