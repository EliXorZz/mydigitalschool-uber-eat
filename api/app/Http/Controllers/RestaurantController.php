<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOrderRequest;
use App\Http\Requests\ListRestaurantRequest;
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
     * Returns a paginated list of restaurants, optionally filtered by name.
     *
     * @unauthenticated
     * @queryParam search string Filter by restaurant name. Example: Pizza
     * @response 200 {"current_page": 1, "data": [{"id": 1, "name": "Le Bistrot", "city": "Paris", "score": "4.5", "price_score": 2}], "total": 1}
     */
    public function index(ListRestaurantRequest $request): JsonResponse
    {
        $restaurants = $this->restaurantService->listRestaurant($request->validated('search'));

        return response()->json($restaurants);
    }

    /**
     * Create a restaurant.
     *
     * Creates a new restaurant linked to an existing owner (user with role restaurant). Admin only.
     *
     * @authenticated
     * @response 201 {"data": {"id": 1, "name": "Le Bistrot", "city": "Paris", "owner_id": 2}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
     */
    public function store(StoreRestaurantRequest $request): JsonResponse
    {
        $this->authorize('create', [Restaurant::class, $request->owner()]);

        $restaurant = $this->restaurantService->createRestaurant(
            $request->owner(),
            $request->validated()
        );

        return response()->json(['data' => $restaurant], 201);
    }

    /**
     * Get a restaurant.
     *
     * Returns the full details of a restaurant including owner, type, and dishes.
     *
     * @unauthenticated
     * @response 200 {"data": {"id": 1, "name": "Le Bistrot", "dishes": [], "owner": {}, "type": {}}}
     * @response 404 {"message": "No query results for model [App\\Models\\Restaurant]."}
     */
    public function show(Restaurant $restaurant): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($restaurant->id);

        return response()->json(['data' => $restaurant]);
    }

    /**
     * Update a restaurant.
     *
     * Updates the details of a restaurant. Restricted to the restaurant owner.
     *
     * @authenticated
     * @response 200 {"data": {"id": 1, "name": "Le Bistrot Updated"}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Restaurant]."}
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
     * Soft-deletes a restaurant and cascades to its dishes. Owner or admin only.
     *
     * @authenticated
     * @response 204 {}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Restaurant]."}
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
     * Returns paginated orders for a restaurant. Restricted to the restaurant owner.
     *
     * @authenticated
     * @queryParam status string Filter by order status (pending, confirmed, preparing, ready, delivered). Example: pending
     * @queryParam date_from date Filter orders from this date. Example: 2026-01-01
     * @queryParam date_to date Filter orders up to this date. Example: 2026-12-31
     * @queryParam min_price number Minimum order total. Example: 10
     * @queryParam max_price number Maximum order total. Example: 100
     * @response 200 {"current_page": 1, "data": [{"id": 1, "state": "pending", "total": "25.99"}], "total": 1}
     * @response 403 {"message": "This action is unauthorized."}
     */
    public function orders(ListOrderRequest $request, Restaurant $restaurant): LengthAwarePaginator
    {
        $this->authorize('view-any', [Order::class, $restaurant]);

        $filters = $request->validated();
        $filters['restaurant_id'] = $restaurant->id;

        return $this->orderService->listOrder(
            null,
            $filters
        );
    }
}
