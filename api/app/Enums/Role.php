<?php

namespace App\Enums;

/**
 * User role enum.
 */
enum Role: string
{
    case USER = 'user';
    case RESTAURANT = 'restaurant';
    case ADMIN = 'admin';
}
