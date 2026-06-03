<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Telegram\Conversations\Concerns\HandlesFailedAttempts;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Carbon;
use SergiX44\Nutgram\Nutgram;

class AwaitingDateConversation
{
    use HandlesFailedAttempts;

    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $input = trim($bot->message()?->text ?? '');
        $date = $this->parseDate($input);

        if ($date === null) {
            $this->failAttempt(
                $bot,
                $conversation,
                "⚠️ Неверный формат даты.\nВведите дату в формате ДД.ММ.ГГГГ\nНапример: 25.07.2026",
            );

            return;
        }

        if ($date->isPast()) {
            $this->failAttempt($bot, $conversation, '⚠️ Дата уже прошла. Введите будущую дату.');

            return;
        }

        $pm = app(PayloadManager::class);
        $pm->set($conversation, 'date', $date->toDateString());
        $pm->resetAttempts($conversation);

        $conversation->update(['state' => TelegramChatState::AwaitingTime]);

        $bot->sendMessage(
            "✅ Дата: {$date->isoFormat('D MMMM YYYY')}\n\n".
            "🕐 Введите время бронирования в формате ЧЧ:ММ\n".
            'Например: 19:00'
        );
    }

    private function parseDate(string $input): ?Carbon
    {
        foreach (['d.m.Y', 'd/m/Y', 'd-m-Y', 'Y-m-d'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $input);
                if ($date && $date->format($format) === $input) {
                    return $date->startOfDay();
                }
            } catch (InvalidFormatException) {
                continue;
            }
        }

        return null;
    }
}
