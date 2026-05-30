<?php

namespace App\Domains\Telegram\Models;

use App\Domains\Restaurant\Models\Restaurant;
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
}
