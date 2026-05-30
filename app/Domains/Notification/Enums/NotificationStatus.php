<?php

namespace App\Domains\Notification\Enums;

enum NotificationStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает',
            self::Sent => 'Отправлено',
            self::Failed => 'Ошибка',
        };
    }
}
