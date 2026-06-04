<?php

namespace App\Http\Controllers;

use App\Models\RestaurantType;
use Illuminate\Http\JsonResponse;

class RestaurantTypeController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => RestaurantType::orderBy('name')->get()]);
    }
}
