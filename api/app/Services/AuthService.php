<?php

namespace App\Services;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;

class AuthService
{
    public function register(string $name, string $email, string $password, Role $role = Role::USER): array
    {
        $user = (new User)
            ->create([
                'name' => $name,
                'email' => $email,
                'role' => $role,
                'password' => $password,
            ]);

        $token = auth()->login($user);

        return [
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];
    }

    public function login(string $email, string $password): array
    {
        if (! auth()->attempt(['email' => $email, 'password' => $password])) {
            throw new AuthenticationException;
        }

        $user = auth()->user();
        $token = auth()->login($user);

        return [
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];
    }

    public function session(): User
    {
        $user = auth()->user();
        if (! $user) {
            throw new AuthenticationException;
        }

        return $user;
    }

    public function refresh(): array
    {
        $token = auth()->refresh();

        return [
            'token' => $token,
            'type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
        ];
    }

    public function logout(): void
    {
        auth()->logout();
    }
}
