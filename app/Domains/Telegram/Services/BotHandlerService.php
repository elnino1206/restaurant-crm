<?php

namespace App\Domains\Telegram\Services;

use App\Domains\Telegram\Actions\ResetConversationAction;
use App\Domains\Telegram\Actions\ResolveOrCreateConversationAction;
use App\Domains\Telegram\Conversations\Booking\AwaitingConfirmConversation;
use App\Domains\Telegram\Conversations\Cancellation\AwaitingCancelConfirmConversation;
use App\Domains\Telegram\Conversations\Cancellation\AwaitingCancelConversation;
use App\Domains\Telegram\Enums\TelegramChatState;
use SergiX44\Nutgram\Nutgram;

class BotHandlerService
{
    public function __construct(
        private readonly ConversationResolver $resolver,
        private readonly ResolveOrCreateConversationAction $resolveConversation,
        private readonly ResetConversationAction $resetConversation,
    ) {}

    public function register(Nutgram $bot, string $restaurantId): void
    {
        $this->registerCommands($bot, $restaurantId);
        $this->registerCallbackHandlers($bot, $restaurantId);
        $this->registerMessageHandler($bot, $restaurantId);
    }

    // ─── Глобальные команды ───────────────────────────────────────────────────

    private function registerCommands(Nutgram $bot, string $restaurantId): void
    {
        $bot->onCommand('start', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());
            $this->resetConversation->handle($conversation);

            $bot->sendMessage(
                "👋 Добро пожаловать!\n\n".
                "Я помогу вам забронировать столик.\n\n".
                "/book — забронировать стол\n".
                "/mycancel — отменить бронирование\n".
                '/help — помощь'
            );
        });

        $bot->onCommand('help', function (Nutgram $bot): void {
            $bot->sendMessage(
                "📋 Доступные команды:\n\n".
                "/book — забронировать стол\n".
                "/mycancel — отменить бронирование\n".
                "/cancel — прервать текущее действие\n".
                "/start — начать заново\n".
                '/help — эта справка'
            );
        });

        // /cancel — прерывает текущий диалог (работает в любом состоянии)
        $bot->onCommand('cancel', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());

            if ($conversation->isIdle()) {
                $bot->sendMessage('Нет активного действия для отмены.');

                return;
            }

            $this->resetConversation->handle($conversation);
            $bot->sendMessage('❌ Действие отменено. Напишите /start чтобы начать заново.');
        });

        // /book — старт FSM бронирования
        $bot->onCommand('book', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());

            if (! $conversation->isIdle()) {
                $bot->sendMessage(
                    "У вас уже есть активный диалог ({$conversation->state->label()}).\n".
                    'Напишите /cancel чтобы отменить его.'
                );

                return;
            }

            $conversation->update(['state' => TelegramChatState::AwaitingDate]);

            $bot->sendMessage(
                "📅 Введите дату бронирования в формате ДД.ММ.ГГГГ\n".
                'Например: 25.07.2026'
            );
        });

        // /mycancel — старт FSM отмены бронирования
        $bot->onCommand('mycancel', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());

            if (! $conversation->isIdle()) {
                $bot->sendMessage(
                    "У вас уже есть активный диалог ({$conversation->state->label()}).\n".
                    'Напишите /cancel чтобы отменить его.'
                );

                return;
            }

            $conversation->update(['state' => TelegramChatState::AwaitingCancel]);
            app(AwaitingCancelConversation::class)->handle($bot, $conversation);
        });
    }

    // ─── Callback Query handlers ──────────────────────────────────────────────

    private function registerCallbackHandlers(Nutgram $bot, string $restaurantId): void
    {
        // Подтверждение бронирования
        $bot->onCallbackQueryData('booking_confirm', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());
            app(AwaitingConfirmConversation::class)->confirm($bot, $conversation);
        });

        // Отмена бронирования через кнопку
        $bot->onCallbackQueryData('booking_cancel', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());
            app(AwaitingConfirmConversation::class)->cancelFromCallback($bot, $conversation);
        });

        // Выбор конкретной брони для отмены (паттерн: cancel_select_{uuid})
        $bot->onCallbackQueryData('cancel_select_.*', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());
            $bookingId = str_replace('cancel_select_', '', $bot->callbackQuery()?->data ?? '');
            app(AwaitingCancelConfirmConversation::class)->selectBooking($bot, $conversation, $bookingId);
        });

        // Финальное подтверждение отмены
        $bot->onCallbackQueryData('cancel_confirm', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());
            app(AwaitingCancelConfirmConversation::class)->confirmCancel($bot, $conversation);
        });

        // Возврат к списку броней
        $bot->onCallbackQueryData('cancel_back', function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());
            app(AwaitingCancelConfirmConversation::class)->goBack($bot, $conversation);
        });
    }

    // ─── Роутинг сообщений по FSM-состоянию ──────────────────────────────────

    private function registerMessageHandler(Nutgram $bot, string $restaurantId): void
    {
        $bot->onMessage(function (Nutgram $bot) use ($restaurantId): void {
            $conversation = $this->resolveConversation->handle($restaurantId, $bot->userId());

            if ($conversation->isIdle()) {
                $bot->sendMessage(
                    "Напишите /book чтобы забронировать стол\n".
                    'или /help для справки.'
                );

                return;
            }

            $conversationClass = $this->resolver->resolve($conversation->state);

            if ($conversationClass === null) {
                $this->resetConversation->handle($conversation);
                $bot->sendMessage('Что-то пошло не так. Начнём заново — напишите /start');

                return;
            }

            app($conversationClass)->handle($bot, $conversation);
        });
    }
}
