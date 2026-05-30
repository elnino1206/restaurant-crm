<?php

namespace App\Domains\Restaurant\Resources;

use App\Shared\Http\ApiResource;
use Illuminate\Http\Request;

class TableResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'capacity' => $this->capacity,
            'min_capacity' => $this->min_capacity,
            'is_active' => $this->is_active,
            'floor_id' => $this->floor_id,
        ];
    }
}
