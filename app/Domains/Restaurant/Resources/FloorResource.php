<?php

namespace App\Domains\Restaurant\Resources;

use App\Shared\Http\ApiResource;
use Illuminate\Http\Request;

class FloorResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sort_order' => $this->sort_order,
            'tables' => TableResource::collection($this->whenLoaded('tables')),
        ];
    }
}
