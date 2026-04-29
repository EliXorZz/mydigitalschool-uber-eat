<?php

namespace App\Services;

use App\Exceptions\CannotCancelOrderException;
use App\Exceptions\OrderDishesFromDifferentRestaurantsException;
use App\Models\Order;
use App\Models\User;
use App\States\OrderPending;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class OrderService
{
    public function __construct(
        private DishService $dishSvc,
        private DatabaseManager $dbm,
    ) {}

    public function listOrder(?User $user = null, array $filters = []): LengthAwarePaginator|Collection
    {
        $query = (new Order)
            ->with(['dishes']);

        if ($user) {
            $query->where('user_id', $user->id);
        }

        if (! empty($filters['status'])) {
            $query->where('state', $filters['status']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        if (! empty($filters['min_price'])) {
            $query->where('total', '>=', $filters['minPrice']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('total', '<=', $filters['maxPrice']);
        }

        return $query->paginate();
    }

    public function getOrder(int $id): Order
    {
        return (new Order)
            ->with(['dishes'])
            ->findOrFail($id);
    }

    public function createOrder(User $user, array $data = []): Order
    {
        $dishes = Arr::pull($data, 'dishes');

        $restaurantIds = [];
        $total = 0;

        foreach ($dishes as $dishData) {
            $dishId = $dishData['id'];
            $quantity = $dishData['quantity'] ?? 1;

            $dish = $this->dishSvc->getDish($dishId);

            $restaurantIds[] = $dish->restaurant_id;
            $total += $dish->price * $quantity;
        }

        $restaurantIds = array_unique($restaurantIds);
        if (count($restaurantIds) > 1) {
            throw new OrderDishesFromDifferentRestaurantsException;
        }

        $id = $restaurantIds[0] ?? null;
        if ($id === null) {
            throw new \Exception('Restaurant not found');
        }

        $order = $this->dbm->transaction(function () use ($user, $total, $dishes, $id) {
            $order = $user
                ->orders()
                ->create(['restaurant_id' => $id, 'total' => $total]);

            foreach ($dishes as $dishData) {
                $dishId = $dishData['id'];
                $quantity = $dishData['quantity'] ?? 1;

                $order->dishes()
                    ->attach($dishId, ['quantity' => $quantity]);
            }

            return $order;
        });

        return $order->fresh(['dishes']);
    }

    public function cancelOrder(Order $order): bool
    {
        if ($order->state === OrderPending::class) {
            throw new CannotCancelOrderException;
        }

        return $order->delete();
    }

    public function updateState(Order $order, string $state): Order
    {
        $order->state->transitionTo($state);

        return $order->fresh(['dishes']);
    }
}
