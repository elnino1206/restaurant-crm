<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Booking\Actions\CancelBookingAction;
use App\Domains\Booking\Actions\CompleteBookingAction;
use App\Domains\Booking\Actions\ConfirmBookingAction;
use App\Domains\Booking\Actions\CreateBookingAction;
use App\Domains\Booking\Actions\MarkNoShowAction;
use App\Domains\Booking\Actions\UpdateBookingAction;
use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Requests\CancelBookingRequest;
use App\Domains\Booking\Requests\ConfirmBookingRequest;
use App\Domains\Booking\Requests\CreateBookingRequest;
use App\Domains\Booking\Requests\UpdateBookingRequest;
use App\Domains\Booking\Resources\BookingResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Carbon;

class BookingController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Booking::class);

        $query = Booking::with(['customer', 'table'])->orderBy('booking_start');

        if ($request->filled('date')) {
            $timezone = auth()->user()->restaurant?->timezone ?? 'UTC';
            $start = Carbon::createFromFormat('Y-m-d', $request->input('date'), $timezone)->startOfDay()->utc();
            $end = $start->copy()->endOfDay();
            $query->whereBetween('booking_start', [$start, $end]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        return BookingResource::collection($query->cursorPaginate(100));
    }

    public function store(CreateBookingRequest $request): JsonResponse
    {
        $this->authorize('create', Booking::class);

        $booking = app(CreateBookingAction::class)->handle($request->toDTO());

        return BookingResource::make($booking->load(['customer', 'table']))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Booking $booking): JsonResponse
    {
        $this->authorize('view', $booking);

        return BookingResource::make($booking->load(['customer', 'table', 'notes']))
            ->response();
    }

    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorize('update', $booking);

        $booking = app(UpdateBookingAction::class)->handle($booking->id, $request->toDTO());

        return BookingResource::make($booking->load(['customer', 'table']))
            ->response();
    }

    public function confirm(ConfirmBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorize('confirm', $booking);

        $booking = app(ConfirmBookingAction::class)->handle($request->toDTO($booking->id));

        return BookingResource::make($booking)->response();
    }

    public function cancel(CancelBookingRequest $request, Booking $booking): JsonResponse
    {
        $this->authorize('cancel', $booking);

        $booking = app(CancelBookingAction::class)->handle($request->toDTO($booking->id));

        return BookingResource::make($booking)->response();
    }

    public function complete(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('complete', $booking);

        $booking = app(CompleteBookingAction::class)->handle($booking->id);

        return BookingResource::make($booking)->response();
    }

    public function markNoShow(Request $request, Booking $booking): JsonResponse
    {
        $this->authorize('markNoShow', $booking);

        $booking = app(MarkNoShowAction::class)->handle($booking->id);

        return BookingResource::make($booking)->response();
    }
}
