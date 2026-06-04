<?php

namespace App\Exceptions;

class OrderDishesFromDifferentRestaurantsException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            message: 'All dishes must be from the same restaurant',
            code: 'DIFFERENT_RESTAURANTS',
        );
    }
}
