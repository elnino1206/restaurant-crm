<?php

namespace App\Domains\Restaurant\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeSlotConfigResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'day_of_week'      => $this->day_of_week,
            'open_time'        => $this->open_time,
            'close_time'       => $this->close_time,
            'slot_duration'    => $this->slot_duration,
            'booking_duration' => $this->booking_duration,
            'is_day_off'       => $this->is_day_off,
        ];
    }
}
