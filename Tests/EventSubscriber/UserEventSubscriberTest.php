<?php

namespace SoureCode\Bundle\User\Tests\EventSubscriber;

use SoureCode\Bundle\User\Tests\AbstractUserTestCase;
use SoureCode\Bundle\User\Tests\Mock\Entity\FooUser;

class UserEventSubscriberTest extends AbstractUserTestCase
{
    public function testUpdateOnPersist(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();

        $user = new FooUser();
        $user->setEmail('FoO_@bar.com');
        $user->setPlainPassword('foo');
        self::assertNull($user->getPassword());
        self::assertNull($user->getCanonicalEmail());

        // Act
        $service->save($user);

        // Assert
        self::assertNotNull($user->getPassword());
        self::assertNotNull($user->getCanonicalEmail());
    }

    public function testUpdateFieldsOnUpdate(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();

        $user = new FooUser();
        $user->setEmail('FoO_@bar.com');
        $user->setPlainPassword('foo');
        $service->save($user);

        $previousEmail = $user->getCanonicalEmail();
        $previousPassword = $user->getPassword();

        $user->setEmail('BaR@fOt.com');
        $user->setPlainPassword('bar');

        // Act
        $service->save($user);

        // Assert
        self::assertNotNull($user->getPassword());
        self::assertNotNull($user->getCanonicalEmail());
        self::assertNotSame($previousEmail, $user->getCanonicalEmail());
        self::assertNotSame($previousPassword, $user->getPassword());
    }
}
