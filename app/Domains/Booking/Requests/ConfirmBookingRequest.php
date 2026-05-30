<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\ConfirmBookingDTO;
use Illuminate\Foundation\Http\FormRequest;

class ConfirmBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function toDTO(string $bookingId): ConfirmBookingDTO
    {
        return ConfirmBookingDTO::from([
            'bookingId'   => $bookingId,
            'confirmedBy' => auth()->id(),
        ]);
    }
}
