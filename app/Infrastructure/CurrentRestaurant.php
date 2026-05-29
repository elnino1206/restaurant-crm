<?php

namespace App\Infrastructure;

use Illuminate\Support\Facades\Auth;

class CurrentRestaurant
{
    public static function id(): ?string
    {
        $user = Auth::user();

        if (! $user) {
            return null;
        }

        if ($user->hasRole('super_admin')) {
            return null;
        }

        return $user->restaurant_id;
    }
}
