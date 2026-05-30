<?php

namespace App\Domains\Restaurant\Models;

use App\Domains\Booking\Models\Booking;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Table extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'floor_id',
        'number',
        'capacity',
        'min_capacity',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'min_capacity' => 'integer',
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

    public function floor(): BelongsTo
    {
        return $this->belongsTo(Floor::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
