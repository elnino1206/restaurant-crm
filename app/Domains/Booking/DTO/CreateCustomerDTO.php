<?php

namespace App\Domains\Booking\DTO;

use Spatie\LaravelData\Data;

class CreateCustomerDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly string $name,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?int $telegramId = null,
        public readonly ?string $telegramUsername = null,
    ) {}
}
