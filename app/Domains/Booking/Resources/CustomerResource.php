<?php

namespace App\Domains\Booking\Resources;

use App\Shared\Http\ApiResource;
use Illuminate\Http\Request;

class CustomerResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'telegram_id' => $this->telegram_id,
            'telegram_username' => $this->telegram_username,
            'preferences' => $this->preferences,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
