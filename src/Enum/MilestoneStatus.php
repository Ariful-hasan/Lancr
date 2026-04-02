<?php

namespace App\Enum;

enum MilestoneStatus: int
{
    case PENDING   = 0;
    case SUBMITTED = 1;
    case APPROVED  = 2;
    case REJECTED  = 3;

    public function label(): string
    {
        return strtolower($this->name); 
    }
}