<?php

namespace App\Enum;

enum PaymentStatus: int
{
    case PENDING = 0;
    case PAID    = 1;

    public function label(): string
    {
        return strtolower($this->name);
    }
}