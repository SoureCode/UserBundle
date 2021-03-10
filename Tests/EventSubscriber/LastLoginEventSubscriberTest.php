<?php

namespace SoureCode\Bundle\User\Tests\EventSubscriber;

use SoureCode\Bundle\User\Tests\AbstractUserTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class LastLoginEventSubscriberTest extends AbstractUserTestCase
{
    public function testUpdateLastLogin(): void
    {
        // Arrange
        static::bootKernel();
        $user = $this->addTestUser();
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        /**
         * @var TokenStorageInterface $tokenStorage
         */
        $tokenStorage = static::$kernel->getContainer()->get('security.token_storage');
        /**
         * @var EventDispatcherInterface $eventDispatcher
         */
        $eventDispatcher = static::$kernel->getContainer()->get('event_dispatcher');
        $tokenStorage->setToken($token);
        $event = new InteractiveLoginEvent(new Request(), $token);
        static::assertNull($user->getLastLogin());

        // Act
        $eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);

        // Assert
        static::assertNotNull($user->getLastLogin());
    }
}
