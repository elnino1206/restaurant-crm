<?php

namespace App\Domains\AI\DTO;

use Spatie\LaravelData\Data;

class CreateAiRequestDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly string $model,
        public readonly string $prompt,
        public readonly ?string $bookingId = null,
    ) {}
}
