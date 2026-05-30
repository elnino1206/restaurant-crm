<?php

namespace App\Domains\Restaurant\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFloorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:200'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
