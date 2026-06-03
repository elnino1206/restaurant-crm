<?php

namespace App\Domains\Booking\DTO;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class AllocateTableDTO extends Data
{
    public function __construct(
        public readonly string $restaurantId,
        public readonly int $guestsCount,
        public readonly Carbon $bookingStart,
        public readonly ?Carbon $bookingEnd = null,
        public readonly ?string $preferredTableId = null,
        public readonly array $excludeTableIds = [],
    ) {}
}
