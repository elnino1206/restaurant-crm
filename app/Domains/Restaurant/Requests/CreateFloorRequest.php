<?php

namespace App\Domains\Restaurant\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:200'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
