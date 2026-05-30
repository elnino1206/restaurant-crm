<?php

namespace App\Domains\User\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case Owner = 'owner';
    case Manager = 'manager';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Суперадмин',
            self::Owner => 'Владелец',
            self::Manager => 'Менеджер',
        };
    }
}
