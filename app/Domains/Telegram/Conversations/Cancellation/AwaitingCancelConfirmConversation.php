<?php

namespace App\Domains\Telegram\Conversations\Cancellation;

use App\Domains\Booking\Actions\CancelBookingAction;
use App\Domains\Booking\DTO\CancelBookingDTO;
use App\Domains\Booking\Models\Booking;
use App\Domains\Telegram\Actions\ResetConversationAction;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class AwaitingCancelConfirmConversation
{
    /**
     * Обрабатывает текстовые сообщения в состоянии ожидания выбора брони.
     * Реальное подтверждение идёт через callback selectBooking() / confirmCancel().
     */
    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $bot->sendMessage(
            "Выберите бронирование из списка выше.\n".
            'Или напишите /cancel чтобы выйти.'
        );
    }

    /**
     * Вызывается при callback_data='cancel_select_{bookingId}'
     * Показывает детали брони и запрашивает финальное подтверждение.
     */
    public function selectBooking(Nutgram $bot, TelegramConversation $conversation, string $bookingId): void
    {
        $bot->answerCallbackQuery();

        $booking = Booking::withoutGlobalScopes()
            ->where('restaurant_id', $conversation->restaurant_id)
            ->find($bookingId);

        if ($booking === null || ! in_array($booking->status->getMorphClass(), ['pending', 'confirmed'])) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage('Бронирование не найдено или уже недоступно.');

            return;
        }

        app(PayloadManager::class)->set($conversation, 'cancel_booking_id', $bookingId);

        $date = $booking->booking_start->timezone($conversation->restaurant?->timezone ?? 'UTC');

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Да, отменить', callback_data: 'cancel_confirm'),
                InlineKeyboardButton::make('↩️ Назад', callback_data: 'cancel_back'),
            );

        $bot->sendMessage(
            "Отменить бронирование?\n\n".
            "📅 {$date->format('d.m.Y')} в {$date->format('H:i')}\n".
            "👥 Гостей: {$booking->guests_count}",
            reply_markup: $keyboard
        );
    }

    /**
     * Вызывается при callback_data='cancel_confirm'
     */
    public function confirmCancel(Nutgram $bot, TelegramConversation $conversation): void
    {
        $bot->answerCallbackQuery();

        $bookingId = app(PayloadManager::class)->get($conversation, 'cancel_booking_id');

        if ($bookingId === null) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage('❌ Ошибка. Начните заново — /start');

            return;
        }

        try {
            app(CancelBookingAction::class)->handle(
                CancelBookingDTO::from([
                    'bookingId' => $bookingId,
                    'reason' => 'Отменено клиентом через Telegram',
                ])
            );

            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage('✅ Бронирование отменено.');
        } catch (\Throwable $e) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage("❌ Не удалось отменить: {$e->getMessage()}");
        }
    }

    /**
     * Вызывается при callback_data='cancel_back' — возврат к списку броней
     */
    public function goBack(Nutgram $bot, TelegramConversation $conversation): void
    {
        $bot->answerCallbackQuery();
        app(PayloadManager::class)->forget($conversation, 'cancel_booking_id');

        app(AwaitingCancelConversation::class)->handle($bot, $conversation);
    }
}
