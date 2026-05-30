<?php

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Enums\BillingPeriod;
use App\Domains\Billing\Enums\SubscriptionStatus;
use App\Domains\Restaurant\Models\Restaurant;
use App\Infrastructure\RestaurantScope;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingSubscription extends Model
{
    use HasUuid;

    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'status',
        'billing_period',
        'gateway',
        'gateway_subscription_id',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SubscriptionStatus::class,
            'billing_period' => BillingPeriod::class,
            'trial_ends_at' => 'datetime',
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function plan(): BelongsTo
    {
        return $this->belongsTo(BillingPlan::class, 'plan_id');
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BillingTransaction::class, 'subscription_id');
    }
}
