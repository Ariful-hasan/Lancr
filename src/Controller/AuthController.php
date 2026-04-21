<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\RegisterDto;
use App\Dto\Response\UserResponse;
use App\Entity\User;
use App\Service\AuthService;
use App\Trait\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    use ApiResponder;

    public function __construct(
        private readonly AuthService $authService,
    ) {}

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(#[MapRequestPayload] RegisterDto $dto): JsonResponse
    {
        $user = $this->authService->register($dto);

        return $this->respond(UserResponse::fromEntity($user), 'User registered', Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(#[CurrentUser()] ?User $user): JsonResponse
    {
        if (!$user) {
            return $this->respond(null, 'Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        return $this->respond(UserResponse::fromEntity($user));
    }
}
