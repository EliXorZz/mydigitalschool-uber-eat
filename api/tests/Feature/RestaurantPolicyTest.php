<?php

use App\Enums\Role;
use App\Models\Restaurant;
use App\Models\User;

describe('RestaurantController Policy Tests', function () {
    beforeEach(function () {
        $this->admin = User::factory()->create(['role' => Role::ADMIN]);
        $this->restaurantOwner = User::factory()->create(['role' => Role::RESTAURANT]);
        $this->regularUser = User::factory()->create(['role' => Role::USER]);
        $this->otherRestaurantOwner = User::factory()->create(['role' => Role::RESTAURANT]);

        $this->restaurant = Restaurant::factory()->create([
            'owner_id' => $this->restaurantOwner->id,
        ]);
    });

    describe('POST /restaurants', function () {
        it('should allow admin to create a restaurant', function () {
            $response = $this->actingAs($this->admin)
                ->postJson('/api/restaurants', [
                    'name' => 'New Restaurant',
                    'description' => 'A nice restaurant',
                    'score' => 4.5,
                    'price_score' => 2,
                    'type_id' => 1,
                    'owner_id' => $this->restaurantOwner->id,
                ]);

            $response->assertStatus(201);
            $this->assertDatabaseHas('restaurants', [
                'name' => 'New Restaurant',
            ]);
        });

        it('should deny non-admin user to create a restaurant', function () {
            $response = $this->actingAs($this->regularUser)
                ->postJson('/api/restaurants', [
                    'name' => 'New Restaurant',
                    'description' => 'A nice restaurant',
                    'owner_id' => $this->restaurantOwner->id,
                ]);

            $response->assertStatus(403);
        });

        it('should deny restaurant owner (non-admin) to create a restaurant', function () {
            $response = $this->actingAs($this->restaurantOwner)
                ->postJson('/api/restaurants', [
                    'name' => 'New Restaurant',
                    'description' => 'A nice restaurant',
                    'owner_id' => $this->otherRestaurantOwner->id,
                ]);

            $response->assertStatus(403);
        });
    });

    describe('GET /restaurants', function () {
        it('should allow any user to view all restaurants', function () {
            $response = $this->actingAs($this->regularUser)
                ->getJson('/api/restaurants');

            $response->assertStatus(200);
        });

        it('should allow unauthenticated user to view restaurants', function () {
            $response = $this->getJson('/api/restaurants');

            $response->assertStatus(200);
        });
    });

    describe('GET /restaurants/{restaurant}', function () {
        it('should allow any user to view a restaurant', function () {
            $response = $this->actingAs($this->regularUser)
                ->getJson("/api/restaurants/{$this->restaurant->id}");

            $response->assertStatus(200);
        });
    });

    describe('PATCH /restaurants/{restaurant}', function () {
        it('should allow owner to update their restaurant', function () {
            $response = $this->actingAs($this->restaurantOwner)
                ->patchJson("/api/restaurants/{$this->restaurant->id}", [
                    'name' => 'Updated Restaurant Name',
                ]);

            $response->assertStatus(200);
            $this->assertDatabaseHas('restaurants', [
                'id' => $this->restaurant->id,
                'name' => 'Updated Restaurant Name',
            ]);
        });

        it('should deny other users from updating restaurant', function () {
            $response = $this->actingAs($this->otherRestaurantOwner)
                ->patchJson("/api/restaurants/{$this->restaurant->id}", [
                    'name' => 'Hacked Name',
                ]);

            $response->assertStatus(403);
        });

        it('should deny regular user from updating restaurant', function () {
            $response = $this->actingAs($this->regularUser)
                ->patchJson("/api/restaurants/{$this->restaurant->id}", [
                    'name' => 'Hacked Name',
                ]);

            $response->assertStatus(403);
        });
    });

    describe('DELETE /restaurants/{restaurant}', function () {
        it('should allow owner to delete their restaurant', function () {
            $response = $this->actingAs($this->restaurantOwner)
                ->deleteJson("/api/restaurants/{$this->restaurant->id}");

            $response->assertStatus(204);
            $this->assertSoftDeleted('restaurants', [
                'id' => $this->restaurant->id,
            ]);
        });

        it('should deny other users from deleting restaurant', function () {
            $response = $this->actingAs($this->otherRestaurantOwner)
                ->deleteJson("/api/restaurants/{$this->restaurant->id}");

            $response->assertStatus(403);
        });
    });
});
