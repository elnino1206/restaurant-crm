<?php

namespace App\Domains\Notification\Models;

use App\Domains\Notification\Enums\NotificationChannel;
use App\Domains\Notification\Enums\NotificationStatus;
use App\Domains\Restaurant\Models\Restaurant;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'notifiable_type',
        'notifiable_id',
        'channel',
        'notification_type',
        'data',
        'sent_at',
        'status',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            'status' => NotificationStatus::class,
            'data' => 'array',
            'sent_at' => 'datetime',
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

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
