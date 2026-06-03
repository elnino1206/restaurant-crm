<?php

namespace App\Domains\Telegram\Conversations\Concerns;

use App\Domains\Telegram\Actions\ResetConversationAction;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;

trait HandlesFailedAttempts
{
    private function failAttempt(
        Nutgram $bot,
        TelegramConversation $conversation,
        string $errorMessage,
        int $maxAttempts = 3,
    ): bool {
        $attempts = app(PayloadManager::class)->incrementAttempts($conversation);

        if ($attempts >= $maxAttempts) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage(
                "❌ Слишком много неверных попыток.\n".
                'Начнём сначала — напишите /start'
            );

            return true; // exceeded
        }

        $remaining = $maxAttempts - $attempts;
        $bot->sendMessage("{$errorMessage}\n\nОсталось попыток: {$remaining}");

        return false; // not exceeded yet
    }
}
