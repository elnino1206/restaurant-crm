<?php

namespace App\Domains\Telegram\Services;

use App\Domains\Telegram\Conversations\Booking\AwaitingCommentConversation;
use App\Domains\Telegram\Conversations\Booking\AwaitingConfirmConversation;
use App\Domains\Telegram\Conversations\Booking\AwaitingDateConversation;
use App\Domains\Telegram\Conversations\Booking\AwaitingGuestsConversation;
use App\Domains\Telegram\Conversations\Booking\AwaitingNameConversation;
use App\Domains\Telegram\Conversations\Booking\AwaitingPhoneConversation;
use App\Domains\Telegram\Conversations\Booking\AwaitingTimeConversation;
use App\Domains\Telegram\Conversations\Cancellation\AwaitingCancelConfirmConversation;
use App\Domains\Telegram\Conversations\Cancellation\AwaitingCancelConversation;
use App\Domains\Telegram\Enums\TelegramChatState;

class ConversationResolver
{
    /**
     * @var array<string, class-string>
     */
    private array $map = [
        TelegramChatState::AwaitingDate->value => AwaitingDateConversation::class,
        TelegramChatState::AwaitingTime->value => AwaitingTimeConversation::class,
        TelegramChatState::AwaitingGuests->value => AwaitingGuestsConversation::class,
        TelegramChatState::AwaitingName->value => AwaitingNameConversation::class,
        TelegramChatState::AwaitingPhone->value => AwaitingPhoneConversation::class,
        TelegramChatState::AwaitingComment->value => AwaitingCommentConversation::class,
        TelegramChatState::AwaitingConfirm->value => AwaitingConfirmConversation::class,
        TelegramChatState::AwaitingCancel->value => AwaitingCancelConversation::class,
        TelegramChatState::AwaitingCancelConfirm->value => AwaitingCancelConfirmConversation::class,
    ];

    /**
     * Возвращает класс Conversation для заданного состояния.
     * Возвращает null если состояние — Idle или неизвестное.
     *
     * @return class-string|null
     */
    public function resolve(TelegramChatState $state): ?string
    {
        return $this->map[$state->value] ?? null;
    }

    public function hasConversation(TelegramChatState $state): bool
    {
        return isset($this->map[$state->value]);
    }
}
