<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Telegram\Conversations\Concerns\HandlesFailedAttempts;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;

class AwaitingTimeConversation
{
    use HandlesFailedAttempts;

    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $input = trim($bot->message()?->text ?? '');

        if (! preg_match('/^([01]?\d|2[0-3]):([0-5]\d)$/', $input, $matches)) {
            $this->failAttempt(
                $bot,
                $conversation,
                "⚠️ Неверный формат времени.\nВведите время в формате ЧЧ:ММ\nНапример: 19:00",
            );

            return;
        }

        $pm = app(PayloadManager::class);
        $pm->set($conversation, 'time', $input);
        $pm->resetAttempts($conversation);

        $conversation->update(['state' => TelegramChatState::AwaitingGuests]);

        $bot->sendMessage(
            "✅ Время: {$input}\n\n".
            "👥 Сколько гостей?\n".
            'Введите число от 1 до 20'
        );
    }
}
