<?php

namespace App\Security\Voter;

use App\Entity\Event;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class EventVoter extends Voter
{
    public const EDIT = 'EVENT_EDIT';
    public const VIEW = 'EVENT_VIEW';
    public const DELETE = 'EVENT_DELETE';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        // replace with your own logic
        // https://symfony.com/doc/current/security/voters.html
        return in_array($attribute, [self::EDIT, self::VIEW, self::DELETE])
            && $subject instanceof \App\Entity\Event;
    }

    protected function voteOnAttribute(string $attribute,mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
//        dd($user);
        if (!$user instanceof UserInterface) {
            return false;
        }

        $event = $subject;

        switch ($attribute) {
            case self::VIEW:
                return $this->canView($event, $user);
            case self::EDIT or self::DELETE:
                return $this->canEditOrDelete($event, $user);
            default:
                throw new \LogicException('This code should not be reached!');
        }
    }


    private function canView(Event $event, UserInterface $user): bool
    {
        if ($event->isPublic()) {
            return true;
        }

        return $event->isPublic();
    }

    private function canEditOrDelete(Event $event, UserInterface $user): bool
    {
        return $event->getCreatedBy() === $user;
    }
}
