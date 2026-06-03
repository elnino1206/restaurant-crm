<?php

namespace App\Domains\Notification\Jobs;

use App\Domains\Notification\DTO\SendNotificationDTO;
use App\Domains\Notification\Enums\NotificationChannel;
use App\Domains\Notification\Enums\NotificationStatus;
use App\Domains\Notification\Models\NotificationLog;
use App\Domains\Telegram\Jobs\SendTelegramMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendNotificationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly SendNotificationDTO $dto,
    ) {}

    public function handle(): void
    {
        $log = NotificationLog::create([
            'restaurant_id' => $this->dto->restaurantId,
            'customer_id' => $this->dto->customerId,
            'channel' => $this->dto->channel,
            'type' => $this->dto->type,
            'payload' => $this->dto->payload,
            'status' => NotificationStatus::Pending,
        ]);

        try {
            match ($this->dto->channel) {
                NotificationChannel::Telegram => $this->sendTelegram(),
            };

            $log->update(['status' => NotificationStatus::Sent, 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $log->update(['status' => NotificationStatus::Failed, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function sendTelegram(): void
    {
        if ($this->dto->telegramChatId === null || $this->dto->botToken === null) {
            throw new \RuntimeException('Telegram chatId or botToken missing');
        }

        SendTelegramMessageJob::dispatch(
            $this->dto->botToken,
            $this->dto->telegramChatId,
            $this->dto->payload['text'] ?? '',
        )->onQueue('high');
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendNotificationJob failed', ['error' => $e->getMessage()]);
    }
}
