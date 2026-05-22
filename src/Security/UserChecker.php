<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\User as AppUser;
use App\Security\Exception\AccountSuspendedException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof AppUser) {
            return;
        }

        $this->assertNotSuspended($user);

        if (!$user->isVerified()) {
            throw new CustomUserMessageAccountStatusException(
                'Votre compte n’est pas encore vérifié. Veuillez confirmer votre adresse email.',
            );
        }
    }

    private function assertNotSuspended(AppUser $user): void
    {
        if (!method_exists($user, 'isSuspended')) {
            return;
        }

        if ($user->isSuspended()) {
            throw new AccountSuspendedException();
        }
    }

    public function checkPostAuth(UserInterface $user, ?TokenInterface $token = null): void
    {
    }
}
