<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\DTO\CreateCustomerDTO;
use App\Domains\Booking\Models\Customer;

class FindOrCreateCustomerAction
{
    public function handle(CreateCustomerDTO $dto): Customer
    {
        if ($dto->telegramId) {
            $customer = Customer::withoutGlobalScopes()
                ->where('restaurant_id', $dto->restaurantId)
                ->where('telegram_id', $dto->telegramId)
                ->first();

            if ($customer) {
                return $customer;
            }
        }

        if ($dto->phone) {
            $customer = Customer::withoutGlobalScopes()
                ->where('restaurant_id', $dto->restaurantId)
                ->where('phone', $dto->phone)
                ->first();

            if ($customer) {
                return $customer;
            }
        }

        return app(CreateCustomerAction::class)->handle($dto);
    }
}
