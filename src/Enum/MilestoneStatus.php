<?php

namespace App\Enum;

enum MilestoneStatus: int
{
    case DRAFT     = 0;
    case PENDING   = 1;
    case SUBMITTED = 2;
    case APPROVED  = 3;
    case REJECTED  = 4;

    public function label(): string
    {
        return strtolower($this->name); 
    }
}