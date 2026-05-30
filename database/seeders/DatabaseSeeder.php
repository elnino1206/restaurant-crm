<?php

namespace Database\Seeders;

use App\Domains\Restaurant\Models\Floor;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Models\Table;
use App\Domains\User\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@restaurant-crm.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => UserRole::SuperAdmin,
            ]
        );

        $restaurant = Restaurant::updateOrCreate(
            ['slug' => 'test-restaurant'],
            [
                'name' => 'Test Restaurant',
                'timezone' => 'Europe/Moscow',
                'phone' => '+74951234567',
                'address' => 'Москва, ул. Тестовая, 1',
                'is_active' => true,
            ]
        );

        User::updateOrCreate(
            ['email' => 'owner@test-restaurant.com'],
            [
                'name' => 'Owner',
                'password' => Hash::make('password'),
                'role' => UserRole::Owner,
                'restaurant_id' => $restaurant->id,
            ]
        );

        $floor = Floor::updateOrCreate(
            ['restaurant_id' => $restaurant->id, 'name' => 'Основной зал'],
            ['sort_order' => 1]
        );

        foreach (range(1, 5) as $n) {
            Table::updateOrCreate(
                ['restaurant_id' => $restaurant->id, 'number' => (string) $n],
                [
                    'floor_id' => $floor->id,
                    'capacity' => $n * 2,
                    'min_capacity' => 1,
                    'is_active' => true,
                ]
            );
        }
    }
}
