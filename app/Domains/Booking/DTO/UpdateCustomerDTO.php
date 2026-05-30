<?php

namespace App\Domains\Booking\DTO;

use Spatie\LaravelData\Data;

class UpdateCustomerDTO extends Data
{
    public function __construct(
        public readonly ?string $name = null,
        public readonly ?string $phone = null,
        public readonly ?string $email = null,
        public readonly ?array $preferences = null,
        public readonly ?string $notes = null,
    ) {}
}
