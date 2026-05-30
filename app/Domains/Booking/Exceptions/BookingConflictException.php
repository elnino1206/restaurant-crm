<?php

namespace App\Domains\Booking\Exceptions;

use App\Shared\Exceptions\DomainException;
use Illuminate\Support\Carbon;

class BookingConflictException extends DomainException
{
    public static function forTable(string $tableId, Carbon $start): self
    {
        return new self(
            "Table [{$tableId}] is already booked at {$start->toDateTimeString()}."
        );
    }
}
