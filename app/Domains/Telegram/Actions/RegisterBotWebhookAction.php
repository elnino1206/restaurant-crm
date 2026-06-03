<?php

namespace App\Domains\Telegram\Actions;

use App\Domains\Telegram\Models\RestaurantBot;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class RegisterBotWebhookAction
{
    public function handle(string $restaurantId): void
    {
        $bot = RestaurantBot::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->first();

        if ($bot === null) {
            Log::warning('RegisterBotWebhook: no active bot found', ['restaurant_id' => $restaurantId]);

            return;
        }

        $url = route('webhook.telegram', ['restaurant_id' => $restaurantId]);

        $nutgram = new Nutgram($bot->token);
        $nutgram->setWebhook($url);

        $bot->update(['webhook_url' => $url]);

        Log::info('Telegram webhook registered', [
            'restaurant_id' => $restaurantId,
            'url' => $url,
        ]);
    }
}
