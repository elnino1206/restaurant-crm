<?php

namespace App\Domains\Booking\Policies;

use App\Domains\Booking\Models\Customer;
use App\Domains\User\Enums\UserRole;
use App\Models\User;

class CustomerPolicy
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

    public function view(User $user, Customer $customer): bool
    {
        // restaurant_id check
        return $user->restaurant_id === $customer->restaurant_id;
    }

    public function create(User $user): bool
    {
        return $user->restaurant_id !== null
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->restaurant_id === $customer->restaurant_id
            && in_array($user->role, [UserRole::Owner, UserRole::Manager]);
    }
}
