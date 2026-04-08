<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DishService
{
    public function listDish(?Restaurant $restaurant = null): Collection|LengthAwarePaginator
    {
        $query = (new Dish);

        if ($restaurant) {
            return $query->where('restaurant_id', $restaurant->id)
                ->paginate();
        }

        return $query->paginate();
    }

    public function getDish(int $id): Dish
    {
        return (new Dish)
            ->where('id', $id)
            ->firstOrFail();
    }

    public function createDish(Restaurant $restaurant, array $data = []): Dish
    {
        return $restaurant
            ->dishes()
            ->create($data);
    }

    public function updateDish(int $id, array $data = []): Dish
    {
        $dish = (new Dish)
            ->where('id', $id)
            ->firstOrFail();

        $dish->update($data);

        return $dish->fresh();
    }

    public function deleteDish(int $id): bool
    {
        return (new Dish)
            ->where('id', $id)
            ->delete();
    }
}
