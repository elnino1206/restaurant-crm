<?php

namespace App\Domains\Booking\Exceptions;

use App\Shared\Exceptions\DomainException;

class BookingNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self("Booking [{$id}] not found.");
    }
}
