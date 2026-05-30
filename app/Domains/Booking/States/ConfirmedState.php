<?php

namespace App\Domains\Booking\States;

class ConfirmedState extends BookingState
{
    public static string $name = 'confirmed';

    public function label(): string
    {
        return 'Подтверждено';
    }
}
