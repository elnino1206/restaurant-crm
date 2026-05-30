<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Floor;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Models\TimeSlotConfig;
use App\Domains\Restaurant\Requests\CreateRestaurantRequest;
use App\Domains\Restaurant\Requests\UpdateRestaurantRequest;
use App\Domains\Restaurant\Resources\FloorResource;
use App\Domains\Restaurant\Resources\RestaurantResource;
use App\Domains\Restaurant\Resources\TimeSlotConfigResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantController extends Controller
{
    /**
     * GET /api/v1/restaurant — текущий ресторан owner/manager
     */
    public function show(): JsonResponse
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('view', $restaurant);

        return RestaurantResource::make($restaurant)->response();
    }

    /**
     * PATCH /api/v1/restaurant — обновление текущего ресторана
     */
    public function update(UpdateRestaurantRequest $request): JsonResponse
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('update', $restaurant);

        $dto = $request->toDTO();

        $restaurant->update(array_filter([
            'name' => $dto->name,
            'timezone' => $dto->timezone,
            'phone' => $dto->phone,
            'address' => $dto->address,
            'settings' => $dto->settings,
            'is_active' => $dto->isActive,
        ], fn ($v) => $v !== null));

        return RestaurantResource::make($restaurant->fresh())->response();
    }

    /**
     * GET /api/v1/restaurant/floors
     */
    public function floors(): AnonymousResourceCollection
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('view', $restaurant);

        return FloorResource::collection(
            Floor::with('tables')->orderBy('sort_order')->get()
        );
    }

    /**
     * GET /api/v1/restaurant/time-slot-configs
     */
    public function timeSlotConfigs(): AnonymousResourceCollection
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('view', $restaurant);

        return TimeSlotConfigResource::collection(
            TimeSlotConfig::orderBy('day_of_week')->get()
        );
    }

    // ─── super_admin: управление всеми ресторанами ───────────────────────────

    /**
     * GET /api/v1/restaurants
     */
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Restaurant::class);

        return RestaurantResource::collection(
            Restaurant::orderBy('name')->cursorPaginate(50)
        );
    }

    /**
     * POST /api/v1/restaurants
     */
    public function store(CreateRestaurantRequest $request): JsonResponse
    {
        $this->authorize('create', Restaurant::class);

        $restaurant = Restaurant::create($request->validated());

        return RestaurantResource::make($restaurant)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/v1/restaurants/{restaurant}
     */
    public function showById(Restaurant $restaurant): JsonResponse
    {
        $this->authorize('view', $restaurant);

        return RestaurantResource::make($restaurant)->response();
    }

    /**
     * PATCH /api/v1/restaurants/{restaurant}
     */
    public function updateById(UpdateRestaurantRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('update', $restaurant);

        $dto = $request->toDTO();

        $restaurant->update(array_filter([
            'name' => $dto->name,
            'timezone' => $dto->timezone,
            'phone' => $dto->phone,
            'address' => $dto->address,
            'settings' => $dto->settings,
            'is_active' => $dto->isActive,
        ], fn ($v) => $v !== null));

        return RestaurantResource::make($restaurant->fresh())->response();
    }

    /**
     * GET /api/v1/restaurants/{restaurant}/floors
     */
    public function floorsById(Restaurant $restaurant): AnonymousResourceCollection
    {
        $this->authorize('view', $restaurant);

        return FloorResource::collection(
            $restaurant->floors()->with('tables')->orderBy('sort_order')->get()
        );
    }
}
