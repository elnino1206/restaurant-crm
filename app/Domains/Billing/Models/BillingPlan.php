<?php

namespace App\Domains\Billing\Models;

use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BillingPlan extends Model
{
    use HasUuid;

    protected $fillable = [
        'name',
        'slug',
        'currency',
        'price_monthly',
        'price_yearly',
        'limits',
        'features',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price_monthly' => 'integer',
            'price_yearly' => 'integer',
            'limits' => 'array',
            'features' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(BillingSubscription::class, 'plan_id');
    }
}
