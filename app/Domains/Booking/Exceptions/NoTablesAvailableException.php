<?php

namespace App\Domains\Booking\Exceptions;

use App\Domains\Booking\DTO\AllocateTableDTO;
use App\Shared\Exceptions\DomainException;

class NoTablesAvailableException extends DomainException
{
    public static function forRequest(AllocateTableDTO $dto): self
    {
        return new self(
            "No available table for {$dto->guestsCount} guests"
            ." from {$dto->bookingStart->toDateTimeString()}"
            ." to {$dto->bookingEnd->toDateTimeString()}."
        );
    }
}
