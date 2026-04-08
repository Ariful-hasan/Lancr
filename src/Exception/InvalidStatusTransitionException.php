<?php

namespace App\Exception;

use BackedEnum;

class InvalidStatusTransitionException extends \LogicException
{
    public function __construct(
        BackedEnum $from,
        BackedEnum $to
    ) {
        parent::__construct(
            "Cannot transition from '{$from->label()}' to '{$to->label()}'."
        );
    }
}
