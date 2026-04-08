<?php

namespace App\Enums;

enum Role: string
{
    case USER = 'user';
    case RESTAURANT = 'restaurant';
    case ADMIN = 'admin';
}
