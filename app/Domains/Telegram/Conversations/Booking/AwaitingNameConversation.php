<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Telegram\Conversations\Concerns\HandlesFailedAttempts;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;

class AwaitingNameConversation
{
    use HandlesFailedAttempts;

    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $input = trim($bot->message()?->text ?? '');

        if (mb_strlen($input) < 2 || mb_strlen($input) > 100) {
            $this->failAttempt(
                $bot,
                $conversation,
                '⚠️ Имя должно быть от 2 до 100 символов',
            );

            return;
        }

        $pm = app(PayloadManager::class);
        $pm->set($conversation, 'name', $input);
        $pm->resetAttempts($conversation);

        $conversation->update(['state' => TelegramChatState::AwaitingPhone]);

        $bot->sendMessage(
            "✅ Имя: {$input}\n\n".
            "📱 Введите номер телефона\n".
            'Например: +79001234567'
        );
    }
}
