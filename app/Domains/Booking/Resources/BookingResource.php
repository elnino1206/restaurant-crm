<?php

namespace App\Domains\Booking\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'     => $this->id,
            'status' => [
                'value' => $this->status->getValue(),
                'label' => $this->status->label(),
            ],
            'source'        => [
                'value' => $this->source->value,
                'label' => $this->source->label(),
            ],
            'guests_count'  => $this->guests_count,
            'booking_start' => $this->booking_start,
            'booking_end'   => $this->booking_end,
            'comment'       => $this->comment,
            'customer'      => CustomerResource::make($this->whenLoaded('customer')),
            'table'         => $this->whenLoaded('table', fn () => [
                'id'       => $this->table->id,
                'number'   => $this->table->number,
                'capacity' => $this->table->capacity,
            ]),
            'notes'      => BookingNoteResource::collection($this->whenLoaded('notes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
