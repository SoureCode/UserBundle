<?php

namespace SoureCode\Bundle\User\Tests\Checker;

use DateInterval;
use DateTime;
use PHPUnit\Framework\TestCase;
use SoureCode\Bundle\User\Checker\UserChecker;
use SoureCode\Bundle\User\Tests\Mock\Entity\FooUser;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CredentialsExpiredException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\Exception\LockedException;

class UserCheckerTest extends TestCase
{
    public function testCheckPreAuth(): void
    {
        // Assert
        $this->expectException(DisabledException::class);

        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->disable();

        // Act
        $checker->checkPreAuth($user);
    }

    public function testCheckPreAuthValid(): void
    {
        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();

        // Act
        $checker->checkPreAuth($user);

        // Assert
        self::assertNull(null);
    }

    public function testLocked(): void
    {
        // Assert
        $this->expectException(LockedException::class);

        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();
        $user->setLocked(true);

        // Act
        $checker->checkPreAuth($user);
    }

    public function testUnlocked(): void
    {
        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();
        $user->setLocked(false);

        // Act
        $checker->checkPreAuth($user);

        // Assert
        self::assertNull(null);
    }

    public function testExpired(): void
    {
        // Assert
        $this->expectException(AccountExpiredException::class);

        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();
        $user->setLocked(false);
        $user->setExpiresAt(new DateTime());

        // Act
        $checker->checkPreAuth($user);
    }

    public function testNotExpired(): void
    {
        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();
        $user->setLocked(false);
        $user->setExpiresAt((new DateTime())->add(new DateInterval('PT1H')));

        // Act
        $checker->checkPreAuth($user);

        // Assert
        self::assertNull(null);
    }

    public function testCredentialExpired(): void
    {
        // Assert
        $this->expectException(CredentialsExpiredException::class);

        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();
        $user->setLocked(false);
        $user->setExpiresAt((new DateTime())->add(new DateInterval('PT1H')));
        $user->setCredentialsExpiresAt(new DateTime());

        // Act
        $checker->checkPostAuth($user);
    }

    public function testCredentialNonExpired(): void
    {
        // Arrange
        $checker = new UserChecker();
        $user = new FooUser();
        $user->enable();
        $user->setLocked(false);
        $user->setExpiresAt((new DateTime())->add(new DateInterval('PT1H')));
        $user->setCredentialsExpiresAt((new DateTime())->add(new DateInterval('PT1H')));

        // Act
        $checker->checkPostAuth($user);

        // Assert
        self::assertNull(null);
    }
}
