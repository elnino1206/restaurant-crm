<?php

namespace App\Domains\Booking\Enums;

enum BookingStatus: string
{
    case Pending = 'pending';
    case Confirmed = 'confirmed';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
    case NoShow = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает подтверждения',
            self::Confirmed => 'Подтверждено',
            self::Completed => 'Завершено',
            self::Cancelled => 'Отменено',
            self::NoShow => 'Не явился',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Cancelled, self::NoShow]);
    }
}
