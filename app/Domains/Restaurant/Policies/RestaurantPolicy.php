<?php

namespace App\Domains\Restaurant\Policies;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\User\Enums\UserRole;
use App\Models\User;

class RestaurantPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function view(User $user, Restaurant $restaurant): bool
    {
        // restaurant_id check
        return $user->restaurant_id === $restaurant->id;
    }

    public function update(User $user, Restaurant $restaurant): bool
    {
        return $user->restaurant_id === $restaurant->id
            && $user->role === UserRole::Owner;
    }

    public function manageTables(User $user, Restaurant $restaurant): bool
    {
        return $user->restaurant_id === $restaurant->id
            && $user->role === UserRole::Owner;
    }

    public function manageFloors(User $user, Restaurant $restaurant): bool
    {
        return $user->restaurant_id === $restaurant->id
            && $user->role === UserRole::Owner;
    }

    public function manageUsers(User $user, Restaurant $restaurant): bool
    {
        return $user->restaurant_id === $restaurant->id
            && $user->role === UserRole::Owner;
    }

    public function manageBots(User $user, Restaurant $restaurant): bool
    {
        return $user->restaurant_id === $restaurant->id
            && $user->role === UserRole::Owner;
    }

    public function viewAnalytics(User $user, Restaurant $restaurant): bool
    {
        return $user->restaurant_id === $restaurant->id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }
}
