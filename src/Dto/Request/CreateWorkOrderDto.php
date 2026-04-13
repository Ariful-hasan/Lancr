<?php

declare(strict_types=1);

namespace App\Dto\Request;

use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateWorkOrderDto
{
    public function __construct(
        #[Assert\NotBlank(message: 'Title is required')]
        #[Assert\Length(min: 2, max: 255)]
        public string $title,

        #[Assert\Length(max: 1000)]
        public ?string $description,

        #[Assert\NotBlank(message: 'Budget is required')]
        #[Assert\Positive(message: 'Budget must be positive')]
        public string $budget,

        #[Assert\NotBlank(message: 'Deadline is required')]
        // Accepting string to handle custom date formats easily
        #[Assert\DateTime(format: 'Y-m-d', message: 'Deadline must be a valid date (Y-m-d)')]
        public string $deadline,

        #[Assert\NotBlank(message: 'Freelancer ID is required')]
        public int $freelancer_id,
    ) {}
}
