<?php

namespace App\Domains\Telegram\Listeners;

use App\Domains\Restaurant\Events\RestaurantCreatedEvent;
use App\Domains\Telegram\Actions\RegisterBotWebhookAction;

class RegisterBotWebhookListener
{
    public function handle(RestaurantCreatedEvent $event): void
    {
        app(RegisterBotWebhookAction::class)->handle($event->restaurantId);
    }
}
