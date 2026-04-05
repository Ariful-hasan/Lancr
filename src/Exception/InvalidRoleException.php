<?php

namespace App\Exception;

class InvalidRoleException extends \LogicException
{
    public function __construct(string $expectedRole)
    {
        parent::__construct("User must have role '{$expectedRole}' to perform this action.");
    }
}
