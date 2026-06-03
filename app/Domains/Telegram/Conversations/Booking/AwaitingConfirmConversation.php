<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Booking\Actions\CreateBookingAction;
use App\Domains\Booking\Actions\FindOrCreateCustomerAction;
use App\Domains\Booking\DTO\CreateBookingDTO;
use App\Domains\Booking\DTO\CreateCustomerDTO;
use App\Domains\Booking\Enums\BookingSource;
use App\Domains\Telegram\Actions\ResetConversationAction;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use Illuminate\Support\Carbon;
use SergiX44\Nutgram\Nutgram;

class AwaitingConfirmConversation
{
    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        // Ожидаем callback_query — текстовые сообщения игнорируем
        $bot->sendMessage(
            "Используйте кнопки выше для подтверждения или отмены.\n".
            'Или напишите /cancel чтобы начать заново.'
        );
    }

    /**
     * Вызывается из BotHandlerService при callback_data='booking_confirm'
     */
    public function confirm(Nutgram $bot, TelegramConversation $conversation): void
    {
        $bot->answerCallbackQuery();

        $pm = app(PayloadManager::class);
        $data = $pm->getBookingData($conversation);

        if (! $this->hasRequiredData($data)) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage('❌ Данные потеряны. Начните заново — /start');

            return;
        }

        try {
            $customer = app(FindOrCreateCustomerAction::class)->handle(
                CreateCustomerDTO::from([
                    'restaurantId' => $conversation->restaurant_id,
                    'name' => $data['name'],
                    'phone' => $data['phone'],
                    'telegramId' => $bot->userId(),
                    'telegramUsername' => $bot->user()?->username,
                ])
            );

            $bookingStart = Carbon::parse(
                $data['date'].' '.$data['time'],
                $conversation->restaurant?->timezone ?? 'UTC'
            )->utc();

            $booking = app(CreateBookingAction::class)->handle(
                CreateBookingDTO::from([
                    'restaurantId' => $conversation->restaurant_id,
                    'guestsCount' => (int) $data['guests'],
                    'bookingStart' => $bookingStart,
                    'source' => BookingSource::Telegram,
                    'customerId' => $customer->id,
                    'comment' => $data['comment'] ?? null,
                ])
            );

            app(ResetConversationAction::class)->handle($conversation);

            $bot->sendMessage(
                "✅ Бронирование создано!\n\n".
                "📅 {$data['date']} в {$data['time']}\n".
                "👥 Гостей: {$data['guests']}\n\n".
                "Ждём вас! 🎉\n".
                "Номер брони: #{$booking->id}"
            );
        } catch (\Throwable $e) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage(
                "❌ Не удалось создать бронирование: {$e->getMessage()}\n\n".
                'Попробуйте снова — /book'
            );
        }
    }

    /**
     * Вызывается из BotHandlerService при callback_data='booking_cancel'
     */
    public function cancelFromCallback(Nutgram $bot, TelegramConversation $conversation): void
    {
        $bot->answerCallbackQuery();
        app(ResetConversationAction::class)->handle($conversation);
        $bot->sendMessage('❌ Бронирование отменено. Напишите /book чтобы начать заново.');
    }

    private function hasRequiredData(array $data): bool
    {
        return isset($data['date'], $data['time'], $data['guests'], $data['name'], $data['phone']);
    }
}
