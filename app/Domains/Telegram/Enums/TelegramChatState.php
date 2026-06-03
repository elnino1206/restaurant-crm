<?php

namespace App\Domains\Telegram\Enums;

enum TelegramChatState: string
{
    // Начальное состояние — нет активного диалога
    case Idle = 'idle';

    // FSM бронирования
    case AwaitingDate = 'awaiting_date';
    case AwaitingTime = 'awaiting_time';
    case AwaitingGuests = 'awaiting_guests';
    case AwaitingName = 'awaiting_name';
    case AwaitingPhone = 'awaiting_phone';
    case AwaitingComment = 'awaiting_comment';
    case AwaitingConfirm = 'awaiting_confirm';

    // FSM отмены бронирования
    case AwaitingCancel = 'awaiting_cancel';
    case AwaitingCancelConfirm = 'awaiting_cancel_confirm';

    public function label(): string
    {
        return match ($this) {
            self::Idle => 'Ожидание',
            self::AwaitingDate => 'Ввод даты',
            self::AwaitingTime => 'Ввод времени',
            self::AwaitingGuests => 'Ввод числа гостей',
            self::AwaitingName => 'Ввод имени',
            self::AwaitingPhone => 'Ввод телефона',
            self::AwaitingComment => 'Ввод комментария',
            self::AwaitingConfirm => 'Подтверждение брони',
            self::AwaitingCancel => 'Выбор брони для отмены',
            self::AwaitingCancelConfirm => 'Подтверждение отмены',
        };
    }

    /**
     * Следующее состояние FSM бронирования.
     */
    public function nextBookingStep(): ?self
    {
        return match ($this) {
            self::Idle => self::AwaitingDate,
            self::AwaitingDate => self::AwaitingTime,
            self::AwaitingTime => self::AwaitingGuests,
            self::AwaitingGuests => self::AwaitingName,
            self::AwaitingName => self::AwaitingPhone,
            self::AwaitingPhone => self::AwaitingComment,
            self::AwaitingComment => self::AwaitingConfirm,
            default => null,
        };
    }

    public function isBookingFlow(): bool
    {
        return in_array($this, [
            self::AwaitingDate,
            self::AwaitingTime,
            self::AwaitingGuests,
            self::AwaitingName,
            self::AwaitingPhone,
            self::AwaitingComment,
            self::AwaitingConfirm,
        ]);
    }

    public function isCancellationFlow(): bool
    {
        return in_array($this, [
            self::AwaitingCancel,
            self::AwaitingCancelConfirm,
        ]);
    }
}
