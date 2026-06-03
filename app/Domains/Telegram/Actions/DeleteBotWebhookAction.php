<?php

namespace App\Domains\Telegram\Actions;

use App\Domains\Telegram\Models\RestaurantBot;
use Illuminate\Support\Facades\Log;
use SergiX44\Nutgram\Nutgram;

class DeleteBotWebhookAction
{
    public function handle(string $restaurantId): void
    {
        $bot = RestaurantBot::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->first();

        if ($bot === null) {
            return;
        }

        $nutgram = new Nutgram($bot->token);
        $nutgram->deleteWebhook();

        $bot->update(['webhook_url' => null]);

        Log::info('Telegram webhook deleted', ['restaurant_id' => $restaurantId]);
    }
}
