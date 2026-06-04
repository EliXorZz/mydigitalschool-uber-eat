<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Models\Order;
use App\Services\OrderService;
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
     *
     * @authenticated
     * @queryParam status string Filter by order status. Example: pending
     * @queryParam date_from date Filter orders from this date. Example: 2026-01-01
     * @queryParam date_to date Filter orders up to this date. Example: 2026-12-31
     * @queryParam min_price number Minimum order total. Example: 10
     * @queryParam max_price number Maximum order total. Example: 100
     * @response 200 {"current_page": 1, "data": [{"id": 1, "state": "pending", "total": "25.99", "allowed_transitions": ["confirmed"]}], "total": 1}
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
     *
     * @authenticated
     * @response 201 {"data": {"id": 1, "state": "pending", "total": "25.99"}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
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
     *
     * @authenticated
     * @response 200 {"data": {"id": 1, "state": "pending", "total": "25.99", "allowed_transitions": ["confirmed"]}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Order]."}
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
     * Cancels an existing order. Only pending orders can be cancelled by their owner.
     *
     * @authenticated
     * @response 204 {}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Order]."}
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
     * Updates the state of an order. Restricted to the restaurant owner.
     *
     * @authenticated
     * @response 200 {"data": {"id": 1, "state": "confirmed", "allowed_transitions": ["preparing"]}}
     * @response 403 {"message": "This action is unauthorized."}
     * @response 404 {"message": "No query results for model [App\\Models\\Order]."}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
     */
    public function updateState(UpdateOrderRequest $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $order = $this->orderService->updateState(
            $order,
            $request->validated('state')
        );

        return response()->json(['data' => $order]);
    }
}
