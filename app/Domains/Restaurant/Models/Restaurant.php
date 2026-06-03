<?php

namespace App\Domains\Restaurant\Models;

use App\Domains\Billing\Models\BillingSubscription;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\Customer;
use App\Domains\Telegram\Models\RestaurantBot;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Models\User;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Restaurant extends Model
{
    use HasUuid, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'timezone',
        'phone',
        'address',
        'settings',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function bot(): HasOne
    {
        return $this->hasOne(RestaurantBot::class)->where('is_active', true)->latestOfMany();
    }

    public function bots(): HasMany
    {
        return $this->hasMany(RestaurantBot::class);
    }

    public function floors(): HasMany
    {
        return $this->hasMany(Floor::class);
    }

    public function tables(): HasMany
    {
        return $this->hasMany(Table::class);
    }

    public function timeSlotConfigs(): HasMany
    {
        return $this->hasMany(TimeSlotConfig::class);
    }

    public function closedDates(): HasMany
    {
        return $this->hasMany(ClosedDate::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(TelegramConversation::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(BillingSubscription::class)->latestOfMany();
    }
}
