<?php

namespace App\Domains\Booking\Jobs;

use App\Domains\Booking\Models\Booking;
use App\Domains\Telegram\Jobs\SendTelegramMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendBookingConfirmationJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public int $backoff = 10;

    public function __construct(
        public readonly string $bookingId,
    ) {}

    public function handle(): void
    {
        $booking = Booking::withoutGlobalScopes()
            ->with(['customer', 'table'])
            ->find($this->bookingId);

        if ($booking === null) {
            Log::warning('SendBookingConfirmationJob: booking not found', ['booking_id' => $this->bookingId]);

            return;
        }

        $customer = $booking->customer;

        if ($customer === null || $customer->telegram_id === null) {
            return;
        }

        $restaurantBot = $booking->restaurant?->bot;

        if ($restaurantBot === null) {
            return;
        }

        $start = $booking->booking_start
            ->timezone($booking->restaurant?->timezone ?? 'UTC');

        $text =
            "вњ… Р‘СЂРѕРЅРёСЂРѕРІР°РЅРёРµ РїРѕРґС‚РІРµСЂР¶РґРµРЅРѕ!\n\n".
            "рџ“… {$start->format('d.m.Y')} РІ {$start->format('H:i')}\n".
            "рџ‘Ґ Р“РѕСЃС‚РµР№: {$booking->guests_count}\n".
            ($booking->table ? "рџЄ‘ РЎС‚РѕР»: {$booking->table->number}\n" : '').
            "\nР”Рѕ РІСЃС‚СЂРµС‡Рё! рџЋ‰";

        SendTelegramMessageJob::dispatch(
            $restaurantBot->token,
            $customer->telegram_id,
            $text,
        )->onQueue('high');
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendBookingConfirmationJob failed', [
            'booking_id' => $this->bookingId,
            'error' => $e->getMessage(),
        ]);
    }
}
