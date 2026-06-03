<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
use App\States\OrderState;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

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
    public function index(ListOrderRequest $request): LengthAwarePaginator
    {
        return $this->orderService->listOrder(
            auth()->user(),
            $request->validated()
        );
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
     * List possible order statuses.
     *
     * Returns all possible order status values and labels.
     */
    public function statuses(): JsonResponse
    {
        // Derive from OrderState config / known states
        $list = [
            ['value' => 'pending', 'label' => 'Pending'],
            ['value' => 'preparing', 'label' => 'Preparing'],
            ['value' => 'confirmed', 'label' => 'Confirmed'],
            ['value' => 'delivered', 'label' => 'Delivered'],
            ['value' => 'ready', 'label' => 'Ready for pickup'],
        ];

        return response()->json(['data' => $list]);
    }

    /**
     * Get allowed transitions for a specific order.
     */
    public function transitions(Order $order): JsonResponse
    {
        $this->authorize('view', $order);

        // Use model accessor
        return response()->json(['data' => $order->allowed_transitions]);
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
