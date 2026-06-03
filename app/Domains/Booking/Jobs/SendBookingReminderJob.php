<?php

namespace App\Domains\Booking\Jobs;

use App\Domains\Booking\Models\Booking;
use App\Domains\Telegram\Jobs\SendTelegramMessageJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendBookingReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 30;

    public function __construct(
        public readonly string $bookingId,
    ) {}

    public function handle(): void
    {
        $booking = Booking::withoutGlobalScopes()
            ->with(['customer', 'table', 'restaurant.bot'])
            ->find($this->bookingId);

        if ($booking === null) {
            return;
        }

        // РќРµ РѕС‚РїСЂР°РІР»СЏРµРј РЅР°РїРѕРјРёРЅР°РЅРёРµ РґР»СЏ РѕС‚РјРµРЅС‘РЅРЅС‹С…/Р·Р°РІРµСЂС€С‘РЅРЅС‹С… Р±СЂРѕРЅРµР№
        if (in_array($booking->status->getMorphClass(), ['cancelled', 'completed', 'no_show'])) {
            return;
        }

        $customer = $booking->customer;

        if ($customer?->telegram_id === null) {
            return;
        }

        $restaurantBot = $booking->restaurant?->bot;

        if ($restaurantBot === null) {
            return;
        }

        $start = $booking->booking_start
            ->timezone($booking->restaurant?->timezone ?? 'UTC');

        $text =
            "рџ”” РќР°РїРѕРјРёРЅР°РЅРёРµ Рѕ Р±СЂРѕРЅРёСЂРѕРІР°РЅРёРё!\n\n".
            "рџ“… РЎРµРіРѕРґРЅСЏ РІ {$start->format('H:i')}\n".
            "рџ‘Ґ Р“РѕСЃС‚РµР№: {$booking->guests_count}\n".
            ($booking->table ? "рџЄ‘ РЎС‚РѕР»: {$booking->table->number}\n" : '').
            "\nР–РґС‘Рј РІР°СЃ!";

        SendTelegramMessageJob::dispatch(
            $restaurantBot->token,
            $customer->telegram_id,
            $text,
        )->onQueue('high');
    }

    public function failed(\Throwable $e): void
    {
        Log::error('SendBookingReminderJob failed', [
            'booking_id' => $this->bookingId,
            'error' => $e->getMessage(),
        ]);
    }
}
