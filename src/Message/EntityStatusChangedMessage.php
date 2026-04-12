<?php

declare(strict_types=1);

namespace App\Message;

readonly class EntityStatusChangedMessage
{
    public function __construct(
        public int $id,
        public string $class,
        public string $oldStatus,
        public string $newStatus,
    ) {}
}
