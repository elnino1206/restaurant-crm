<?php

namespace App\Domains\Restaurant\Requests;

use App\Domains\Restaurant\DTO\UpdateRestaurantDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRestaurantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => ['sometimes', 'string', 'max:200'],
            'timezone'  => ['sometimes', 'string', 'timezone'],
            'phone'     => ['sometimes', 'nullable', 'string', 'max:20'],
            'address'   => ['sometimes', 'nullable', 'string', 'max:500'],
            'settings'  => ['sometimes', 'nullable', 'array'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function toDTO(): UpdateRestaurantDTO
    {
        return UpdateRestaurantDTO::from([
            'name'     => $this->input('name'),
            'timezone' => $this->input('timezone'),
            'phone'    => $this->input('phone'),
            'address'  => $this->input('address'),
            'settings' => $this->input('settings'),
            'isActive' => $this->boolean('is_active'),
        ]);
    }
}
