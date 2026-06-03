<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private string $internalBase;

    public function __construct()
    {
        // Для PHP→Nginx внутри Docker используем имя сервиса
        $this->internalBase = env('API_INTERNAL_URL', 'http://nginx').'/api/v1';
    }

    public function index(Request $request)
    {
        $token = Http::post("{$this->internalBase}/auth/login", [
            'login' => 'owner',
            'password' => 'password',
        ])->json('data.token');

        $api = Http::withToken($token)->acceptJson();

        $restaurant = $api->get("{$this->internalBase}/restaurant")->json('data', []);
        $floors = $api->get("{$this->internalBase}/restaurant/floors")->json('data', []);

        return view('dashboard', [
            'restaurant' => $restaurant,
            'floors' => $floors,
            'token' => $token,
            'apiBase' => '/api/v1', // публичный URL для JS (через nginx → браузер)
        ]);
    }
}
