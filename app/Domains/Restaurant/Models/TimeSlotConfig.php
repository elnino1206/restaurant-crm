<?php

namespace App\Domains\Restaurant\Models;

use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeSlotConfig extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'day_of_week',
        'open_time',
        'close_time',
        'slot_duration',
        'booking_duration',
        'is_day_off',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'slot_duration' => 'integer',
            'booking_duration' => 'integer',
            'is_day_off' => 'boolean',
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
