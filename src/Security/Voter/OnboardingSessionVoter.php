<?php

namespace App\Security\Voter;

use App\Entity\OnboardingSession;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class OnboardingSessionVoter extends Voter
{
    public const EDIT = 'edit';
    public const VIEW = 'view';
    public const DELETE = 'delete';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof OnboardingSession;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        /** @var OnboardingSession $session */
        $session = $subject;

        return match ($attribute) {
            self::VIEW => $this->canView($session, $user),
            self::EDIT => $this->canEdit($session, $user),
            self::DELETE => $this->canDelete($session, $user),
            default => false,
        };
    }

    private function canView(OnboardingSession $session, User $user): bool
    {
        if ($user->isInternalUser()) {
            return true;
        }

        return $session->getUser()->getId() === $user->getId();
    }

    private function canEdit(OnboardingSession $session, User $user): bool
    {
        if ($user->isInternalUser()) {
            return $session->getStatus() === OnboardingSession::STATUS_IN_PROGRESS;
        }

        return $session->getUser()->getId() === $user->getId()
            && $session->getStatus() === OnboardingSession::STATUS_IN_PROGRESS;
    }

    private function canDelete(OnboardingSession $session, User $user): bool
    {
        if ($user->isInternalUser()) {
            return true;
        }

        return $session->getUser()->getId() === $user->getId();
    }
}
