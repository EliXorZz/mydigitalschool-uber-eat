<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOrderRequest;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\OrderService;
use App\Services\RestaurantService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * @group Restaurants
 */
class RestaurantController extends Controller
{
    public function __construct(
        private RestaurantService $restaurantService,
        private OrderService $orderService
    ) {}

    /**
     * List all restaurants.
     *
     * Returns a paginated list of all restaurants.
     */
    public function index(): JsonResponse
    {
        $restaurants = $this->restaurantService->listRestaurant();

        return response()->json($restaurants);
    }

    /**
     * Create a restaurant.
     *
     * Creates a new restaurant owned by the specified user.
     */
    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $this->authorize('create', Restaurant::class);
        abort_if(
            auth()->user()->id === $request->owner()->id,
            403,
            'You cannot perform this action on your own resource.'
        );

        $restaurant = $this->restaurantService->createRestaurant(
            $request->owner(),
            $request->validated()
        );

        return response()->json(['data' => $restaurant], 201);
    }

    /**
     * Get a restaurant.
     *
     * Returns the details of a specific restaurant by ID.
     */
    public function show(Restaurant $restaurant): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($restaurant->id);

        return response()->json(['data' => $restaurant]);
    }

    /**
     * Update a restaurant.
     *
     * Updates the details of an existing restaurant.
     */
    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('update', $restaurant);

        $restaurant = $this->restaurantService->updateRestaurant(
            $restaurant->id,
            $request->validated(),
        );

        return response()->json(['data' => $restaurant]);
    }

    /**
     * Delete a restaurant.
     *
     * Deletes an existing restaurant.
     */
    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $this->authorize('delete', $restaurant);

        $this->restaurantService->deleteRestaurant($restaurant->id);

        return response()->json(null, 204);
    }

    /**
     * List orders by restaurant.
     *
     * Returns all orders for a restaurant.
     */
    public function orders(ListOrderRequest $request, Restaurant $restaurant): LengthAwarePaginator
    {
        $this->authorize('view-any', [Order::class, $restaurant]);

        return $this->orderService->listOrder(
            auth()->user(),
            $request->validated()
        );
    }
}
