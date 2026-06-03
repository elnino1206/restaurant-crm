<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Telegram\Conversations\Concerns\HandlesFailedAttempts;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;

class AwaitingPhoneConversation
{
    use HandlesFailedAttempts;

    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $input = trim($bot->message()?->text ?? '');
        $phone = $this->normalizePhone($input);

        if ($phone === null) {
            $this->failAttempt(
                $bot,
                $conversation,
                "⚠️ Неверный формат номера.\nВведите номер телефона\nНапример: +79001234567",
            );

            return;
        }

        $pm = app(PayloadManager::class);
        $pm->set($conversation, 'phone', $phone);
        $pm->resetAttempts($conversation);

        $conversation->update(['state' => TelegramChatState::AwaitingComment]);

        $bot->sendMessage(
            "✅ Телефон: {$phone}\n\n".
            "💬 Есть пожелания или комментарий? (необязательно)\n".
            'Напишите текст или отправьте /skip чтобы пропустить'
        );
    }

    private function normalizePhone(string $input): ?string
    {
        $digits = preg_replace('/\D/', '', $input);

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7'.substr($digits, 1);
        }

        if (strlen($digits) < 10 || strlen($digits) > 15) {
            return null;
        }

        return '+'.$digits;
    }
}
