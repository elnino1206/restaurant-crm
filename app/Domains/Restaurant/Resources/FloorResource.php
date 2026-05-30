<?php

namespace App\Domains\Restaurant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FloorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'sort_order' => $this->sort_order,
            'tables'     => TableResource::collection($this->whenLoaded('tables')),
        ];
    }
}
