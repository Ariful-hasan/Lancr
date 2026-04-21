<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Request\CreateMilestoneDto;
use App\Dto\Request\CreateWorkOrderDto;
use App\Dto\Response\MilestoneResponse;
use App\Dto\Response\WorkOrderResponse;
use App\Entity\WorkOrder;
use App\Repository\Contracts\WorkOrderRepositoryInterface;
use App\Service\WorkOrderService;
use App\Trait\ApiResponder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/work-orders', name: 'api_work_order_')]
class WorkOrderController extends AbstractController
{
    use ApiResponder;

    public function __construct(
        private readonly WorkOrderService $workOrderService,
        private readonly WorkOrderRepositoryInterface $workOrderRepository,
    ) {}

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateWorkOrderDto $dto): JsonResponse
    {
        $workOrder = $this->workOrderService->createWorkOrder($dto);

        return $this->respond(
            WorkOrderResponse::fromEntity($workOrder, $this->workOrderRepository),
            'Work order created',
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(WorkOrder $workOrder): JsonResponse
    {
        return $this->respond(WorkOrderResponse::fromEntity($workOrder, $this->workOrderRepository));
    }

    #[Route('/{id}/accept', name: 'accept', methods: ['POST'])]
    public function accept(WorkOrder $workOrder): JsonResponse
    {
        $this->workOrderService->acceptWorkOrder($workOrder);
        
        return $this->respond(
            WorkOrderResponse::fromEntity($workOrder, $this->workOrderRepository),
            'Work order accepted'
        );
    }

    #[Route('/{id}/reject', name: 'reject', methods: ['POST'])]
    public function reject(WorkOrder $workOrder): JsonResponse
    {
        $this->workOrderService->rejectWorkOrder($workOrder);
        
        return $this->respond(
            WorkOrderResponse::fromEntity($workOrder, $this->workOrderRepository),
            'Work order rejected'
        );
    }

    #[Route('/{id}/milestones', name: 'add_milestone', methods: ['POST'])]
    public function addMilestone(WorkOrder $workOrder, #[MapRequestPayload] CreateMilestoneDto $dto): JsonResponse 
    {
        $milestone = $this->workOrderService->addMilestone($workOrder, $dto);

        return $this->respond(
            MilestoneResponse::fromEntity($milestone), 
            'Milestone added',
            Response::HTTP_CREATED
        );
    }

    #[Route('/{id}/dispute', name: 'dispute', methods: ['POST'])]
    public function dispute(WorkOrder $workOrder): JsonResponse
    {
        $this->workOrderService->raiseDispute($workOrder);
        
        return $this->respond(
            WorkOrderResponse::fromEntity($workOrder, $this->workOrderRepository),
            'Dispute raised'
        );
    }
}
