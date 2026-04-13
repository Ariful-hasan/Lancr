<?php

declare(strict_types=1);

namespace App\Dto\Response;

use App\Entity\WorkOrder;
use App\Repository\Contracts\WorkOrderRepositoryInterface;
use DateTimeInterface;

final readonly class WorkOrderResponse
{
    /**
     * @param array<MilestoneResponse> $milestones
     */
    public function __construct(
        public int $id,
        public string $title,
        public ?string $description,
        public string $budget,
        public string $amountPaid,
        public string $status,
        public string $deadline,
        public string $createdAt,
        public ?string $updatedAt,
        public int $clientId,
        public string $clientName,
        public int $freelancerId,
        public string $freelancerName,
        public array $milestones = [],
    ) {}

    public static function fromEntity(WorkOrder $workOrder, WorkOrderRepositoryInterface $repository): self
    {
        return new self(
            id: $workOrder->getId(),
            title: $workOrder->getTitle(),
            description: $workOrder->getDescription(),
            budget: $workOrder->getBudget(),
            amountPaid: $repository->getTotalPaidAmount($workOrder),
            status: $workOrder->getStatus()->label(),
            deadline: $workOrder->getDeadline()->format(DateTimeInterface::ATOM),
            createdAt: $workOrder->getCreatedAt()->format(DateTimeInterface::ATOM),
            updatedAt: $workOrder->getUpdatedAt()?->format(DateTimeInterface::ATOM),
            clientId: $workOrder->getClient()->getId(),
            clientName: $workOrder->getClient()->getName(),
            freelancerId: $workOrder->getFreelancer()->getId(),
            freelancerName: $workOrder->getFreelancer()->getName(),
            milestones: array_map(
                fn ($m) => MilestoneResponse::fromEntity($m),
                $workOrder->getMilestones()->toArray()
            ),
        );
    }
}
