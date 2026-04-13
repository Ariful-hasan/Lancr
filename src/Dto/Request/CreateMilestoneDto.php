<?php

declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateMilestoneDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(min: 2, max: 255)]
        public string $title,

        #[Assert\NotBlank(message: 'Description is required')]
        #[Assert\Length(max: 1000)]
        public string $description,

        #[Assert\NotBlank(message: 'Amount is required')]
        #[Assert\Positive(message: 'Amount must be positive')]
        public string $amount,

        #[Assert\NotBlank(message: 'Due date is required')]
        #[Assert\DateTime(format: 'Y-m-d', message: 'Due date must be a valid date (Y-m-d)')]
        public string $due_date,
    ) {}
}
