<?php

namespace App\Domains\Telegram\Models;

use App\Domains\Restaurant\Models\Restaurant;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestaurantBot extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'token',
        'username',
        'webhook_url',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
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
