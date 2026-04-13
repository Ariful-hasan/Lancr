<?php

declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

readonly class RejectMilestoneDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'A rejection note is required')]
        #[Assert\Length(min: 5, max: 1000, minMessage: 'Note must be at least 5 characters', maxMessage: 'Note cannot exceed 1000 characters')]
        public string $note,
    ) {}
}
