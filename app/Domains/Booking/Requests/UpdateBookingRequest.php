<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\UpdateBookingDTO;
use App\Domains\Restaurant\Models\Restaurant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guests_count' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'booking_start' => ['sometimes', 'date'],
            'booking_end' => ['sometimes', 'nullable', 'date', 'after:booking_start'],
            'table_id' => ['sometimes', 'nullable', 'uuid'],
            'comment' => ['sometimes', 'nullable', 'string', 'max:500'],
        ];
    }

    public function toDTO(): UpdateBookingDTO
    {
        $timezone = Restaurant::find(auth()->user()->restaurant_id)?->timezone ?? 'UTC';

        $hasBookingEnd = $this->has('booking_end');
        $filledBookingEnd = $this->filled('booking_end');

        return UpdateBookingDTO::from([
            'guestsCount' => $this->input('guests_count'),
            'bookingStart' => $this->filled('booking_start')
                ? Carbon::parse($this->input('booking_start'), $timezone)->utc()
                : null,
            'bookingEnd' => ($hasBookingEnd && $filledBookingEnd)
                ? Carbon::parse($this->input('booking_end'), $timezone)->utc()
                : null,
            'clearBookingEnd' => $hasBookingEnd && ! $filledBookingEnd,
            'tableId' => $this->input('table_id'),
            'comment' => $this->input('comment'),
        ]);
    }
}
