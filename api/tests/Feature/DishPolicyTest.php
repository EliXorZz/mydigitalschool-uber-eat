<?php

use App\Enums\Role;
use App\Models\Dish;
use App\Models\Restaurant;
use App\Models\User;

describe('DishController Policy Tests', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->restaurantOwner = User::factory()->create(['role' => Role::RESTAURANT]);
        $this->regularUser = User::factory()->create(['role' => Role::USER]);
        $this->otherRestaurantOwner = User::factory()->create(['role' => Role::RESTAURANT]);

        $this->restaurant = Restaurant::factory()->create([
            'owner_id' => $this->restaurantOwner->id,
        ]);

        $this->otherRestaurant = Restaurant::factory()->create([
            'owner_id' => $this->otherRestaurantOwner->id,
        ]);

        $this->dish = Dish::factory()->create([
            'restaurant_id' => $this->restaurant->id,
        ]);
    });

    describe('GET /restaurants/{restaurant}/dishes', function () {
        it('should allow any user to view dishes', function () {
            $response = $this->actingAs($this->regularUser)
                ->getJson("/api/restaurants/{$this->restaurant->id}/dishes");

            $response->assertStatus(200);
        });

        it('should allow unauthenticated user to view dishes', function () {
            $response = $this->getJson("/api/restaurants/{$this->restaurant->id}/dishes");

            $response->assertStatus(200);
        });
    });

    describe('POST /restaurants/{restaurant}/dishes', function () {
        it('should allow restaurant owner to create a dish', function () {
            $response = $this->actingAs($this->restaurantOwner)
                ->postJson("/api/restaurants/{$this->restaurant->id}/dishes", [
                    'name' => 'Pizza',
                    'description' => 'Delicious pizza',
                    'price' => 12.99,
                ]);

            $response->assertStatus(201);
            $this->assertDatabaseHas('dishes', [
                'name' => 'Pizza',
                'restaurant_id' => $this->restaurant->id,
            ]);
        });

        it('should deny regular user from creating a dish', function () {
            $response = $this->actingAs($this->regularUser)
                ->postJson("/api/restaurants/{$this->restaurant->id}/dishes", [
                    'name' => 'Pizza',
                    'description' => 'Delicious pizza',
                    'price' => 12.99,
                ]);

            $response->assertStatus(403);
        });

        it('should deny non-owner restaurant from creating a dish', function () {
            $response = $this->actingAs($this->otherRestaurantOwner)
                ->postJson("/api/restaurants/{$this->restaurant->id}/dishes", [
                    'name' => 'Pizza',
                    'description' => 'Delicious pizza',
                    'price' => 12.99,
                ]);

            $response->assertStatus(403);
        });
    });

    describe('GET /dishes/{dish}', function () {
        it('should allow any user to view a dish', function () {
            $response = $this->actingAs($this->regularUser)
                ->getJson("/api/dishes/{$this->dish->id}");

            $response->assertStatus(200);
        });
    });

    describe('PATCH /dishes/{dish}', function () {
        it('should allow restaurant owner to update their dish', function () {
            $response = $this->actingAs($this->restaurantOwner)
                ->patchJson("/api/dishes/{$this->dish->id}", [
                    'name' => 'Updated Pizza',
                ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('dishes', [
                'id' => $this->dish->id,
                'name' => 'Updated Pizza',
            ]);
        });

        it('should deny other restaurant owner from updating dish', function () {
            $response = $this->actingAs($this->otherRestaurantOwner)
                ->patchJson("/api/dishes/{$this->dish->id}", [
                    'name' => 'Hacked Pizza',
                ]);

            $response->assertStatus(403);
        });

        it('should deny regular user from updating dish', function () {
            $response = $this->actingAs($this->regularUser)
                ->patchJson("/api/dishes/{$this->dish->id}", [
                    'name' => 'Hacked Pizza',
                ]);

            $response->assertStatus(403);
        });
    });

    describe('DELETE /dishes/{dish}', function () {
        it('should allow restaurant owner to delete their dish', function () {
            $response = $this->actingAs($this->restaurantOwner)
                ->deleteJson("/api/dishes/{$this->dish->id}");

            $response->assertStatus(204);
            $this->assertSoftDeleted('dishes', [
                'id' => $this->dish->id,
            ]);
        });

        it('should deny other restaurant owner from deleting dish', function () {
            $response = $this->actingAs($this->otherRestaurantOwner)
                ->deleteJson("/api/dishes/{$this->dish->id}");

            $response->assertStatus(403);
        });
    });
});
