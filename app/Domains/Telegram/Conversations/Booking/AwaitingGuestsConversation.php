<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Telegram\Conversations\Concerns\HandlesFailedAttempts;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;

class AwaitingGuestsConversation
{
    use HandlesFailedAttempts;

    private const MAX_GUESTS = 20;

    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $input = trim($bot->message()?->text ?? '');
        $guests = filter_var($input, FILTER_VALIDATE_INT);

        if ($guests === false || $guests < 1 || $guests > self::MAX_GUESTS) {
            $this->failAttempt(
                $bot,
                $conversation,
                '⚠️ Введите число гостей от 1 до '.self::MAX_GUESTS,
            );

            return;
        }

        $pm = app(PayloadManager::class);
        $pm->set($conversation, 'guests', $guests);
        $pm->resetAttempts($conversation);

        $conversation->update(['state' => TelegramChatState::AwaitingName]);

        $bot->sendMessage(
            "✅ Гостей: {$guests}\n\n".
            '👤 Введите ваше имя'
        );
    }
}
