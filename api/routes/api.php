<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DishController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\RestaurantController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;
use PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/refresh', [AuthController::class, 'refresh']);

    Route::middleware(Authenticate::class)->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
    });
});

Route::apiResource('restaurants', RestaurantController::class)
    ->shallow();

Route::apiResource('restaurants.dishes', DishController::class)
    ->shallow();

Route::post('orders/{order}/cancel', [OrderController::class, 'cancel']);
Route::post('orders/{order}/state', [OrderController::class, 'updateState']);
Route::apiResource('orders', OrderController::class)
    ->shallow();
