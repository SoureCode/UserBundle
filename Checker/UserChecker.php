<?php

namespace SoureCode\Bundle\User\Checker;

use SoureCode\Component\User\Model\Advanced\AdvancedUserInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;
use Symfony\Component\Security\Core\User\UserChecker as BaseUserChecker;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

class UserChecker extends BaseUserChecker
{
    /**
     * {@inheritdoc}
     */
    public function checkPreAuth(BaseUserInterface $user): void
    {
        if ($user instanceof BasicUserInterface) {
            if (!$user->isEnabled()) {
                $exception = new DisabledException();
                $exception->setUser($user);

                throw $exception;
            }
        }

        if ($user instanceof AdvancedUserInterface) {
            if ($user->isLocked()) {
                $exception = new LockedException();
                $exception->setUser($user);

                throw $exception;
            }

            if ($user->isExpired()) {
                $exception = new AccountExpiredException();
                $exception->setUser($user);

                throw $exception;
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function checkPostAuth(BaseUserInterface $user): void
    {
        if ($user instanceof AdvancedUserInterface) {
            if ($user->isCredentialsExpired()) {
                $exception = new CredentialsExpiredException();
                $exception->setUser($user);

                throw $exception;
            }
        }
    }
}
