<?php

namespace App\Domains\Telegram\Commands;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Telegram\Actions\DeleteBotWebhookAction;
use Illuminate\Console\Command;

class DeleteWebhookCommand extends Command
{
    protected $signature = 'telegram:webhook:delete
                            {restaurant_id? : UUID ресторана (если не указан — удаляет у всех)}';

    protected $description = 'Удалить Telegram webhook для ресторана (или всех ресторанов)';

    public function handle(DeleteBotWebhookAction $action): int
    {
        $restaurantId = $this->argument('restaurant_id');

        if ($restaurantId) {
            return $this->deleteOne($action, $restaurantId);
        }

        if (! $this->confirm('Удалить webhook у ВСЕХ ресторанов?')) {
            $this->line('Отменено.');

            return self::SUCCESS;
        }

        return $this->deleteAll($action);
    }

    private function deleteOne(DeleteBotWebhookAction $action, string $restaurantId): int
    {
        $restaurant = Restaurant::withoutGlobalScopes()->find($restaurantId);

        if ($restaurant === null) {
            $this->error("Ресторан [{$restaurantId}] не найден.");

            return self::FAILURE;
        }

        $this->info("Удаление webhook для: {$restaurant->name}");

        $action->handle($restaurantId);

        $this->info('✓ Webhook удалён.');

        return self::SUCCESS;
    }

    private function deleteAll(DeleteBotWebhookAction $action): int
    {
        $restaurants = Restaurant::withoutGlobalScopes()
            ->whereHas('bot')
            ->get();

        if ($restaurants->isEmpty()) {
            $this->warn('Не найдено ресторанов с ботом.');

            return self::SUCCESS;
        }

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
