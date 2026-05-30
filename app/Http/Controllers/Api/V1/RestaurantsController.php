<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Requests\CreateRestaurantRequest;
use App\Domains\Restaurant\Requests\UpdateRestaurantRequest;
use App\Domains\Restaurant\Resources\FloorResource;
use App\Domains\Restaurant\Resources\RestaurantResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantsController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Restaurant::class);

        return RestaurantResource::collection(Restaurant::orderBy('name')->get());
    }

    public function store(CreateRestaurantRequest $request): JsonResponse
    {
        $this->authorize('create', Restaurant::class);

        $restaurant = Restaurant::create($request->validated());

        return RestaurantResource::make($restaurant)->response()->setStatusCode(201);
    }

    public function show(Restaurant $restaurant): JsonResponse
    {
        $this->authorize('view', $restaurant);

        return RestaurantResource::make($restaurant)->response();
    }

    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): JsonResponse
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

    public function floors(Restaurant $restaurant): AnonymousResourceCollection
    {
        $this->authorize('view', $restaurant);

        return FloorResource::collection(
            $restaurant->floors()->with('tables')->orderBy('sort_order')->get()
        );
    }
}
