<?php

namespace App\Domains\Booking\Enums;

enum BookingSource: string
{
    case Telegram = 'telegram';
    case Web = 'web';
    case Phone = 'phone';
    case WalkIn = 'walk_in';

    public function label(): string
    {
        return match ($this) {
            self::Telegram => 'Telegram',
            self::Web => 'Веб',
            self::Phone => 'Телефон',
            self::WalkIn => 'Живая запись',
        };
    }
}
