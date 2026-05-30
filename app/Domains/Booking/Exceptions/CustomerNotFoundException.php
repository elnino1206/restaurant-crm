<?php

namespace App\Domains\Booking\Exceptions;

use App\Shared\Exceptions\DomainException;

class CustomerNotFoundException extends DomainException
{
    public static function withId(string $id): self
    {
        return new self("Customer [{$id}] not found.");
    }
}
