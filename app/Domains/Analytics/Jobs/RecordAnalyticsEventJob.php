<?php

namespace App\Domains\Analytics\Jobs;

use App\Domains\Analytics\Models\AnalyticsEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RecordAnalyticsEventJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        public readonly string $restaurantId,
        public readonly string $eventType,
        public readonly array $payload = [],
        public readonly ?string $bookingId = null,
        public readonly ?string $customerId = null,
    ) {}

    public function handle(): void
    {
        AnalyticsEvent::create([
            'restaurant_id' => $this->restaurantId,
            'event_type' => $this->eventType,
            'payload' => $this->payload,
            'booking_id' => $this->bookingId,
            'customer_id' => $this->customerId,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error('RecordAnalyticsEventJob failed', [
            'restaurant_id' => $this->restaurantId,
            'event_type' => $this->eventType,
            'error' => $e->getMessage(),
        ]);
    }
}
