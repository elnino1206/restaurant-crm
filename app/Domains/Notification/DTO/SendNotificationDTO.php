<?php

namespace App\Domains\Notification\DTO;

use App\Domains\Notification\Enums\NotificationChannel;
use Spatie\LaravelData\Data;

class SendNotificationDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly NotificationChannel $channel,
        public readonly string $type,
        public readonly array $payload = [],
        public readonly ?string $customerId = null,
        public readonly ?string $botToken = null,
        public readonly int|string|null $telegramChatId = null,
    ) {}
}
