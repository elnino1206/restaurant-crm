<?php

namespace App\Domains\Booking\Listeners;

use App\Domains\Booking\Events\BookingCreatedEvent;
use App\Domains\Booking\Jobs\SendBookingConfirmationJob;

class SendBookingConfirmationListener
{
    public function handle(BookingCreatedEvent $event): void
    {
        SendBookingConfirmationJob::dispatch($event->bookingId)
            ->onQueue('high');
    }
}
