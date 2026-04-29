<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

/**
 * @group Orders
 */
class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    /**
     * List orders.
     *
     * Returns all orders for the authenticated user.
     */
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $orders = $this->orderService->listOrder($user);

        return response()->json($orders);
    }

    /**
     * Create an order.
     *
     * Creates a new order with specified dishes.
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        $order = $this->orderService->createOrder(
            $request->user(),
            $request->validated()
        );

        return response()->json(['data' => $order], 201);
    }

    /**
     * Get an order.
     *
     * Returns the details of a specific order by ID.
     */
    public function show(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        $order = $this->orderService->getOrder($order->id);

        return response()->json(['data' => $order]);
    }

    /**
     * Cancel an order.
     *
     * Cancels an existing order. Only pending orders can be cancelled.
     */
    public function cancel(Order $order): JsonResponse
    {
        $this->authorize('cancel', $order);

        $this->orderService->cancelOrder($order);

        return response()->json(null, 204);
    }

    /**
     * Update order state.
     *
     * Updates the state of an existing order.
     */
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
