<?php

namespace App\Domains\Notification\DTO;

use App\Domains\Notification\Enums\NotificationChannel;
use Spatie\LaravelData\Data;

class SendNotificationDTO extends Data
{
    public function __construct(
        public readonly string $notifiableType,
        public readonly string $notifiableId,
        public readonly NotificationChannel $channel,
        public readonly string $notificationType,
        public readonly array $data = [],
        public readonly ?string $restaurantId = null,
    ) {}
}
