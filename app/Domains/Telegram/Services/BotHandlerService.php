<?php

namespace App\Domains\Telegram\Services;

use App\Domains\Telegram\Actions\ResetConversationAction;
use App\Domains\Telegram\Actions\ResolveOrCreateConversationAction;
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
        $this->registerMessageHandler($bot, $restaurantId);
    }

    // ─── Глобальные команды (работают в любом состоянии) ─────────────────────

    private function registerCommands(Nutgram $bot, string $restaurantId): void
    {
        $bot->onCommand('start', function (Nutgram $bot) use ($restaurantId): void {
            $telegramUserId = $bot->userId();
            $conversation = $this->resolveConversation->handle($restaurantId, $telegramUserId);
            $this->resetConversation->handle($conversation);

            $bot->sendMessage(
                "👋 Добро пожаловать!\n\n".
                "Я помогу вам забронировать столик.\n\n".
                "/book — забронировать стол\n".
                "/cancel — отменить бронирование\n".
                '/help — помощь'
            );
        });

        $bot->onCommand('help', function (Nutgram $bot): void {
            $bot->sendMessage(
                "📋 Доступные команды:\n\n".
                "/book — забронировать стол\n".
                "/cancel — отменить бронирование\n".
                "/start — начать заново\n".
                '/help — эта справка'
            );
        });

        $bot->onCommand('cancel', function (Nutgram $bot) use ($restaurantId): void {
            $telegramUserId = $bot->userId();
            $conversation = $this->resolveConversation->handle($restaurantId, $telegramUserId);

            if ($conversation->isIdle()) {
                $bot->sendMessage('Нет активного действия для отмены.');

                return;
            }

            $this->resetConversation->handle($conversation);
            $bot->sendMessage('❌ Действие отменено. Напишите /start чтобы начать заново.');
        });

        $bot->onCommand('book', function (Nutgram $bot) use ($restaurantId): void {
            $telegramUserId = $bot->userId();
            $conversation = $this->resolveConversation->handle($restaurantId, $telegramUserId);

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
    }

    // ─── Роутинг входящих сообщений по состоянию FSM ─────────────────────────

    private function registerMessageHandler(Nutgram $bot, string $restaurantId): void
    {
        $bot->onMessage(function (Nutgram $bot) use ($restaurantId): void {
            $telegramUserId = $bot->userId();
            $conversation = $this->resolveConversation->handle($restaurantId, $telegramUserId);

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
