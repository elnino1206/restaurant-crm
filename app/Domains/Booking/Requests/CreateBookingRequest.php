<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\Actions\FindOrCreateCustomerAction;
use App\Domains\Booking\DTO\CreateBookingDTO;
use App\Domains\Booking\DTO\CreateCustomerDTO;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Restaurant\Models\Restaurant;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
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
            'guests_count' => ['required', 'integer', 'min:1', 'max:100'],
            'booking_start' => ['required', 'date', 'after:now'],
            'booking_end' => ['nullable', 'date', 'after:booking_start'],
            'table_id' => ['nullable', 'uuid'],
            'customer_id' => ['nullable', 'uuid', 'required_without:customer_phone'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30', 'required_without:customer_id'],
            'comment' => ['nullable', 'string', 'max:500'],
            'source' => ['nullable', Rule::enum(BookingSource::class)],
        ];
    }

    public function toDTO(): CreateBookingDTO
    {
        $restaurantId = auth()->user()->restaurant_id;
        $timezone = Restaurant::find($restaurantId)?->timezone ?? 'UTC';

        $customerId = $this->input('customer_id');

        if (! $customerId && $this->filled('customer_phone')) {
            $customer = app(FindOrCreateCustomerAction::class)->handle(
                CreateCustomerDTO::from([
                    'restaurantId' => $restaurantId,
                    'name' => $this->input('customer_name', 'Гость'),
                    'phone' => $this->input('customer_phone'),
                ])
            );

            $customerId = $customer->id;
        }

        return CreateBookingDTO::from([
            'restaurantId' => $restaurantId,
            'guestsCount' => $this->integer('guests_count'),
            'bookingStart' => Carbon::parse($this->input('booking_start'), $timezone)->utc(),
            'bookingEnd' => $this->filled('booking_end')
                ? Carbon::parse($this->input('booking_end'), $timezone)->utc()
                : null,
            'tableId' => $this->input('table_id'),
            'customerId' => $customerId,
            'comment' => $this->input('comment'),
            'source' => $this->input('source', BookingSource::Web->value),
            'createdBy' => auth()->id(),
        ]);
    }
}
