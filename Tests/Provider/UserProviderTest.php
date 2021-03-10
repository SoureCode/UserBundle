<?php

namespace SoureCode\Bundle\User\Tests\Provider;

use Doctrine\ORM\EntityManager;
use ReflectionObject;
use SoureCode\Bundle\User\Provider\UserProvider;
use SoureCode\Bundle\User\Tests\AbstractUserTestCase;
use SoureCode\Bundle\User\Tests\Mock\Entity\FooUser;
use SoureCode\Bundle\User\Tests\Mock\Model\BarUser;
use SoureCode\Bundle\User\Tests\Mock\Model\UnsupportedUser;
use Symfony\Bridge\Doctrine\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class UserProviderTest extends AbstractUserTestCase
{
    public function testLoadByUsername(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        /**
         * @var UserProvider $provider
         */
        $provider = $container->get('sourecode.user.provider');
        $this->addTestUser();

        // Act
        $provider->loadUserByUsername('foo@bar.com');

        // Assert
        self::assertNull(null);
    }

    public function testSupportedClass(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        /**
         * @var UserProvider $provider
         */
        $provider = $container->get('sourecode.user.provider');

        // Act and Assert
        self::assertTrue($provider->supportsClass(FooUser::class), 'Supports same class');
        self::assertTrue($provider->supportsClass(BarUser::class), 'Supports child class');
        self::assertFalse($provider->supportsClass(UnsupportedUser::class));
    }

    public function testRefreshUserNotExist(): void
    {
        // Assert
        $this->expectException(UsernameNotFoundException::class);

        // Arrange
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        /**
         * @var UserProvider $provider
         */
        $provider = $container->get('sourecode.user.provider');
        $user = new FooUser();

        $reflection = new ReflectionObject($user);
        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        // Act
        $provider->refreshUser($user);
    }

    public function testRefreshUserNotSupported(): void
    {
        // Assert
        $this->expectException(UnsupportedUserException::class);

        // Arrange
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        /**
         * @var UserProvider $provider
         */
        $provider = $container->get('sourecode.user.provider');
        $user = new UnsupportedUser();

        // Act
        $provider->refreshUser($user);
    }

    public function testRefreshUser(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $container = $kernel->getContainer();
        /**
         * @var UserProvider $provider
         */
        $provider = $container->get('sourecode.user.provider');
        $user = $this->addTestUser();
        $expected = 'bar@test.com';

        /**
         * @var ManagerRegistry $doctrine
         */
        $doctrine = $container->get('doctrine');
        /**
         * @var EntityManager $manager
         */
        $manager = $doctrine->getManager();

        $user->setEmail($expected);
        $manager->flush();
        $manager->clear();

        // Act
        $updatedUser = $provider->refreshUser($user);

        // Assert
        self::assertSame($expected, $updatedUser->getEmail());
    }
}
