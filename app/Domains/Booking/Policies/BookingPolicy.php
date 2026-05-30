<?php

namespace App\Domains\Booking\Policies;

use App\Domains\Booking\Models\Booking;
use App\Domains\User\Enums\UserRole;
use App\Models\User;

class BookingPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        // restaurant_id check: user must belong to a restaurant
        return $user->restaurant_id !== null
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function view(User $user, Booking $booking): bool
    {
        // restaurant_id check
        return $user->restaurant_id === $booking->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->restaurant_id !== null
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function update(User $user, Booking $booking): bool
    {
        return $user->restaurant_id === $booking->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function confirm(User $user, Booking $booking): bool
    {
        return $user->restaurant_id === $booking->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function cancel(User $user, Booking $booking): bool
    {
        return $user->restaurant_id === $booking->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function complete(User $user, Booking $booking): bool
    {
        return $user->restaurant_id === $booking->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function markNoShow(User $user, Booking $booking): bool
    {
        return $user->restaurant_id === $booking->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function reassign(User $user, Booking $booking): bool
    {
        return $user->restaurant_id === $booking->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }
}
