<?php

namespace App\Domains\Billing\Enums;

enum BillingPeriod: string
{
    case Monthly = 'monthly';
    case Yearly = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Ежемесячно',
            self::Yearly => 'Ежегодно',
        };
    }
}
