<?php

namespace App\Domains\Notification\Enums;

enum NotificationChannel: string
{
    case Telegram = 'telegram';
    case Email = 'email';
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Telegram => 'Telegram',
            self::Email => 'Email',
            self::Sms => 'SMS',
        };
    }
}
