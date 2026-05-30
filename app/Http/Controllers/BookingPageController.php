<?php

namespace App\Http\Controllers;

use App\Domains\Booking\Actions\CreateBookingAction;
use App\Domains\Booking\Actions\FindOrCreateCustomerAction;
use App\Domains\Booking\DTO\CreateBookingDTO;
use App\Domains\Booking\DTO\CreateCustomerDTO;
use App\Domains\Booking\DTO\SlotQueryDTO;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Booking\Exceptions\BookingConflictException;
use App\Domains\Booking\Exceptions\NoTablesAvailableException;
use App\Domains\Booking\SlotCalculator;
use App\Domains\Restaurant\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BookingPageController extends Controller
{
    public function show(string $slug)
    {
        $restaurant = Restaurant::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('booking.index', compact('restaurant'));
    }

    public function slots(Request $request, string $slug): JsonResponse
    {
        $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['nullable', 'integer', 'min:30', 'max:480'],
        ]);

        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        $slots = app(SlotCalculator::class)->calculate(
            SlotQueryDTO::from([
                'restaurantId' => $restaurant->id,
                'date' => Carbon::parse($request->input('date')),
                'guestsCount' => $request->integer('guests_count'),
                'durationMinutes' => $request->integer('duration_minutes', 120),
            ])
        );

        $now = Carbon::now($restaurant->timezone);

        $result = $slots
            ->filter(fn ($slot) => $slot->isAfter($now))
            ->values()
            ->map(fn ($slot) => $slot->setTimezone($restaurant->timezone)->format('H:i'));

        return response()->json(['slots' => $result]);
    }

    public function book(Request $request, string $slug): JsonResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'string', 'max:20'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'guests_count' => ['required', 'integer', 'min:1', 'max:100'],
            'comment' => ['nullable', 'string', 'max:500'],
        ]);

        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();

        $customer = app(FindOrCreateCustomerAction::class)->handle(
            CreateCustomerDTO::from([
                'restaurantId' => $restaurant->id,
                'name' => $request->input('name'),
                'phone' => $request->input('phone'),
            ])
        );

        $bookingStart = Carbon::createFromFormat(
            'Y-m-d H:i',
            $request->input('date').' '.$request->input('time'),
            $restaurant->timezone
        );
        $bookingEnd = $bookingStart->copy()->addMinutes(120);

        try {
            $booking = app(CreateBookingAction::class)->handle(
                CreateBookingDTO::from([
                    'restaurantId' => $restaurant->id,
                    'guestsCount' => $request->integer('guests_count'),
                    'bookingStart' => $bookingStart,
                    'bookingEnd' => $bookingEnd,
                    'customerId' => $customer->id,
                    'comment' => $request->input('comment'),
                    'source' => BookingSource::Web,
                ])
            );
        } catch (NoTablesAvailableException) {
            return response()->json(['message' => 'На выбранное время нет свободных столов.'], 422);
        } catch (BookingConflictException) {
            return response()->json(['message' => 'Стол уже занят. Выберите другое время.'], 422);
        }

        return response()->json([
            'id' => $booking->id,
            'guests_count' => $booking->guests_count,
            'booking_start' => $bookingStart->setTimezone($restaurant->timezone)->format('d.m.Y H:i'),
            'restaurant_name' => $restaurant->name,
        ]);
    }
}
