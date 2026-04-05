<?php

namespace App\Exception;

class BudgetExceededException extends \LogicException
{
    public function __construct(string $budget, string $total)
    {
        parent::__construct(
            "Total milestone amount '{$total}' exceeds work order budget '{$budget}'."
        );
    }
}
