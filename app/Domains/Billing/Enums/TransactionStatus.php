<?php

namespace App\Domains\Billing\Enums;

enum TransactionStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'В обработке',
            self::Completed => 'Выполнен',
            self::Failed => 'Ошибка',
            self::Refunded => 'Возврат',
        };
    }
}
