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
    const ACCEPT        = 'acceptWorkOrder';
    const REJECT        = 'rejectWorkOrder';
    const DISPUTE       = 'disputeWorkOrder';
    const VIEW          = 'viewWorkOrder';
    const ADD_MILESTONE = 'addMilestone';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::ACCEPT, self::REJECT, self::DISPUTE, self::VIEW, self::ADD_MILESTONE])
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
            self::ACCEPT        => $this->canAccept($subject, $user, $vote),
            self::REJECT        => $this->canReject($subject, $user, $vote),
            self::DISPUTE       => $this->canDispute($subject, $user, $vote),
            self::VIEW          => $this->canView($subject, $user),
            self::ADD_MILESTONE => $this->canAddMilestone($subject, $user, $vote),
            default => false,
        };
    }

    private function canAccept(WorkOrder $workOrder, UserInterface $user, ?Vote $vote = null): bool
    {
        if ($workOrder->getFreelancer() !== $user) {
            $vote?->addReason('Only the assigned freelancer can accept this work order.');

            return false;
        }

        return true;
    }

    private function canReject(WorkOrder $workOrder, UserInterface $user, ?Vote $vote = null): bool
    {
        if ($workOrder->getFreelancer() !== $user) {
            $vote?->addReason('Only the assigned freelancer can reject this work order.');

            return false;
        }

        return true;
    }

    private function canDispute(WorkOrder $workOrder, UserInterface $user, ?Vote $vote = null): bool
    {
        if ($workOrder->getClient() !== $user && $workOrder->getFreelancer() !== $user) {
            $vote?->addReason('Only participants can dispute this work order.');

            return false;
        }

        return true;
    }

    private function canView(WorkOrder $workOrder, UserInterface $user): bool
    {
        return $workOrder->getClient() === $user || $workOrder->getFreelancer() === $user;
    }

    private function canAddMilestone(WorkOrder $workOrder, UserInterface $user, ?Vote $vote = null): bool
    {
        if ($workOrder->getClient() !== $user) {
            $vote?->addReason('Only the client can add milestones.');

            return false;
        }

        return true;
    }
}
