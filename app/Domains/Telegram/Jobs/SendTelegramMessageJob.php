<?php

namespace App\Domains\Telegram\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class SendTelegramMessageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 10;

    public function __construct(
        public readonly string $botToken,
        public readonly int|string $chatId,
        public readonly string $text,
        public readonly ?array $replyMarkup = null,
    ) {}

    public function handle(): void
    {
        $bot = new Nutgram($this->botToken);

        $params = ['text' => $this->text];

        if ($this->replyMarkup !== null) {
            $params['reply_markup'] = $this->replyMarkup;
        }

        $bot->sendMessage($this->text, chat_id: $this->chatId);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendTelegramMessageJob failed', [
            'chat_id' => $this->chatId,
            'error' => $e->getMessage(),
        ]);
    }
}
