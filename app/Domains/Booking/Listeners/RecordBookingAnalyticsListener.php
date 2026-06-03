<?php

namespace App\Domains\Booking\Listeners;

use App\Domains\Analytics\Jobs\RecordAnalyticsEventJob;
use App\Domains\Booking\Events\BookingCancelledEvent;
use App\Domains\Booking\Events\BookingConfirmedEvent;
use App\Domains\Booking\Events\BookingCreatedEvent;

class RecordBookingAnalyticsListener
{
    public function handleCreated(BookingCreatedEvent $event): void
    {
        RecordAnalyticsEventJob::dispatch(
            restaurantId: $event->restaurantId,
            eventType: 'booking.created',
            payload: [],
            bookingId: $event->bookingId,
        )->onQueue('low');
    }

    public function handleConfirmed(BookingConfirmedEvent $event): void
    {
        RecordAnalyticsEventJob::dispatch(
            restaurantId: $event->restaurantId,
            eventType: 'booking.confirmed',
            payload: [],
            bookingId: $event->bookingId,
        )->onQueue('low');
    }

    public function handleCancelled(BookingCancelledEvent $event): void
    {
        RecordAnalyticsEventJob::dispatch(
            restaurantId: $event->restaurantId,
            eventType: 'booking.cancelled',
            payload: [],
            bookingId: $event->bookingId,
        )->onQueue('low');
    }
}
