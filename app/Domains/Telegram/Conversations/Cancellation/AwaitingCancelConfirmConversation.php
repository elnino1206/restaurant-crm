<?php

namespace App\Domains\Telegram\Conversations\Cancellation;

use App\Domains\Telegram\Models\TelegramConversation;
use SergiX44\Nutgram\Nutgram;

/** Stub — будет реализован в Фазе 6 (FSM Conversations) */
class AwaitingCancelConfirmConversation
{
    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        // TODO: implement in Phase 6
    }
}
