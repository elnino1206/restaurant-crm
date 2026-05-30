<?php

namespace App\Domains\Booking\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingNoteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'content'    => $this->content,
            'created_at' => $this->created_at,
        ];
    }
}
