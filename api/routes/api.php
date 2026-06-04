<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RestaurantController;
use App\Http\Controllers\RestaurantTypeController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate;

Route::middleware('throttle:auth')->prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware(Authenticate::class)->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/me', [AuthController::class, 'update']);
        Route::delete('/me', [AuthController::class, 'destroy']);
    });
});

// Public
Route::middleware('throttle:public')->group(function () {
    Route::get('restaurant-types', [RestaurantTypeController::class, 'index']);
    Route::apiResource('restaurants', RestaurantController::class)->shallow()->only(['index', 'show']);
    Route::apiResource('restaurants.dishes', DishController::class)->shallow()->only(['index', 'show']);
});

Route::middleware('throttle:api')->group(function () {
    Route::apiResource('restaurants', RestaurantController::class)->shallow()->except(['index', 'show']);
    Route::apiResource('restaurants.dishes', DishController::class)->shallow()->except(['index', 'show']);
});

Route::middleware(['throttle:api', Authenticate::class])->group(function () {
    Broadcast::routes();

    // File upload
    Route::post('upload', [UploadController::class, 'store']);

    // Admin helpers
    Route::get('users/owners', [UserController::class, 'owners']);

    // Orders
    Route::apiResource('orders', OrderController::class)->shallow();
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('orders/{order}/state', [OrderController::class, 'updateState']);

    Route::get('restaurants/{restaurant}/orders', [RestaurantController::class, 'orders']);
});
