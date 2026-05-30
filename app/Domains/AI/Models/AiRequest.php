<?php

namespace App\Domains\AI\Models;

use App\Domains\AI\Enums\AiRequestStatus;
use App\Domains\Booking\Models\Booking;
use App\Domains\Restaurant\Models\Restaurant;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiRequest extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'booking_id',
        'model',
        'prompt',
        'response',
        'input_tokens',
        'output_tokens',
        'status',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiRequestStatus::class,
            'input_tokens' => 'integer',
            'output_tokens' => 'integer',
            'processed_at' => 'datetime',
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

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
