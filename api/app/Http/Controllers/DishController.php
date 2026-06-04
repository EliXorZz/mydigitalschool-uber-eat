<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Services\DishService;
use Illuminate\Http\JsonResponse;

/**
 * @group Dishes
 */
class DishController extends Controller
{
    public function __construct(
        private DishService $dishService,
    ) {}

    /**
     * List dishes for a restaurant.
     *
     * Returns a paginated list of dishes for the given restaurant.
     *
     * @unauthenticated
     * @response 200 {"current_page": 1, "data": [{"id": 1, "name": "Pizza Margherita", "price": "12.50", "description": "Classic pizza"}], "total": 1}
     * @response 404 {"message": "No query results for model [App\\Models\\Restaurant]."}
     */
    public function index(Restaurant $restaurant): JsonResponse
    {
        $dishes = $this->dishService->listDish($restaurant);

        return response()->json($dishes);
    }

    /**
     * Create a dish.
     *
     * Creates a new dish for the given restaurant. Restricted to the restaurant owner.
     *
     * @authenticated
     * @response 201 {"data": {"id": 1, "name": "Pizza Margherita", "price": "12.50", "restaurant_id": 1}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
     */
    public function store(StoreDishRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('create', [Dish::class, $restaurant]);

        $dish = $this->dishService->createDish(
            $restaurant,
            $request->validated()
        );

        return response()->json(['data' => $dish], 201);
    }

    /**
     * Get a dish.
     *
     * Returns the details of a specific dish including its restaurant.
     *
     * @unauthenticated
     * @response 200 {"data": {"id": 1, "name": "Pizza Margherita", "price": "12.50", "description": "Classic pizza", "restaurant": {}}}
     * @response 404 {"message": "No query results for model [App\\Models\\Dish]."}
     */
    public function show(Dish $dish): JsonResponse
    {
        $dish = $this->dishService->getDish($dish->id);

        return response()->json(['data' => $dish]);
    }

    /**
     * Update a dish.
     *
     * Updates a dish's fields. Restricted to the restaurant owner.
     *
     * @authenticated
     * @response 200 {"data": {"id": 1, "name": "Pizza Margherita Updated", "price": "13.50"}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Dish]."}
     */
    public function update(UpdateDishRequest $request, Dish $dish): JsonResponse
    {
        $this->authorize('update', $dish);

        $dish = $this->dishService->updateDish(
            $dish->id,
            $request->validated(),
        );

        return response()->json(['data' => $dish]);
    }

    /**
     * Delete a dish.
     *
     * Soft-deletes a dish. Restricted to the restaurant owner.
     *
     * @authenticated
     * @response 204 {}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Dish]."}
     */
    public function destroy(Dish $dish): JsonResponse
    {
        $this->authorize('delete', $dish);

        $this->dishService->deleteDish($dish->id);

        return response()->json(null, 204);
    }
}
