<?php

namespace App\Domains\Booking\Models;

use App\Models\User;
use App\Shared\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingNote extends Model
{
    use HasUuid;

    protected $fillable = [
        'booking_id',
        'user_id',
        'content',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
