<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\CreateBookingDTO;
use App\Domains\Booking\Enums\BookingSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // handled by Policy in controller
    }

    public function rules(): array
    {
        return [
            'guests_count'  => ['required', 'integer', 'min:1', 'max:100'],
            'booking_start' => ['required', 'date', 'after:now'],
            'booking_end'   => ['required', 'date', 'after:booking_start'],
            'table_id'      => ['nullable', 'uuid'],
            'customer_id'   => ['nullable', 'uuid'],
            'comment'       => ['nullable', 'string', 'max:500'],
            'source'        => ['nullable', Rule::enum(BookingSource::class)],
        ];
    }

    public function toDTO(): CreateBookingDTO
    {
        return CreateBookingDTO::from([
            'restaurantId' => auth()->user()->restaurant_id,
            'guestsCount'  => $this->integer('guests_count'),
            'bookingStart' => $this->input('booking_start'),
            'bookingEnd'   => $this->input('booking_end'),
            'tableId'      => $this->input('table_id'),
            'customerId'   => $this->input('customer_id'),
            'comment'      => $this->input('comment'),
            'source'       => $this->input('source', BookingSource::Web->value),
            'createdBy'    => auth()->id(),
        ]);
    }
}
