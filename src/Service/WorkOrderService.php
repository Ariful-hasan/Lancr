<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\CreateMilestoneDto;
use App\Dto\CreateWorkOrderDto;
use App\Entity\Milestone;
use App\Entity\User;
use App\Entity\WorkOrder;
use App\Enum\MilestoneStatus;
use App\Enum\UserRole;
use App\Enum\WorkOrderStatus;
use App\Exception\AccessDeniedException;
use App\Exception\BudgetExceededException;
use App\Exception\InvalidRoleException;
use App\Exception\InvalidStatusTransitionException;
use App\Exception\NotFoundException;
use App\Message\EntityStatusChangedMessage;
use App\Repository\Contracts\MilestoneRepositoryInterface;
use App\Repository\Contracts\UserRepositoryInterface;
use App\Repository\Contracts\WorkOrderRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Trait\CanValidateEntity;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

class WorkOrderService
{
    use CanValidateEntity;

    public function __construct(
        private readonly WorkOrderRepositoryInterface $workOrderRepository,
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly MessageBusInterface $bus,
        private readonly EntityManagerInterface $entityManager,
    ) {}

    public function createWorkOrder(CreateWorkOrderDto $dto): WorkOrder
    {
        /** @var User $client */
        $client = $this->security->getUser();

        if (!$this->security->isGranted(UserRole::CLIENT->value)) {
            throw new AccessDeniedException('Only clients can create work orders.');
        }

        $freelancer = $this->userRepository->findById($dto->freelancer_id);

        if (!$freelancer) {
            throw new NotFoundException('Freelancer not found.');
        }

        if (!in_array(UserRole::FREELANCER->value, $freelancer->getRoles())) {
            throw new InvalidRoleException(UserRole::FREELANCER->value);
        }

        if ($client->getId() === $freelancer->getId()) {
            throw new AccessDeniedException('You cannot hire yourself.');
        }

        $workOrder = new WorkOrder();
        $workOrder->setTitle($dto->title);
        $workOrder->setDescription($dto->description);
        $workOrder->setBudget($dto->budget);
        $workOrder->setDeadline(new \DateTimeImmutable($dto->deadline));
        $workOrder->setClient($client);
        $workOrder->setFreelancer($freelancer);
        $workOrder->setStatus(WorkOrderStatus::PENDING);

        $this->validate($workOrder);

        $this->entityManager->beginTransaction();
        try {
            $this->workOrderRepository->save($workOrder);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $workOrder->getId(),
                WorkOrder::class,
                WorkOrderStatus::DRAFT->label(),
                $workOrder->getStatus()->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $workOrder;
    }

    public function acceptWorkOrder(WorkOrder $workOrder): void
    {
        $freelancer = $this->security->getUser();

        if ($workOrder->getFreelancer() !== $freelancer) {
            throw new AccessDeniedException('You are not the assigned freelancer.');
        }

        if ($workOrder->getStatus() !== WorkOrderStatus::PENDING) {
            throw new InvalidStatusTransitionException(WorkOrderStatus::PENDING, $workOrder->getStatus());
        }

        $oldStatus = $workOrder->getStatus()->label();
        $workOrder->setStatus(WorkOrderStatus::ACTIVE);

        $this->entityManager->beginTransaction();
        try {
            $this->workOrderRepository->save($workOrder);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $workOrder->getId(),
                WorkOrder::class,
                $oldStatus,
                WorkOrderStatus::ACTIVE->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function addMilestone(WorkOrder $workOrder, CreateMilestoneDto $dto): Milestone
    {
        $client = $this->security->getUser();

        if ($workOrder->getClient() !== $client) {
            throw new AccessDeniedException('You are not the client of this work order.');
        }

        if ($workOrder->getStatus() !== WorkOrderStatus::ACTIVE) {
            throw new InvalidStatusTransitionException(WorkOrderStatus::ACTIVE, $workOrder->getStatus());
        }

        $this->checkBudget($workOrder, $dto->amount);

        $milestone = new Milestone();
        $milestone->setTitle($dto->title);
        $milestone->setDescription($dto->description);
        $milestone->setAmount($dto->amount);
        $milestone->setDueDate(new \DateTimeImmutable($dto->due_date));
        $milestone->setWorkOrder($workOrder);
        $milestone->setStatus(MilestoneStatus::PENDING);

        $this->validate($milestone);

        $this->entityManager->beginTransaction();
        try {
            $this->milestoneRepository->save($milestone);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $milestone->getId(),
                Milestone::class,
                'NEW',
                MilestoneStatus::PENDING->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }

        return $milestone;
    }

    public function checkBudget(WorkOrder $workOrder, string $newMileStoneAmount): void
    {
        $totalMilestoneAmount = array_reduce(
            $workOrder->getMilestones()->toArray(),
            function (string $carry, Milestone $milestone): string {
                return bcadd($carry, $milestone->getAmount(), 2);
            },
            '0.00'
        );

        $newTotal = bcadd($totalMilestoneAmount, $newMileStoneAmount, 2);

        if (bccomp($newTotal, $workOrder->getBudget(), 2) > 0) {
            throw new BudgetExceededException($workOrder->getBudget(), $newTotal);
        }
    }

    public function rejectWorkOrder(WorkOrder $workOrder): void
    {
        $user = $this->security->getUser();

        if ($workOrder->getFreelancer() !== $user) {
            throw new AccessDeniedException('You are not the assigned freelancer.');
        }

        if ($workOrder->getStatus() !== WorkOrderStatus::PENDING) {
            throw new InvalidStatusTransitionException(WorkOrderStatus::PENDING, $workOrder->getStatus());
        }

        $oldStatus = $workOrder->getStatus()->label();
        $workOrder->setStatus(WorkOrderStatus::REJECTED);

        $this->entityManager->beginTransaction();
        try {
            $this->workOrderRepository->save($workOrder);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $workOrder->getId(),
                WorkOrder::class,
                $oldStatus,
                WorkOrderStatus::REJECTED->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function raiseDispute(WorkOrder $workOrder): void
    {
        $user = $this->security->getUser();

        if ($workOrder->getClient() !== $user && $workOrder->getFreelancer() !== $user) {
            throw new AccessDeniedException('You are not a participant in this contract.');
        }

        if (!in_array($workOrder->getStatus(), [WorkOrderStatus::ACTIVE, WorkOrderStatus::COMPLETED])) {
            throw new InvalidStatusTransitionException($workOrder->getStatus(), WorkOrderStatus::DISPUTED);
        }

        $oldStatus = $workOrder->getStatus()->label();
        $workOrder->setStatus(WorkOrderStatus::DISPUTED);

        $this->entityManager->beginTransaction();
        try {
            $this->workOrderRepository->save($workOrder);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $workOrder->getId(),
                WorkOrder::class,
                $oldStatus,
                WorkOrderStatus::DISPUTED->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
