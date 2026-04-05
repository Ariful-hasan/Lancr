<?php

namespace App\Exception;

class UnauthorizedException extends \RuntimeException
{
    public function __construct(string $message = 'You are not authorized to perform this action.')
    {
        parent::__construct($message);
    }
}
