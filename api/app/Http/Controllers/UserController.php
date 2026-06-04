<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * @group Users
 */
class UserController extends Controller
{
    /**
     * List restaurant owners.
     *
     * Returns all users with the restaurant role. Admin only.
     *
     * @authenticated
     * @response 200 {"data": [{"id": 2, "name": "Owner Name", "email": "owner@example.com", "role": "restaurant"}]}
     * @response 403 {"message": "This action is unauthorized."}
     */
    public function owners(): JsonResponse
    {
        $this->authorize('viewAny', User::class);

        $owners = User::where('role', Role::RESTAURANT)
            ->orderBy('name')
            ->get();

        return response()->json(['data' => $owners]);
    }
}
