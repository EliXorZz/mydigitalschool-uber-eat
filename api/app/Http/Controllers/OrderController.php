<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(): JsonResponse
    {
        $user = auth()->user();
        $orders = $this->orderService->listOrder($user);

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->orderService->createOrder(
            $request->user(),
            $request->validated()
        );

        return response()->json(['data' => $order], 201);
    }

    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order = $this->orderService->getOrder($order->id);

        return response()->json(['data' => $order]);
    }

    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $this->orderService->cancelOrder($order);

        return response()->json(null, 204);
    }

    public function updateState(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order = $this->orderService->updateState(
            $order,
            $request->input('state')
        );

        return response()->json(['data' => $order]);
    }
}
