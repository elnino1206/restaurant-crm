<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class DashboardController extends Controller
{
    private string $base;

    public function __construct()
    {
        $this->base = env('API_INTERNAL_URL', 'http://nginx').'/api/v1';
    }

    private function api(): PendingRequest
    {
        static $token = null;

        if (! $token) {
            $token = Http::post("{$this->base}/auth/login", [
                'email' => 'owner@test-restaurant.com',
                'password' => 'password',
            ])->json('data.token');
        }

        return Http::withToken($token)->acceptJson();
    }

    public function index(Request $request)
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $api = $this->api();

        $restaurant = $api->get("{$this->base}/restaurant")->json('data', []);
        $bookings = $api->get("{$this->base}/bookings", ['date' => $date])->json('data', []);
        $floors = $api->get("{$this->base}/restaurant/floors")->json('data', []);

        return view('dashboard', compact('restaurant', 'bookings', 'floors', 'date'));
    }

    public function fetchBookings(Request $request): JsonResponse
    {
        $date = $request->input('date', now()->format('Y-m-d'));
        $bookings = $this->api()->get("{$this->base}/bookings", ['date' => $date])->json('data', []);

        return response()->json(['bookings' => $bookings]);
    }

    public function action(Request $request, string $bookingId, string $action): JsonResponse
    {
        $api = $this->api();

        $response = match ($action) {
            'confirm' => $api->post("{$this->base}/bookings/{$bookingId}/confirm"),
            'cancel' => $api->post("{$this->base}/bookings/{$bookingId}/cancel", [
                'reason' => $request->input('reason'),
            ]),
            'complete' => $api->post("{$this->base}/bookings/{$bookingId}/complete"),
            'no-show' => $api->post("{$this->base}/bookings/{$bookingId}/no-show"),
            default => null,
        };

        if (! $response) {
            return response()->json(['message' => 'Неизвестное действие.'], 400);
        }

        return response()->json($response->json());
    }

    public function update(Request $request, string $bookingId): JsonResponse
    {
        $response = $this->api()->patch("{$this->base}/bookings/{$bookingId}", $request->all());

        return response()->json($response->json(), $response->status());
    }
}
