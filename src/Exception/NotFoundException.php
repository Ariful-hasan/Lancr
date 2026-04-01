<?php

declare(strict_types=1);

namespace App\Exception;

use RuntimeException;

class NotFoundException extends RuntimeException
{
    public function __construct(string $resource = 'Resource')
    {
        parent::__construct("{$resource} not found");
    }
}
