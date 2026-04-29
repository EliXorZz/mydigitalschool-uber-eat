<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Models\Restaurant;
use App\Services\RestaurantService;
use Illuminate\Http\JsonResponse;

class RestaurantController extends Controller
{
    public function __construct(
        private RestaurantService $restaurantService,
    ) {}

    public function index(): JsonResponse
    {
        $restaurants = $this->restaurantService->listRestaurant();

        return response()->json($restaurants);
    }

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

    public function show(Restaurant $restaurant): JsonResponse
    {
        $restaurant = $this->restaurantService->getRestaurant($restaurant->id);

        return response()->json(['data' => $restaurant]);
    }

    public function update(UpdateRestaurantRequest $request, Restaurant $restaurant): JsonResponse
    {
        $this->authorize('update', $restaurant);

        $restaurant = $this->restaurantService->updateRestaurant(
            $restaurant->id,
            $request->validated(),
        );

        return response()->json(['data' => $restaurant]);
    }

    public function destroy(Restaurant $restaurant): JsonResponse
    {
        $this->authorize('delete', $restaurant);

        $this->restaurantService->deleteRestaurant($restaurant->id);

        return response()->json(null, 204);
    }
}
