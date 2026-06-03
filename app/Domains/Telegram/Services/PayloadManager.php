<?php

namespace App\Domains\Telegram\Services;

use App\Domains\Telegram\Models\TelegramConversation;

class PayloadManager
{
    public function set(TelegramConversation $conversation, string $key, mixed $value): void
    {
        $payload = $conversation->payload ?? [];
        $payload[$key] = $value;
        $conversation->update(['payload' => $payload]);
        $conversation->setRawAttributes(array_merge($conversation->getRawOriginal(), ['payload' => json_encode($payload)]));
        $conversation->syncOriginal();
    }

    public function get(TelegramConversation $conversation, string $key, mixed $default = null): mixed
    {
        return $conversation->payload[$key] ?? $default;
    }

    public function has(TelegramConversation $conversation, string $key): bool
    {
        return isset($conversation->payload[$key]);
    }

    public function forget(TelegramConversation $conversation, string $key): void
    {
        $payload = $conversation->payload ?? [];
        unset($payload[$key]);
        $conversation->update(['payload' => $payload]);
    }

    public function clear(TelegramConversation $conversation): void
    {
        $conversation->update(['payload' => []]);
    }

    /**
     * Увеличивает счётчик неудачных попыток и возвращает новое значение.
     */
    public function incrementAttempts(TelegramConversation $conversation): int
    {
        $attempts = ($conversation->failed_attempts ?? 0) + 1;
        $conversation->update(['failed_attempts' => $attempts]);

        return $attempts;
    }

    public function resetAttempts(TelegramConversation $conversation): void
    {
        $conversation->update(['failed_attempts' => 0]);
    }

    /**
     * Возвращает все накопленные данные для создания брони.
     *
     * @return array{date?: string, time?: string, guests?: int, name?: string, phone?: string, comment?: string}
     */
    public function getBookingData(TelegramConversation $conversation): array
    {
        $payload = $conversation->payload ?? [];

        return array_filter([
            'date' => $payload['date'] ?? null,
            'time' => $payload['time'] ?? null,
            'guests' => $payload['guests'] ?? null,
            'name' => $payload['name'] ?? null,
            'phone' => $payload['phone'] ?? null,
            'comment' => $payload['comment'] ?? null,
        ], fn ($v) => $v !== null);
    }
}
