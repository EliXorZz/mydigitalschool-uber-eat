<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

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
     * Creates a new user account and returns a JWT bearer token.
     *
     * @unauthenticated
     * @response 201 {"data": {"token": "eyJ...", "type": "bearer", "expires_in": 3600}}
     * @response 409 {"message": "Email already in use."}
     * @response 422 {"message": "The given data was invalid.", "errors": {"name": ["The name field is required."]}}
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
     * Logs in an existing user and returns a JWT bearer token.
     *
     * @unauthenticated
     * @response 200 {"data": {"token": "eyJ...", "type": "bearer", "expires_in": 3600}}
     * @response 401 {"message": "Unauthenticated."}
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
     * Generates a new JWT token from the current valid token (rotation).
     *
     * @authenticated
     * @response 200 {"data": {"token": "eyJ...", "type": "bearer", "expires_in": 3600}}
     * @response 401 {"message": "Unauthenticated."}
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
     *
     * @authenticated
     * @response 200 {"data": {"id": 1, "name": "John Doe", "email": "john@example.com", "role": "user", "created_at": "2026-01-01T00:00:00.000000Z"}}
     * @response 401 {"message": "Unauthenticated."}
     */
    public function me(): JsonResponse
    {
        $user = $this->authService->session();

        return response()->json(['data' => $user]);
    }

    /**
     * Update current user profile.
     *
     * Updates the authenticated user's name, email, and optionally password.
     *
     * @authenticated
     * @response 200 {"data": {"id": 1, "name": "John Doe", "email": "john@example.com", "role": "user"}}
     * @response 401 {"message": "Unauthenticated."}
     * @response 422 {"message": "The given data was invalid.", "errors": {}}
     */
    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->session();

        $data = array_filter($request->validated(), fn ($v) => $v !== null);

        if (isset($data['email']) && $data['email'] !== $user->email) {
            if (User::where('email', $data['email'])->exists()) {
                throw ValidationException::withMessages([
                    'email' => ['The email has already been taken.'],
                ]);
            }
        }

        $user->update($data);

        return response()->json(['data' => $user]);
    }

    /**
     * Delete current user account.
     *
     * Permanently deletes the authenticated user's account.
     *
     * @authenticated
     * @response 204 {}
     * @response 401 {"message": "Unauthenticated."}
     */
    public function destroy(): JsonResponse
    {
        $user = $this->authService->session();

        auth()->logout();
        $user->delete();

        return response()->json(null, 204);
    }
}
