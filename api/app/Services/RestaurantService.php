<?php

namespace App\Services;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class RestaurantService
{
    public function listRestaurant(): Collection|LengthAwarePaginator
    {
        return (new Restaurant)
            ->with(['owner', 'type'])
            ->paginate();
    }

    public function getRestaurant(int $id): Restaurant
    {
        return (new Restaurant)
            ->with(['owner', 'type', 'dishes'])
            ->where('id', $id)
            ->firstOrFail();
    }

    public function createRestaurant(User $user, array $data = []): Restaurant
    {
        return $user
            ->restaurants()
            ->create($data);
    }

    public function updateRestaurant(int $id, array $data = []): Restaurant
    {
        $restaurant = (new Restaurant)
            ->where('id', $id)
            ->firstOrFail();

        $restaurant->update($data);

        return $restaurant->fresh();
    }

    public function deleteRestaurant(int $id): bool
    {
        return (new Restaurant)
            ->where('id', $id)
            ->delete();
    }
}
