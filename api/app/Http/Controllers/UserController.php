<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * List users with role=restaurant (owners). Admin only.
     */
    public function owners(): JsonResponse
    {
        abort_if(
            auth()->user()->role !== Role::ADMIN,
            403,
            'Admin access required.'
        );

        $owners = User::where('role', Role::RESTAURANT)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return response()->json(['data' => $owners]);
    }
}
