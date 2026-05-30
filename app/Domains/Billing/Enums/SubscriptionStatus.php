<?php

namespace App\Domains\Billing\Enums;

enum SubscriptionStatus: string
{
    case Trialing = 'trialing';
    case Active = 'active';
    case Cancelled = 'cancelled';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::Trialing => 'Пробный период',
            self::Active => 'Активна',
            self::Cancelled => 'Отменена',
            self::Expired => 'Истекла',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Trialing, self::Active]);
    }
}
