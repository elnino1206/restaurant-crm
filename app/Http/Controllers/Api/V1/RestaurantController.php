<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Floor;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Models\TimeSlotConfig;
use App\Domains\Restaurant\Requests\UpdateRestaurantRequest;
use App\Domains\Restaurant\Resources\FloorResource;
use App\Domains\Restaurant\Resources\RestaurantResource;
use App\Domains\Restaurant\Resources\TimeSlotConfigResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantController extends Controller
{
    public function show(): JsonResponse
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('view', $restaurant);

        return RestaurantResource::make($restaurant)->response();
    }

    public function update(UpdateRestaurantRequest $request): JsonResponse
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('update', $restaurant);

        $dto = $request->toDTO();

        $restaurant->update(array_filter([
            'name'      => $dto->name,
            'timezone'  => $dto->timezone,
            'phone'     => $dto->phone,
            'address'   => $dto->address,
            'settings'  => $dto->settings,
            'is_active' => $dto->isActive,
        ], fn ($v) => $v !== null));

        return RestaurantResource::make($restaurant->fresh())->response();
    }

    public function floors(): AnonymousResourceCollection
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('view', $restaurant);

        $floors = Floor::with('tables')->get();

        return FloorResource::collection($floors);
    }

    public function timeSlotConfigs(): AnonymousResourceCollection
    {
        $restaurant = Restaurant::findOrFail(auth()->user()->restaurant_id);

        $this->authorize('view', $restaurant);

        $configs = TimeSlotConfig::orderBy('day_of_week')->get();

        return TimeSlotConfigResource::collection($configs);
    }
}
