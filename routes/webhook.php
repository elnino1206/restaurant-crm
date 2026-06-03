<?php

use App\Domains\Telegram\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

/*
 * Telegram Webhook endpoint.
 * Один URL на каждый ресторан — мультибот архитектура.
 *
 * Telegram отправляет POST-запрос при каждом входящем сообщении.
 * CSRF отключён через withoutMiddleware в bootstrap/app.php.
 */
Route::post('/webhook/telegram/{restaurant_id}', WebhookController::class)
    ->name('webhook.telegram');
