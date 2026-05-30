<?php

namespace App\Domains\Booking\States;

class NoShowState extends BookingState
{
    public static string $name = 'no_show';

    public function label(): string
    {
        return 'Не явился';
    }
}
