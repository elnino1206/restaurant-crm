<?php

namespace App\Providers;

use App\Domains\Booking\Models\Booking;
use App\Domains\Booking\Models\Customer;
use App\Domains\Booking\Policies\BookingPolicy;
use App\Domains\Booking\Policies\CustomerPolicy;
use App\Domains\Restaurant\Events\RestaurantCreatedEvent;
use App\Domains\Restaurant\Events\RestaurantDeletedEvent;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Policies\RestaurantPolicy;
use App\Domains\Telegram\Listeners\DeleteBotWebhookListener;
use App\Domains\Telegram\Listeners\RegisterBotWebhookListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Policies
        Gate::policy(Booking::class, BookingPolicy::class);
        Gate::policy(Customer::class, CustomerPolicy::class);
        Gate::policy(Restaurant::class, RestaurantPolicy::class);

        // Telegram webhook auto-registration
        Event::listen(RestaurantCreatedEvent::class, RegisterBotWebhookListener::class);
        Event::listen(RestaurantDeletedEvent::class, DeleteBotWebhookListener::class);
    }
}
