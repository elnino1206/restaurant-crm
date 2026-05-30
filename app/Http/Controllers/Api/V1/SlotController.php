<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\DTO\SlotQueryDTO;
use App\Domains\Booking\SlotCalculator;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SlotController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'date'             => ['required', 'date_format:Y-m-d'],
            'guests_count'     => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:480'],
        ]);

        $slots = app(SlotCalculator::class)->calculate(
            SlotQueryDTO::from([
                'restaurantId'    => auth()->user()->restaurant_id,
                'date'            => $request->input('date'),
                'guestsCount'     => $request->integer('guests_count'),
                'durationMinutes' => $request->integer('duration_minutes', 120),
            ])
        );

        return response()->json([
            'data' => $slots->map(fn ($slot) => $slot->toDateTimeString()),
            'meta' => ['count' => $slots->count()],
        ]);
    }
}
