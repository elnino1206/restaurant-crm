<?php

namespace App\Domains\Restaurant\Resources;

use App\Shared\Http\ApiResource;
use Illuminate\Http\Request;

class RestaurantResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'timezone' => $this->timezone,
            'phone' => $this->phone,
            'address' => $this->address,
            'settings' => $this->settings,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
        ];
    }
}
