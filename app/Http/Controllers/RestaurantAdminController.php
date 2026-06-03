<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;

class RestaurantAdminController extends Controller
{
    private string $base;

    public function __construct()
    {
        $this->base = env('API_INTERNAL_URL', 'http://nginx').'/api/v1';
    }

    // ── Auth ──────────────────────────────────────────────────────────────────

    private function api(): PendingRequest
    {
        static $token = null;

        if (! $token) {
            $token = Http::post("{$this->base}/auth/login", [
                'login' => 'admin',
                'password' => 'password',
            ])->json('data.token');
        }

        return Http::withToken($token)->acceptJson();
    }

    // ── Restaurants ───────────────────────────────────────────────────────────

    public function index()
    {
        $restaurants = $this->api()->get("{$this->base}/restaurants")->json('data', []);

        return view('admin.restaurants.index', compact('restaurants'));
    }

    public function create()
    {
        return view('admin.restaurants.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $response = $this->api()->post("{$this->base}/restaurants", $request->except('_token'));

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        $id = $response->json('data.id');

        return Redirect::route('admin.restaurants.edit', $id)
            ->with('success', 'Ресторан создан.');
    }

    public function edit(string $id)
    {
        $api = $this->api();
        $restaurant = $api->get("{$this->base}/restaurants/{$id}")->json('data', []);
        $floors = $api->get("{$this->base}/restaurants/{$id}/floors")->json('data', []);
        $schedule = $api->get("{$this->base}/restaurants/{$id}/time-slot-configs")->json('data', []);
        $users = $api->get("{$this->base}/restaurants/{$id}/users")->json('data', []);

        return view('admin.restaurants.edit', compact('restaurant', 'floors', 'schedule', 'users'));
    }

    public function updateSchedule(Request $request, string $id): RedirectResponse
    {
        $configs = [];
        foreach (range(0, 6) as $day) {
            $isDayOff = $request->boolean("days.{$day}.is_day_off");
            $configs[] = [
                'day_of_week' => $day,
                'is_day_off' => $isDayOff,
                'open_time' => $isDayOff ? null : $request->input("days.{$day}.open_time"),
                'close_time' => $isDayOff ? null : $request->input("days.{$day}.close_time"),
                'slot_duration' => (int) $request->input("days.{$day}.slot_duration", 30),
                'booking_duration' => (int) $request->input("days.{$day}.booking_duration", 120),
            ];
        }

        $response = $this->api()->put("{$this->base}/restaurants/{$id}/time-slot-configs", [
            'configs' => $configs,
        ]);

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Расписание обновлено.');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);
        if (isset($data['is_active'])) {
            $data['is_active'] = (bool) $data['is_active'];
        }

        $response = $this->api()->patch("{$this->base}/restaurants/{$id}", $data);

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Ресторан обновлён.');
    }

    // ── Users ─────────────────────────────────────────────────────────────────

    public function storeUser(Request $request, string $restaurantId): RedirectResponse
    {
        $response = $this->api()->post(
            "{$this->base}/restaurants/{$restaurantId}/users",
            $request->except('_token')
        );

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Пользователь создан.');
    }

    public function destroyUser(string $restaurantId, string $userId): RedirectResponse
    {
        $this->api()->delete("{$this->base}/restaurants/{$restaurantId}/users/{$userId}");

        return back()->with('success', 'Пользователь удалён.');
    }

    // ── Floors ────────────────────────────────────────────────────────────────

    public function storeFloor(Request $request, string $restaurantId): RedirectResponse
    {
        $response = $this->api()->post(
            "{$this->base}/restaurants/{$restaurantId}/floors",
            $request->except('_token')
        );

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Зал добавлен.');
    }

    public function updateFloor(Request $request, string $restaurantId, string $floorId): RedirectResponse
    {
        $response = $this->api()->patch(
            "{$this->base}/restaurants/{$restaurantId}/floors/{$floorId}",
            $request->except(['_token', '_method'])
        );

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Зал обновлён.');
    }

    public function destroyFloor(string $restaurantId, string $floorId): RedirectResponse
    {
        $this->api()->delete("{$this->base}/restaurants/{$restaurantId}/floors/{$floorId}");

        return back()->with('success', 'Зал удалён.');
    }

    // ── Tables ────────────────────────────────────────────────────────────────

    public function storeTable(Request $request, string $restaurantId, string $floorId): RedirectResponse
    {
        $data = $request->except('_token');
        $data['is_active'] = $request->has('is_active');

        $response = $this->api()->post(
            "{$this->base}/restaurants/{$restaurantId}/floors/{$floorId}/tables",
            $data
        );

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Стол добавлен.');
    }

    public function updateTable(Request $request, string $restaurantId, string $tableId): RedirectResponse
    {
        $data = $request->except(['_token', '_method']);
        $data['is_active'] = $request->has('is_active');

        $response = $this->api()->patch(
            "{$this->base}/restaurants/{$restaurantId}/tables/{$tableId}",
            $data
        );

        if ($response->failed()) {
            return back()->withErrors($response->json('errors', []))->withInput();
        }

        return back()->with('success', 'Стол обновлён.');
    }

    public function destroyTable(string $restaurantId, string $tableId): RedirectResponse
    {
        $this->api()->delete("{$this->base}/restaurants/{$restaurantId}/tables/{$tableId}");

        return back()->with('success', 'Стол удалён.');
    }
}
