<?php
declare(strict_types=1);

namespace App\Repository\Contracts;

use App\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;
    public function delete(User $user): void;
    public function findByEmail(string $email): ?User;
    public function findById(int $id): ?User;
}