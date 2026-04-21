<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\RejectMilestoneDto;
use App\Dto\Response\MilestoneResponse;
use App\Entity\Milestone;
use App\Service\MilestoneService;
use App\Trait\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api/milestones', name: 'api_milestone_')]
class MilestoneController extends AbstractController
{
    use ApiResponder;

    public function __construct(
        private readonly MilestoneService $milestoneService
    ) {}

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(Milestone $milestone): JsonResponse
    {
        return $this->respond(MilestoneResponse::fromEntity($milestone));
    }

    #[Route('/{id}/submit', name: 'submit', methods: ['POST'])]
    public function submit(Milestone $milestone): JsonResponse
    {
        $this->milestoneService->submit($milestone);
        
        return $this->respond(MilestoneResponse::fromEntity($milestone), 'Milestone submitted for review');
    }

    #[Route('/{id}/approve', name: 'approve', methods: ['POST'])]
    public function approve(Milestone $milestone): JsonResponse
    {
        $this->milestoneService->approve($milestone);

        return $this->respond(MilestoneResponse::fromEntity($milestone), 'Milestone approved and payment recorded');
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(
        Milestone $milestone, 
        #[MapRequestPayload] RejectMilestoneDto $dto
    ): JsonResponse {
        $this->milestoneService->reject($milestone, $dto);

        return $this->respond(MilestoneResponse::fromEntity($milestone), 'Milestone rejected/revisions requested');
    }
}
