<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\EntityStatusChangedMessage;
use App\Repository\Contracts\MilestoneRepositoryInterface;
use App\Repository\Contracts\WorkOrderRepositoryInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Psr\Log\LoggerInterface;

#[AsMessageHandler]
class EntityStatusChangedHandler
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly WorkOrderRepositoryInterface $workOrderRepository,
        private readonly MilestoneRepositoryInterface $milestoneRepository,
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(EntityStatusChangedMessage $message): void
    {
        $this->logger->info('Processing status change message', [
            'class' => $message->class,
            'id' => $message->id,
            'oldStatus' => $message->oldStatus,
            'newStatus' => $message->newStatus,
        ]);

        // 1. Fetch the Entity based on the class and ID provided in the message
        $entity = match ($message->class) {
            \App\Entity\WorkOrder::class => $this->workOrderRepository->findById($message->id),
            \App\Entity\Milestone::class => $this->milestoneRepository->findById($message->id),
            default => null,
        };

        if (!$entity) {
            $this->logger->warning('Entity not found for status change message', [
                'class' => $message->class,
                'id' => $message->id,
            ]);
            return;
        }

        // 2. Logic to decide what notification to send
        $this->handleNotification($entity, $message->oldStatus, $message->newStatus);

        $this->logger->info('Successfully handled status change notification', [
            'class' => $message->class,
            'id' => $message->id,
        ]);
    }

    private function handleNotification(object $entity, string $oldStatus, string $newStatus): void
    {
        // This is where you would put your specific email/notification logic
        // Example:
        // if ($entity instanceof \App\Entity\WorkOrder && $newStatus === 'Active') {
        //     $this->sendWorkOrderStartedEmail($entity);
        // }
    }
}
