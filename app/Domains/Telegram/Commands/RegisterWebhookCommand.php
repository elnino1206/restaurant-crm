<?php

namespace App\Domains\Telegram\Commands;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Telegram\Actions\RegisterBotWebhookAction;
use Illuminate\Console\Command;

class RegisterWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook:register
                            {restaurant_id? : UUID ресторана (если не указан — регистрирует все)}
                            {--force : Перерегистрировать даже если webhook уже установлен}';

    protected $description = 'Зарегистрировать Telegram webhook для ресторана (или всех ресторанов)';

    public function handle(RegisterBotWebhookAction $action): int
    {
        $restaurantId = $this->argument('restaurant_id');

        if ($restaurantId) {
            return $this->registerOne($action, $restaurantId);
        }

        return $this->registerAll($action);
    }

    private function registerOne(RegisterBotWebhookAction $action, string $restaurantId): int
    {
        $restaurant = Restaurant::withoutGlobalScopes()->find($restaurantId);

        if ($restaurant === null) {
            $this->error("Ресторан [{$restaurantId}] не найден.");

            return self::FAILURE;
        }

        $this->info("Регистрация webhook для: {$restaurant->name}");

        $action->handle($restaurantId);

        $this->info('✓ Webhook зарегистрирован.');
        $this->line('  URL: '.route('webhook.telegram', ['restaurant_id' => $restaurantId]));

        return self::SUCCESS;
    }

    private function registerAll(RegisterBotWebhookAction $action): int
    {
        $restaurants = Restaurant::withoutGlobalScopes()
            ->whereHas('bot', fn ($q) => $q->where('is_active', true))
            ->get();

        if ($restaurants->isEmpty()) {
            $this->warn('Не найдено ресторанов с активным ботом.');

            return self::SUCCESS;
        }

        $this->info("Найдено ресторанов: {$restaurants->count()}");

        $bar = $this->output->createProgressBar($restaurants->count());
        $bar->start();

        $success = 0;
        $failed = 0;

        foreach ($restaurants as $restaurant) {
            try {
                $action->handle($restaurant->id);
                $success++;
            } catch (\Throwable $e) {
                $failed++;
                $this->newLine();
                $this->error("  Ошибка [{$restaurant->name}]: {$e->getMessage()}");
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✓ Успешно: {$success} | Ошибок: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
