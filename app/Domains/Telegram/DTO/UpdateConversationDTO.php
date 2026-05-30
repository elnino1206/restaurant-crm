<?php

namespace App\Domains\Telegram\DTO;

use Spatie\LaravelData\Data;

class UpdateConversationDTO extends Data
{
    public function __construct(
        public readonly string $state,
        public readonly ?array $payload = null,
        public readonly int $failedAttempts = 0,
    ) {}
}
