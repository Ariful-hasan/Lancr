<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\RegisterDto;
use App\Entity\User;
use App\Service\AuthService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterDto $dto): JsonResponse
    {
        $user = $this->authService->register($dto);

        return $this->json(
            $user,
            Response::HTTP_CREATED,
            [],
            ['groups' => ['user:read']]
        );
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser()] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(['error' => 'user is null'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->json(
            $user,
            Response::HTTP_OK,
            [],
            ['groups' => ['user:read']]
        );
    }
}
