<?php

namespace App\Domains\Telegram\Conversations\Booking;

use App\Domains\Telegram\Enums\TelegramChatState;
use App\Domains\Telegram\Models\TelegramConversation;
use App\Domains\Telegram\Services\PayloadManager;
use SergiX44\Nutgram\Nutgram;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardButton;
use SergiX44\Nutgram\Telegram\Types\Keyboard\InlineKeyboardMarkup;

class AwaitingCommentConversation
{
    public function handle(Nutgram $bot, TelegramConversation $conversation): void
    {
        $input = trim($bot->message()?->text ?? '');
        $pm = app(PayloadManager::class);

        // /skip — пропускаем комментарий
        if (strtolower($input) !== '/skip' && $input !== '') {
            if (mb_strlen($input) > 500) {
                $bot->sendMessage('⚠️ Комментарий слишком длинный (максимум 500 символов).');

                return;
            }
            $pm->set($conversation, 'comment', $input);
        }

        $pm->resetAttempts($conversation);
        $conversation->update(['state' => TelegramChatState::AwaitingConfirm]);

        $this->sendSummary($bot, $conversation);
    }

    private function sendSummary(Nutgram $bot, TelegramConversation $conversation): void
    {
        $data = app(PayloadManager::class)->getBookingData($conversation);

        $comment = isset($data['comment']) ? "\n💬 Комментарий: {$data['comment']}" : '';

        $text =
            "📋 Проверьте данные бронирования:\n\n".
            "📅 Дата: {$data['date']}\n".
            "🕐 Время: {$data['time']}\n".
            "👥 Гостей: {$data['guests']}\n".
            "👤 Имя: {$data['name']}\n".
            "📱 Телефон: {$data['phone']}".
            $comment.
            "\n\nВсё верно?";

        $keyboard = InlineKeyboardMarkup::make()
            ->addRow(
                InlineKeyboardButton::make('✅ Подтвердить', callback_data: 'booking_confirm'),
                InlineKeyboardButton::make('❌ Отменить', callback_data: 'booking_cancel'),
            );

        $bot->sendMessage($text, reply_markup: $keyboard);
    }
}
