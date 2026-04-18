<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\Request\RejectMilestoneDto;
use App\Entity\Milestone;
use App\Entity\Payment;
use App\Entity\WorkOrder;
use App\Enum\MilestoneStatus;
use App\Enum\PaymentStatus;
use App\Enum\WorkOrderStatus;
use App\Exception\AccessDeniedException;
use App\Exception\InvalidStatusTransitionException;
use App\Message\EntityStatusChangedMessage;
use App\Repository\Contracts\MilestoneRepositoryInterface;
use App\Repository\Contracts\PaymentRepositoryInterface;
use App\Repository\Contracts\WorkOrderRepositoryInterface;
use App\Trait\CanValidateEntity;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Throwable;

class MilestoneService
{
    use CanValidateEntity;

    public function __construct(
        private readonly WorkOrderRepositoryInterface $workOrderRepository,
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly Security $security,
        private readonly ValidatorInterface $validator,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $bus,
    ) {}

    public function submit(Milestone $milestone): void
    {
        $user = $this->security->getUser();

        if ($milestone->getWorkOrder()->getFreelancer() !== $user) {
            throw new AccessDeniedException('Only the freelancer can submit a milestone.');
        }
        $oldStatus = $milestone->getStatus()->label();
        $milestone->setStatus(MilestoneStatus::SUBMITTED);
        $milestone->setSubmittedAt(new \DateTimeImmutable());

        $this->entityManager->beginTransaction();
        try {
            $this->milestoneRepository->save($milestone);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $milestone->getId(),
                Milestone::class,
                $oldStatus,
                MilestoneStatus::SUBMITTED->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function approve(Milestone $milestone): void
    {
        $user = $this->security->getUser();
        $workOrder = $milestone->getWorkOrder();

        // ─── 1. Guards (Outside Transaction) ──────────────────────────
        if ($workOrder->getClient() !== $user) {
            throw new AccessDeniedException('Only the client can approve milestones.');
        }

        if ($milestone->getStatus() !== MilestoneStatus::SUBMITTED) {
            throw new InvalidStatusTransitionException(MilestoneStatus::SUBMITTED, $milestone->getStatus());
        }

        if ($workOrder->getStatus() !== WorkOrderStatus::ACTIVE) {
            throw new InvalidStatusTransitionException(WorkOrderStatus::ACTIVE, $workOrder->getStatus());
        }

        // ─── 2. Transactional Block ───────────────────────────────────
        $this->entityManager->beginTransaction();

        try {
            // A. Update Milestone Status
            $oldMilestoneStatus = $milestone->getStatus()->label();
            $milestone->setStatus(MilestoneStatus::APPROVED);
            $milestone->setReviewedAt(new \DateTimeImmutable());
            $this->milestoneRepository->save($milestone);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $milestone->getId(),
                Milestone::class,
                $oldMilestoneStatus,
                MilestoneStatus::APPROVED->label()
            ));

            // B. Create Payment Record
            $payment = new Payment();
            $payment->setWorkOrder($workOrder);
            $payment->setMilestone($milestone);
            $payment->setAmount($milestone->getAmount());
            $payment->setStatus(PaymentStatus::PAID);
            $payment->setPaidAt(new \DateTimeImmutable());

            $this->validate($payment);
            $this->paymentRepository->save($payment);

            // C. Auto-complete Work Order if all milestones are paid
            $totalMilestoneAmount = $this->workOrderRepository->getTotalAllocatedAmount($workOrder);

            if (
                $this->milestoneRepository->allApproved($workOrder) &&
                bccomp($totalMilestoneAmount, $workOrder->getBudget(), 2) >= 0
            ) {
                $oldWorkOrderStatus = $workOrder->getStatus()->label();
                $workOrder->setStatus(WorkOrderStatus::COMPLETED);
                $this->workOrderRepository->save($workOrder);

                $this->bus->dispatch(new EntityStatusChangedMessage(
                    $workOrder->getId(),
                    WorkOrder::class,
                    $oldWorkOrderStatus,
                    WorkOrderStatus::COMPLETED->label()
                ));
            }

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    public function reject(Milestone $milestone, RejectMilestoneDto $dto): void
    {
        $user = $this->security->getUser();

        if ($milestone->getWorkOrder()->getClient() !== $user) {
            throw new AccessDeniedException('Only the client can reject milestones.');
        }

        $oldStatus = $milestone->getStatus()->label();
        $milestone->setStatus(MilestoneStatus::REJECTED);
        $milestone->setReviewNote($dto->note);
        $milestone->setReviewedAt(new \DateTimeImmutable());

        $this->entityManager->beginTransaction();
        try {
            $this->milestoneRepository->save($milestone);

            $this->bus->dispatch(new EntityStatusChangedMessage(
                $milestone->getId(),
                Milestone::class,
                $oldStatus,
                MilestoneStatus::REJECTED->label()
            ));

            $this->entityManager->commit();
        } catch (Throwable $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }
}
