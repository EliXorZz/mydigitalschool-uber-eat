<?php

use App\Enums\Role;
use App\Models\Dish;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\States\OrderDelivered;
use App\States\OrderPending;

describe('OrderController Policy Tests', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->restaurantOwner = User::factory()->create(['role' => Role::RESTAURANT]);
        $this->regularUser = User::factory()->create(['role' => Role::USER]);
        $this->otherUser = User::factory()->create(['role' => Role::USER]);

        $this->restaurant = Restaurant::factory()->create([
            'owner_id' => $this->restaurantOwner->id,
        ]);

        $this->dish = Dish::factory()->create([
            'restaurant_id' => $this->restaurant->id,
            'price' => 10.00,
        ]);

        $this->order = Order::factory()->create([
            'user_id' => $this->regularUser->id,
            'restaurant_id' => $this->restaurant->id,
            'state' => OrderPending::class,
        ]);

        $this->orderId = $this->order->id;
    });

    describe('GET /orders', function () {
        it('should allow user to view their own orders', function () {
            $response = $this->actingAsWithJWT($this->regularUser)
                ->getJson('/api/orders');

            $response->assertStatus(200);
        });

        it('should deny user from viewing other users orders', function () {
            $response = $this->actingAsWithJWT($this->otherUser)
                ->getJson('/api/orders');

            $response->assertStatus(200);
            $response->assertJsonCount(0, 'data');
        });
    });

    describe('POST /orders', function () {
        it('should allow user to create an order', function () {
            $response = $this->actingAsWithJWT($this->regularUser)
                ->postJson('/api/orders', [
                    'dishes' => [
                        ['id' => $this->dish->id, 'quantity' => 2],
                    ],
                ]);

            $response->assertStatus(201);
        });
    });

    describe('GET /orders/{order}', function () {
        it('should allow user to view their own order', function () {
            $response = $this->actingAsWithJWT($this->regularUser)
                ->getJson("/api/orders/{$this->order->id}");

            $response->assertStatus(200);
        });

        it('should deny user from viewing other users order', function () {
            $response = $this->actingAsWithJWT($this->otherUser)
                ->getJson("/api/orders/{$this->order->id}");

            $response->assertStatus(403);
        });
    });

    describe('POST /orders/{order}/state', function () {
        it('should allow restaurant owner to update order state', function () {
            $response = $this->actingAsWithJWT($this->restaurantOwner)
                ->postJson("/api/orders/{$this->order->id}/state", [
                    'state' => 'preparing',
                ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('orders', [
                'id' => $this->order->id,
                'state' => 'preparing',
            ]);
        });

        it('should deny regular user from updating order state', function () {
            $response = $this->actingAsWithJWT($this->regularUser)
                ->postJson("/api/orders/{$this->order->id}/state", [
                    'state' => 'preparing',
                ]);

            $response->assertStatus(403);
        });

        it('should deny other restaurant owner from updating order', function () {
            $otherRestaurant = Restaurant::factory()->create([
                'owner_id' => $this->otherUser->id,
            ]);

            $response = $this->actingAsWithJWT($this->otherUser)
                ->postJson("/api/orders/{$this->order->id}/state", [
                    'state' => 'preparing',
                ]);

            $response->assertStatus(403);
        });
    });

    describe('POST /orders/{order}/cancel', function () {
        it('should allow user to cancel their pending order', function () {
            $response = $this->actingAsWithJWT($this->regularUser)
                ->postJson("/api/orders/{$this->order->id}/cancel");

            $response->assertStatus(204);
            $this->assertDatabaseMissing('orders', ['id' => $this->order->id]);
        });

        it('should deny user from canceling other users order', function () {
            $response = $this->actingAsWithJWT($this->otherUser)
                ->postJson("/api/orders/{$this->order->id}/cancel");

            $response->assertStatus(403);
        });

        it('should deny user from canceling non-pending order', function () {
            $this->order->state->transitionTo(OrderDelivered::class);

            $response = $this->actingAsWithJWT($this->regularUser)
                ->postJson("/api/orders/{$this->order->id}/cancel");

            $response->assertStatus(403);
        });
    });
});
