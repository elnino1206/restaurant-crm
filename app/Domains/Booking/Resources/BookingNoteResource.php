<?php

namespace App\Domains\Booking\Resources;

use App\Shared\Http\ApiResource;
use Illuminate\Http\Request;

class BookingNoteResource extends ApiResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content' => $this->content,
            'created_at' => $this->created_at,
        ];
    }
}
