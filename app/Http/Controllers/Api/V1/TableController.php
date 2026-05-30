<?php

namespace App\Http\Controllers\Api\V1;

use App\Domains\Restaurant\Models\Floor;
use App\Domains\Restaurant\Models\Restaurant;
use App\Domains\Restaurant\Models\Table;
use App\Domains\Restaurant\Requests\CreateTableRequest;
use App\Domains\Restaurant\Requests\UpdateTableRequest;
use App\Domains\Restaurant\Resources\TableResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class TableController extends Controller
{
    public function store(CreateTableRequest $request, Restaurant $restaurant, Floor $floor): JsonResponse
    {
        $this->authorize('manageTables', $restaurant);

        $table = $floor->tables()->create(array_merge(
            $request->validated(),
            ['restaurant_id' => $restaurant->id]
        ));

        return TableResource::make($table)->response()->setStatusCode(201);
    }

    public function update(UpdateTableRequest $request, Restaurant $restaurant, Table $table): JsonResponse
    {
        $this->authorize('manageTables', $restaurant);

        $table->update($request->validated());

        return TableResource::make($table->fresh())->response();
    }

    public function destroy(Restaurant $restaurant, Table $table): JsonResponse
    {
        $this->authorize('manageTables', $restaurant);

        $table->delete();

        return response()->json(['message' => 'Deleted.']);
    }
}
