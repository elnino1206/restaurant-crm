<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Floor;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Requests\CreateFloorRequest;
use App\Domains\Restaurant\Requests\UpdateFloorRequest;
use App\Domains\Restaurant\Resources\FloorResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class FloorController extends Controller
{
    public function store(CreateFloorRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('manageFloors', $restaurant);

        $floor = $restaurant->floors()->create($request->validated());

        return FloorResource::make($floor->load('tables'))->response()->setStatusCode(201);
    }

    public function update(UpdateFloorRequest $request, Restaurant $restaurant, Floor $floor): JsonResponse
    {
        $this->authorize('manageFloors', $restaurant);

        $floor->update($request->validated());

        return FloorResource::make($floor->fresh()->load('tables'))->response();
    }

    public function destroy(Restaurant $restaurant, Floor $floor): JsonResponse
    {
        $this->authorize('manageFloors', $restaurant);

        $floor->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
