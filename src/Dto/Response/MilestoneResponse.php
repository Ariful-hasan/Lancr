<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\Milestone;
use DateTimeInterface;

final readonly class MilestoneResponse
{
    public function __construct(
        public int $id,
        public string $title,
        public string $description,
        public string $amount,
        public string $dueDate,
        public string $status,
        public ?string $reviewNote,
        public ?string $submittedAt,
        public ?string $reviewedAt,
        public string $createdAt,
        public int $workOrderId,
    ) {}

    public static function fromEntity(Milestone $milestone): self
    {
        return new self(
            id: $milestone->getId(),
            title: $milestone->getTitle(),
            description: $milestone->getDescription(),
            amount: $milestone->getAmount(),
            dueDate: $milestone->getDueDate()->format(DateTimeInterface::ATOM),
            status: $milestone->getStatus()->label(),
            reviewNote: $milestone->getReviewNote(),
            submittedAt: $milestone->getSubmittedAt()?->format(DateTimeInterface::ATOM),
            reviewedAt: $milestone->getReviewedAt()?->format(DateTimeInterface::ATOM),
            createdAt: $milestone->getCreatedAt()->format(DateTimeInterface::ATOM),
            workOrderId: $milestone->getWorkOrder()->getId(),
        );
    }
}
