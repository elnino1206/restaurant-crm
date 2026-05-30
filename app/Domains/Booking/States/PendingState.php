<?php

namespace App\Domains\Booking\States;

class PendingState extends BookingState
{
    public static string $name = 'pending';

    public function label(): string
    {
        return 'Ожидает подтверждения';
    }
}
