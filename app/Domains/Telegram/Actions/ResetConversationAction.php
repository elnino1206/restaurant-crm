<?php

namespace App\Domains\Telegram\Actions;

use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;

class ResetConversationAction
{
    public function handle(TelegramConversation $conversation): TelegramConversation
    {
        $conversation->update([
            'state' => TelegramChatState::Idle,
            'payload' => [],
            'failed_attempts' => 0,
        ]);

        return $conversation->refresh();
    }
}
