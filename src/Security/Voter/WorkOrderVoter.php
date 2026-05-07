<?php

namespace App\Security\Voter;

use App\Entity\WorkOrder;
use App\Enum\WorkOrderStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class WorkOrderVoter extends Voter
{
    const ACCEPT = 'acceptWorkOrder';

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::ACCEPT])
            && $subject instanceof WorkOrder;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');

            return false;
        }

        return match ($attribute) {
            self::ACCEPT => $this->canAccept($subject, $user, $vote),
            default => false,
        };
    }

    private function canAccept(WorkOrder $workOrder, UserInterface $user, ?Vote $vote = null): bool
    {
        if ($workOrder->getFreelancer() !== $user) {
            $vote?->addReason('Only the assigned freelancer can accept this work order.');

            return false;
        }

        if ($workOrder->getStatus() !== WorkOrderStatus::PENDING) {
            $vote?->addReason(sprintf(
                'Work order must be PENDING, current status is %s.',
                $workOrder->getStatus()->label()
            ));

            return false;
        }

        return true;
    }
}
