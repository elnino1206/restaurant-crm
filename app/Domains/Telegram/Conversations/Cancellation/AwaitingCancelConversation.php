<?php

namespace App\Domains\Telegram\Conversations\Cancellation;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\Customer;
use App\Domains\Telegram\Actions\ResetConversationAction;
use App\Domains\Telegram\Conversations\Concerns\HandlesFailedAttempts;
use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class AwaitingCancelConversation
{
    use HandlesFailedAttempts;

    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $telegramUserId = $bot->userId();

        // Ищем активные брони клиента по telegram_id
        $customer = Customer::withoutGlobalScopes()
            ->where('restaurant_id', $conversation->restaurant_id)
            ->where('telegram_id', $telegramUserId)
            ->first();

        if ($customer === null) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage('У вас нет активных бронирований.');

            return;
        }

        $bookings = Booking::withoutGlobalScopes()
            ->where('restaurant_id', $conversation->restaurant_id)
            ->where('customer_id', $customer->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('booking_start', '>=', now())
            ->orderBy('booking_start')
            ->limit(5)
            ->get();

        if ($bookings->isEmpty()) {
            app(ResetConversationAction::class)->handle($conversation);
            $bot->sendMessage('У вас нет активных бронирований.');

            return;
        }

        $keyboard = InlineKeyboardMarkup::make();

        foreach ($bookings as $booking) {
            $date = $booking->booking_start->timezone($conversation->restaurant?->timezone ?? 'UTC');
            $label = $date->format('d.m H:i').' — '.$booking->guests_count.' гостей';

            $keyboard->addRow(
                InlineKeyboardButton::make($label, callback_data: 'cancel_select_'.$booking->id)
            );
        }

        app(PayloadManager::class)->resetAttempts($conversation);
        $conversation->update(['state' => TelegramChatState::AwaitingCancelConfirm]);

        $bot->sendMessage('Выберите бронирование для отмены:', reply_markup: $keyboard);
    }
}
