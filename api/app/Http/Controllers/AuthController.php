<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;

/**
 * @group Authentication
 */
class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
    ) {}

    /**
     * Register a new user.
     *
     * Creates a new user account and returns an authentication token.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $token = $this->authService->register(
            $request->input('name'),
            $request->input('email'),
            $request->input('password')
        );

        return response()->json(['data' => $token], 201);
    }

    /**
     * Authenticate user.
     *
     * Logs in an existing user and returns an authentication token.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $token = $this->authService->login(
            $request->input('email'),
            $request->input('password')
        );

        return response()->json(['data' => $token]);
    }

    /**
     * Refresh token.
     *
     * Generates a new token for the currently authenticated user.
     */
    public function refresh(): JsonResponse
    {
        $token = $this->authService->refresh();

        return response()->json(['data' => $token]);
    }

    /**
     * Get current user.
     *
     * Returns the currently authenticated user's profile data.
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->session();

        return response()->json(['data' => $user]);
    }
}
