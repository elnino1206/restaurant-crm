<?php

namespace App\Domains\Booking\Models;

use App\Domains\Restaurant\Models\Restaurant;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'name',
        'phone',
        'email',
        'telegram_id',
        'telegram_username',
        'preferences',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'telegram_id' => 'integer',
            'preferences' => 'array',
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

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
