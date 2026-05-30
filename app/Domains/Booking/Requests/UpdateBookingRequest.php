<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\UpdateBookingDTO;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guests_count'  => ['sometimes', 'integer', 'min:1', 'max:100'],
            'booking_start' => ['sometimes', 'date'],
            'booking_end'   => ['sometimes', 'date', 'after:booking_start'],
            'table_id'      => ['sometimes', 'nullable', 'uuid'],
            'comment'       => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function toDTO(): UpdateBookingDTO
    {
        return UpdateBookingDTO::from([
            'guestsCount'  => $this->input('guests_count'),
            'bookingStart' => $this->input('booking_start'),
            'bookingEnd'   => $this->input('booking_end'),
            'tableId'      => $this->input('table_id'),
            'comment'      => $this->input('comment'),
        ]);
    }
}
