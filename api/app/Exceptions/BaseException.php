<?php

namespace App\Exceptions;

use Exception;

class BaseException extends Exception
{
    public function __construct(string $message = '', string $code = 'UNKNOWN_ERROR')
    {
        parent::__construct($message);
    }
}
