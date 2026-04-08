<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $token = $this->authService->register(
            $request->input('name'),
            $request->input('email'),
            $request->input('password'),
            Role::ADMIN
        );

        return response()->json(['data' => $token], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        return response()->json(['data' => $token]);
    }

    public function refresh(): JsonResponse
    {
        $token = $this->authService->refresh();

        return response()->json(['data' => $token]);
    }

    public function me(): JsonResponse
    {
        $user = $this->authService->session();

        return response()->json(['data' => $user]);
    }
}
