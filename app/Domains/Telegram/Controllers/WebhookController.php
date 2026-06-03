<?php

namespace App\Domains\Telegram\Controllers;

use App\Domains\Telegram\Models\RestaurantBot;
use App\Domains\Telegram\Services\BotHandlerService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Nutgram\Laravel\RunningMode\LaravelWebhook;
use SergiX44\Nutgram\Nutgram;

class WebhookController
{
    public function __invoke(Request $request, string $restaurantId): Response
    {
        $bot = RestaurantBot::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->firstOrFail();

        $nutgram = new Nutgram($bot->token);
        $nutgram->setRunningMode(new LaravelWebhook);

        app(BotHandlerService::class)->register($nutgram, $restaurantId);

        $nutgram->run();

        return response()->noContent();
    }
}
