<?php

namespace App\Domains\AI\Enums;

enum AiRequestStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Ожидает',
            self::Processing => 'В обработке',
            self::Completed => 'Выполнен',
            self::Failed => 'Ошибка',
        };
    }
}
