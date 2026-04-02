<?php

namespace App\Enum;

enum WorkOrderStatus: int
{
    case DRAFT     = 0;
    case PENDING   = 1;
    case ACTIVE    = 2;
    case COMPLETED = 3;
    case DISPUTED  = 4;
    case REJECTED  = 5;

    public function label(): string
    {
        return match($this) {
            self::DRAFT     => 'draft',
            self::PENDING   => 'pending',
            self::ACTIVE    => 'active',
            self::COMPLETED => 'completed',
            self::DISPUTED  => 'disputed',
            self::REJECTED  => 'rejected',
        };
    }
}
