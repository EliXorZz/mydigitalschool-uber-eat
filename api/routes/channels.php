<?php

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('restaurants.{id}.orders', function (User $user, string $id) {
    return $user->role === Role::RESTAURANT
        && $user->restaurants->contains('id', $id);
});
