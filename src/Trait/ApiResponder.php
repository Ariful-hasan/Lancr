<?php

declare(strict_types=1);

namespace App\Trait;

use App\Dto\Response\ApiResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

trait ApiResponder
{
    /**
     * @param mixed $data
     * @param string|null $message
     * @param int $status
     * @param array<string, mixed> $headers
     * @return JsonResponse
     */
    protected function respond(mixed $data = null, ?string $message = null, int $status = Response::HTTP_OK, array $headers = []): JsonResponse
    {
        return $this->json(new ApiResponse($data, $message), $status, $headers);
    }
}
