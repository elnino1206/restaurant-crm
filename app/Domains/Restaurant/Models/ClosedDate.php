<?php

namespace App\Domains\Restaurant\Models;

use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClosedDate extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'date',
        'reason',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
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
