<?php

namespace App\Domains\Telegram\Models;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TelegramConversation extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'telegram_user_id',
        'state',
        'payload',
        'failed_attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'telegram_user_id' => 'integer',
            'state' => TelegramChatState::class,
            'payload' => 'array',
            'failed_attempts' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new RestaurantScope);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function isIdle(): bool
    {
        return $this->state === TelegramChatState::Idle;
    }

    public function isInBookingFlow(): bool
    {
        return $this->state->isBookingFlow();
    }

    public function isInCancellationFlow(): bool
    {
        return $this->state->isCancellationFlow();
    }
}
