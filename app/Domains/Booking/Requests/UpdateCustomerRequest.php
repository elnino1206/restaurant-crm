<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\UpdateCustomerDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['sometimes', 'string', 'max:200'],
            'phone'       => ['sometimes', 'nullable', 'string', 'max:20'],
            'email'       => ['sometimes', 'nullable', 'email', 'max:200'],
            'preferences' => ['sometimes', 'nullable', 'array'],
            'notes'       => ['sometimes', 'nullable', 'string', 'max:1000'],
        ];
    }

    public function toDTO(): UpdateCustomerDTO
    {
        return UpdateCustomerDTO::from([
            'name'        => $this->input('name'),
            'phone'       => $this->input('phone'),
            'email'       => $this->input('email'),
            'preferences' => $this->input('preferences'),
            'notes'       => $this->input('notes'),
        ]);
    }
}
