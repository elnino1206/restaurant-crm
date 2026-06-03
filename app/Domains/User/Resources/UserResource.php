<?php

namespace App\Domains\User\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'login' => $this->login,
            'email' => $this->email,
            'role' => [
                'value' => $this->role->value,
                'label' => $this->role->label(),
            ],
        ];
    }
}
