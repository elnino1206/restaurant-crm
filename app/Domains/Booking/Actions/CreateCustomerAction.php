<?php

namespace App\Domains\Booking\Actions;

use App\Domains\Booking\DTO\CreateCustomerDTO;
use App\Domains\Booking\Models\Customer;

class CreateCustomerAction
{
    public function handle(CreateCustomerDTO $dto): Customer
    {
        return Customer::create([
            'restaurant_id' => $dto->restaurantId,
            'name' => $dto->name,
            'phone' => $dto->phone,
            'email' => $dto->email,
            'telegram_id' => $dto->telegramId,
            'telegram_username' => $dto->telegramUsername,
        ]);
    }
}
