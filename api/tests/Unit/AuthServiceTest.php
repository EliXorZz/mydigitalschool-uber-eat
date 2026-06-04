<?php

use App\Enums\Role;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;

describe('AuthService', function () {
    beforeEach(function () {
        $this->service = app(AuthService::class);
    });

    describe('register()', function () {
        it('creates a user and returns a JWT token', function () {
            $result = $this->service->register('John Doe', 'john@example.com', 'password123');

            expect($result)->toHaveKeys(['token', 'type', 'expires_in'])
                ->and($result['type'])->toBe('bearer')
                ->and($result['token'])->not->toBeEmpty();

            $this->assertDatabaseHas('users', [
                'email' => 'john@example.com',
                'name' => 'John Doe',
            ]);
        });

        it('assigns USER role by default', function () {
            $this->service->register('Jane Doe', 'jane@example.com', 'password123');

            $user = User::where('email', 'jane@example.com')->first();
            expect($user->role)->toBe(Role::USER);
        });

        it('assigns a custom role when specified', function () {
            $this->service->register('Owner', 'owner@example.com', 'password123', Role::RESTAURANT);

            $user = User::where('email', 'owner@example.com')->first();
            expect($user->role)->toBe(Role::RESTAURANT);
        });

        it('hashes the password', function () {
            $this->service->register('Alice', 'alice@example.com', 'plaintext');

            $user = User::where('email', 'alice@example.com')->first();
            expect(password_verify('plaintext', $user->getAuthPassword()))->toBeTrue();
        });

        it('throws an exception when email is already taken', function () {
            User::factory()->create(['email' => 'taken@example.com']);

            expect(fn () => $this->service->register('Bob', 'taken@example.com', 'password123'))
                ->toThrow(\Illuminate\Database\QueryException::class);
        });
    });

    describe('login()', function () {
        it('returns a JWT token for valid credentials', function () {
            User::factory()->create(['email' => 'user@example.com', 'password' => 'password123']);

            $result = $this->service->login('user@example.com', 'password123');

            expect($result)->toHaveKeys(['token', 'type', 'expires_in'])
                ->and($result['type'])->toBe('bearer')
                ->and($result['token'])->not->toBeEmpty();
        });

        it('throws AuthenticationException for wrong password', function () {
            User::factory()->create(['email' => 'user@example.com', 'password' => 'correct']);

            expect(fn () => $this->service->login('user@example.com', 'wrong'))
                ->toThrow(AuthenticationException::class);
        });

        it('throws AuthenticationException for unknown email', function () {
            expect(fn () => $this->service->login('ghost@example.com', 'password123'))
                ->toThrow(AuthenticationException::class);
        });
    });

    describe('refresh()', function () {
        it('returns a new token from a valid existing token', function () {
            $user = User::factory()->create();
            $original = $this->service->login($user->email, 'password');

            auth()->setToken($original['token']);
            $refreshed = $this->service->refresh();

            expect($refreshed)->toHaveKeys(['token', 'type', 'expires_in'])
                ->and($refreshed['token'])->not->toBe($original['token']);
        });
    });

    describe('session()', function () {
        it('returns the authenticated user', function () {
            $user = User::factory()->create();
            auth()->login($user);

            $result = $this->service->session();
            expect($result->id)->toBe($user->id);
        });

        it('throws AuthenticationException when unauthenticated', function () {
            expect(fn () => $this->service->session())
                ->toThrow(AuthenticationException::class);
        });
    });
});
