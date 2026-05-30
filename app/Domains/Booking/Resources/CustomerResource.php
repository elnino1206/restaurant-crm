<?php

namespace App\Domains\Booking\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'phone'            => $this->phone,
            'email'            => $this->email,
            'telegram_id'      => $this->telegram_id,
            'telegram_username' => $this->telegram_username,
            'preferences'      => $this->preferences,
            'notes'            => $this->notes,
            'created_at'       => $this->created_at,
        ];
    }
}
