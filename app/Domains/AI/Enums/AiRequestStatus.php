<?php

namespace App\Domains\AI\Enums;

enum AiRequestStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В обработке',
            self::Completed => 'Выполнен',
            self::Failed => 'Ошибка',
        };
    }
}
