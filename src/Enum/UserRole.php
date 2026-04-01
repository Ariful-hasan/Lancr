<?php

declare(strict_types=1);
namespace App\Enum;

enum UserRole: string
{
    case CLIENT     = 'ROLE_CLIENT';
    case FREELANCER = 'ROLE_FREELANCER';
    case ADMIN      = 'ROLE_ADMIN';
}