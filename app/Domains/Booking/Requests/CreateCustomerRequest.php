<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\CreateCustomerDTO;
use Illuminate\Foundation\Http\FormRequest;

class CreateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:200'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'email'            => ['nullable', 'email', 'max:200'],
            'telegram_id'      => ['nullable', 'integer'],
            'telegram_username' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function toDTO(): CreateCustomerDTO
    {
        return CreateCustomerDTO::from([
            'restaurantId'    => auth()->user()->restaurant_id,
            'name'            => $this->input('name'),
            'phone'           => $this->input('phone'),
            'email'           => $this->input('email'),
            'telegramId'      => $this->input('telegram_id'),
            'telegramUsername' => $this->input('telegram_username'),
        ]);
    }
}
