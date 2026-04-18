<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Request\RegisterDto;
use App\Entity\User;
use App\Enum\UserRole;
use App\Repository\Contracts\UserRepositoryInterface;
use App\Trait\CanValidateEntity;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AuthService
{
    use CanValidateEntity;

    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator
    ) {}

    public function register(RegisterDto $dto): User
    {
        $user = new User();
        $user->setName($dto->name);
        $user->setEmail($dto->email);
        $user->setRole(UserRole::from($dto->role));

        $hashed = $this->passwordHasher->hashPassword($user, $dto->password);
        $user->setPassword($hashed);

        // System validation (e.g. UniqueEntity)
        $this->validate($user);

        $this->userRepository->save($user);

        return $user;
    }

    public function findByEmail(string $email): ?User
    {
        return $this->userRepository->findByEmail($email);
    }
}
