<?php

namespace App\Domains\Booking\Models;

use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\States\BookingState;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Models\Table;
use App\Infrastructure\RestaurantScope;
use App\Models\User;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\ModelStates\HasStates;

class Booking extends Model
{
    use HasStates, HasUuid;

    protected $fillable = [
        'restaurant_id',
        'table_id',
        'customer_id',
        'created_by',
        'status',
        'guests_count',
        'booking_start',
        'booking_end',
        'comment',
        'source',
    ];

    protected function casts(): array
    {
        return [
            'status' => BookingState::class,
            'source' => BookingSource::class,
            'booking_start' => 'datetime',
            'booking_end' => 'datetime',
            'guests_count' => 'integer',
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

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function notes(): HasMany
    {
        return $this->hasMany(BookingNote::class);
    }
}
