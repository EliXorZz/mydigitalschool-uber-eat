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
     * Returns all dishes belonging to a specific restaurant.
     */
    public function index(Restaurant $restaurant): JsonResponse
    {
        $dishes = $this->dishService->listDish($restaurant);

        return response()->json($dishes);
    }

    /**
     * Create a dish.
     *
     * Creates a new dish for a specific restaurant.
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
     * Returns the details of a specific dish by ID.
     */
    public function show(Dish $dish): JsonResponse
    {
        $dish = $this->dishService->getDish($dish->id);

        return response()->json(['data' => $dish]);
    }

    /**
     * Update a dish.
     *
     * Updates the details of an existing dish.
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
     * Deletes an existing dish.
     */
    public function destroy(Dish $dish): JsonResponse
    {
        $this->authorize('delete', $dish);

        $this->dishService->deleteDish($dish->id);

        return response()->json(null, 204);
    }
}
