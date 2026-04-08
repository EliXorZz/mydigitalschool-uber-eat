<?php

namespace App\Exceptions;

class CannotCancelOrderException extends BaseException
{
    public function __construct()
    {
        parent::__construct(
            message: 'Cannot cancel order when status is not PENDING',
            code: 'CANNOT_CANCEL_ORDER'
        );
    }
}
