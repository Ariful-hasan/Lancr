<?php

declare(strict_types=1);

namespace App\Dto\Response;

final readonly class ApiResponse
{
    public function __construct(
        public mixed $data = null,
        public ?string $message = null,
        public ?array $meta = null,
    ) {}
}
