<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\UserRole;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ClubConfirmationUserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getRole() === UserRole::CLUB_NOT_CONFIRMED->value) {
            throw new CustomUserMessageAccountStatusException('Your account is not yet confirmed.');
        }
    }
}
