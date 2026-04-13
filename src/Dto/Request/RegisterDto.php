<?php

declare(strict_types=1);

namespace App\Dto\Request;

use App\Enum\UserRole;
use Symfony\Component\Validator\Constraints as Assert;

readonly class RegisterDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Name is required')]
        #[Assert\Length(min: 3, max: 100, minMessage: 'Name must be at least 3 characters long', maxMessage: 'Name cannot be longer than 100 characters')]
        public string $name,

        #[Assert\NotBlank(message: 'Email is required')]
        #[Assert\Email(message: 'Please provide a valid email address')]
        public string $email,

        #[Assert\NotBlank(message: 'Password is required')]
        #[Assert\Length(min: 6, message: 'Password must be at least 6 characters long')]
        public string $password,

        #[Assert\NotBlank(message: 'Role is required')]
        #[Assert\Choice(callback: [UserRole::class, 'cases'], message: 'Invalid role provided')]
        public string $role,
    ) {}
}
