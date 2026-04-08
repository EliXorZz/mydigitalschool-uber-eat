<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDishRequest;
use App\Http\Requests\UpdateDishRequest;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Services\DishService;
use Illuminate\Http\JsonResponse;

class DishController extends Controller
{
    public function __construct(
        private DishService $dishService,
    ) {}

    public function index(Restaurant $restaurant): JsonResponse
    {
        $dishes = $this->dishService->listDish($restaurant);

        return response()->json($dishes);
    }

    public function store(StoreDishRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('create', [Dish::class, $restaurant]);

        $dish = $this->dishService->createDish(
            $restaurant,
            $request->validated()
        );

        return response()->json(['data' => $dish], 201);
    }

    public function show(Dish $dish): JsonResponse
    {
        $dish = $this->dishService->getDish($dish->id);

        return response()->json(['data' => $dish]);
    }

    public function update(UpdateDishRequest $request, Dish $dish): JsonResponse
    {
        $this->authorize('update', $dish);

        $dish = $this->dishService->updateDish(
            $dish->id,
            $request->validated(),
        );

        return response()->json(['data' => $dish]);
    }

    public function destroy(Dish $dish): JsonResponse
    {
        $this->authorize('delete', $dish);

        $this->dishService->deleteDish($dish->id);

        return response()->json(null, 204);
    }
}
