<?php

namespace App\Infrastructure;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class RestaurantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $restaurantId = CurrentRestaurant::id();

        if ($restaurantId !== null) {
            $builder->where($model->getTable() . '.restaurant_id', $restaurantId);
        }
    }
}
