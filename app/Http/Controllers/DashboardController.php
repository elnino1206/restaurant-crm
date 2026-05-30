<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private string $apiBase;

    public function __construct()
    {
        // Внутри Docker PHP-контейнер обращается к Nginx по имени сервиса
        $internalHost = env('API_INTERNAL_URL', 'http://nginx');
        $this->apiBase = $internalHost.'/api/v1';
    }

    public function index()
    {
        $token = $this->getToken();

        if (! $token) {
            return view('index', ['error' => 'Не удалось получить токен API']);
        }

        $api = Http::withToken($token)->acceptJson();

        $restaurant = $api->get("{$this->apiBase}/restaurant")->json('data', []);
        $floors = $api->get("{$this->apiBase}/restaurant/floors")->json('data', []);
        $timeSlotConfig = $api->get("{$this->apiBase}/restaurant/time-slot-configs")->json('data', []);
        $bookings = $api->get("{$this->apiBase}/bookings")->json('data', []);
        $customers = $api->get("{$this->apiBase}/customers")->json('data', []);

        return view('index', compact(
            'restaurant',
            'floors',
            'timeSlotConfig',
            'bookings',
            'customers',
        ));
    }

    private function getToken(): ?string
    {
        $response = Http::post("{$this->apiBase}/auth/login", [
            'email' => 'owner@test-restaurant.com',
            'password' => 'password',
        ]);

        return $response->json('data.token');
    }
}
