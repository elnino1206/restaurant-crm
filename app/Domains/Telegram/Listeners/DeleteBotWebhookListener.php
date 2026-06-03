<?php

namespace App\Domains\Telegram\Listeners;

use App\Domains\Restaurant\Events\RestaurantDeletedEvent;
use App\Domains\Telegram\Actions\DeleteBotWebhookAction;

class DeleteBotWebhookListener
{
    public function handle(RestaurantDeletedEvent $event): void
    {
        app(DeleteBotWebhookAction::class)->handle($event->restaurantId);
    }
}
