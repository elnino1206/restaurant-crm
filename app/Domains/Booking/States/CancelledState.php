<?php

namespace App\Domains\Booking\States;

class CancelledState extends BookingState
{
    public static string $name = 'cancelled';

    public function label(): string
    {
        return 'Отменено';
    }
}
