<?php

namespace App\Domains\Booking\DTO;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class SlotQueryDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly Carbon $date,
        public readonly int $guestsCount,
        public readonly int $durationMinutes,
    ) {}
}
