<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Milestone;
use App\Enum\MilestoneStatus;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class MilestoneVoter extends Voter
{
    // Define the permissions that this voter supports
    const VIEW    = 'viewMilestone';
    const SUBMIT  = 'submitMilestone';
    const APPROVE = 'approveMilestone';
    const REJECT  = 'rejectMilestone';

    /**
     * Determines if the voter supports the given attribute and subject.
     *
     * @param string $attribute The attribute to check (e.g., 'viewMilestone').
     * @param mixed  $subject   The object to secure (e.g., a Milestone entity).
     * @return bool True if the voter supports the attribute and subject, false otherwise.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        // If the attribute is not one of our defined permissions, or the subject
        // is not a Milestone object, this voter cannot determine access.
        return in_array($attribute, [self::VIEW, self::SUBMIT, self::APPROVE, self::REJECT])
            && $subject instanceof Milestone;
    }

    /**
     * Performs the actual voting on the attribute and subject.
     *
     * @param string         $attribute The attribute to check.
     * @param mixed          $subject   The object to secure.
     * @param TokenInterface $token     The security token.
     * @param Vote|null      $vote      (Optional) Vote object to add reasons to.
     * @return int One of self::ACCESS_GRANTED, self::ACCESS_DENIED, or self::ACCESS_ABSTAIN.
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        // If there is no logged-in user, deny access.
        if (!$user instanceof UserInterface) {
            $vote?->addReason('The user must be logged in to access this resource.');
            return false;
        }

        // Dispatch to the specific authorization method based on the attribute.
        return match ($attribute) {
            self::VIEW    => $this->canView($subject, $user),
            self::SUBMIT  => $this->canSubmit($subject, $user, $vote),
            self::APPROVE => $this->canApprove($subject, $user, $vote),
            self::REJECT  => $this->canReject($subject, $user, $vote),
            default => false, // Should not happen due to the 'supports' check.
        };
    }

    /**
     * Checks if the user can view a Milestone.
     * Access is granted if the user is either the client or the freelancer of the associated WorkOrder.
     */
    private function canView(Milestone $milestone, UserInterface $user): bool
    {
        $workOrder = $milestone->getWorkOrder();
        // Check if the user is the client OR the freelancer of the work order.
        return $workOrder->getClient() === $user || $workOrder->getFreelancer() === $user;
    }

    /**
     * Checks if the user can submit a Milestone for review.
     * Access is granted only if the user is the assigned freelancer of the WorkOrder.
     */
    private function canSubmit(Milestone $milestone, UserInterface $user, ?Vote $vote = null): bool
    {
        // User must be the assigned freelancer.
        if ($milestone->getWorkOrder()->getFreelancer() !== $user) {
            $vote?->addReason('Only the assigned freelancer can submit this milestone.');
            return false;
        }

        return true; // All checks passed.
    }

    /**
     * Checks if the user can approve a Milestone.
     * Access is granted only if the user is the client of the WorkOrder.
     */
    private function canApprove(Milestone $milestone, UserInterface $user, ?Vote $vote = null): bool
    {
        // User must be the client.
        if ($milestone->getWorkOrder()->getClient() !== $user) {
            $vote?->addReason('Only the client can approve this milestone.');
            return false;
        }

        return true; // All checks passed.
    }

    /**
     * Checks if the user can reject a Milestone.
     * Access is granted only if the user is the client of the WorkOrder.
     */
    private function canReject(Milestone $milestone, UserInterface $user, ?Vote $vote = null): bool
    {
        // User must be the client.
        if ($milestone->getWorkOrder()->getClient() !== $user) {
            $vote?->addReason('Only the client can reject this milestone.');
            return false;
        }

        return true; // All checks passed.
    }
}
