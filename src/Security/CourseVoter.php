<?php

namespace App\Security;

use App\Entity\Course;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CourseVoter extends Voter
{
    public const EDIT = 'COURSE_EDIT';
    public const VIEW = 'COURSE_VIEW';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, [self::EDIT, self::VIEW], true) && $subject instanceof Course;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }
        if (in_array('ROLE_ADMIN', $user->getRoles(), true)) {
            return true;
        }
        if ($attribute === self::EDIT) {
            return $subject->getTeacher()?->getId() === $user->getId();
        }

        return true;
    }
}
