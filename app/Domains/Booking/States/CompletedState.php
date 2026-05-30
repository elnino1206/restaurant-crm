<?php

namespace App\Domains\Booking\States;

class CompletedState extends BookingState
{
    public static string $name = 'completed';

    public function label(): string
    {
        return 'Завершено';
    }
}
