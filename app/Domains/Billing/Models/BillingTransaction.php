<?php

namespace App\Domains\Billing\Models;

use App\Domains\Billing\Enums\TransactionStatus;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BillingTransaction extends Model
{
    use HasUuid;

    protected $fillable = [
        'subscription_id',
        'amount',
        'currency',
        'status',
        'gateway',
        'gateway_transaction_id',
        'gateway_response',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => TransactionStatus::class,
            'amount' => 'integer',
            'gateway_response' => 'array',
            'processed_at' => 'datetime',
        ];
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(BillingSubscription::class, 'subscription_id');
    }
}
