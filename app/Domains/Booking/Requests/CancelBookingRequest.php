<?php

namespace App\Domains\Booking\Requests;

use App\Domains\Booking\DTO\CancelBookingDTO;
use Illuminate\Foundation\Http\FormRequest;

class CancelBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reason' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function toDTO(string $bookingId): CancelBookingDTO
    {
        return CancelBookingDTO::from([
            'bookingId'   => $bookingId,
            'reason'      => $this->input('reason'),
            'cancelledBy' => auth()->id(),
        ]);
    }
}
