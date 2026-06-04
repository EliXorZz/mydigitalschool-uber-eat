<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('POST /api/auth/register', function () {
    it('creates a user and returns 201 with a token', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['token', 'type', 'expires_in']]);

        $this->assertDatabaseHas('users', ['email' => 'john@example.com']);
    });

    it('returns 409 when email is already registered', function () {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Jane Doe',
            'email' => 'taken@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'Email already in use.']);
    });

    it('returns 422 when required fields are missing', function () {
        $response = $this->postJson('/api/auth/register', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    });

    it('returns 422 when password is too short', function () {
        $response = $this->postJson('/api/auth/register', [
            'name' => 'John',
            'email' => 'john@example.com',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    });
});

describe('POST /api/auth/login', function () {
    it('returns 200 with a token for valid credentials', function () {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'password123']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'type', 'expires_in']]);
    });

    it('returns 401 for wrong password', function () {
        User::factory()->create(['email' => 'user@example.com', 'password' => 'correct']);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    });

    it('returns 401 for unknown email', function () {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'ghost@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(401);
    });
});

describe('GET /api/auth/me', function () {
    it('returns the current user when authenticated', function () {
        $user = User::factory()->create();

        $response = $this->actingAsWithJWT($user)
            ->getJson('/api/auth/me');

        $response->assertStatus(200)
            ->assertJsonFragment(['email' => $user->email]);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    });
});

describe('POST /api/auth/me', function () {
    it('updates the user profile', function () {
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAsWithJWT($user)
            ->postJson('/api/auth/me', [
                'name' => 'New Name',
                'email' => $user->email,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['id' => $user->id, 'name' => 'New Name']);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->postJson('/api/auth/me', [
            'name' => 'Hacker',
            'email' => 'hacker@example.com',
        ]);

        $response->assertStatus(401);
    });
});

describe('DELETE /api/auth/me', function () {
    it('deletes the user account and returns 204', function () {
        $user = User::factory()->create();

        $response = $this->actingAsWithJWT($user)
            ->deleteJson('/api/auth/me');

        $response->assertStatus(204);
        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    });

    it('returns 401 when unauthenticated', function () {
        $response = $this->deleteJson('/api/auth/me');

        $response->assertStatus(401);
    });
});

describe('POST /api/auth/refresh', function () {
    it('returns a new token for an authenticated user', function () {
        $user = User::factory()->create();
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $token = $loginResponse->json('data.token');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'type', 'expires_in']]);
    });
});
