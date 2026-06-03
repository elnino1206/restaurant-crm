<?php

namespace App\Domains\Telegram\Actions;

use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;

class ResolveOrCreateConversationAction
{
    public function handle(string $restaurantId, int $telegramUserId): TelegramConversation
    {
        return TelegramConversation::withoutGlobalScopes()
            ->firstOrCreate(
                [
                    'restaurant_id' => $restaurantId,
                    'telegram_user_id' => $telegramUserId,
                ],
                [
                    'state' => TelegramChatState::Idle,
                    'payload' => [],
                    'failed_attempts' => 0,
                ]
            );
    }
}
