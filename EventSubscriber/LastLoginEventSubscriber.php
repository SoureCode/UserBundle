<?php

namespace SoureCode\Bundle\User\EventSubscriber;

use DateTime;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class LastLoginEventSubscriber implements EventSubscriberInterface
{
    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => 'onSecurityInteractiveLogin',
        ];
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        if ($user instanceof BasicUserInterface) {
            $user->setLastLogin(new DateTime());
            $this->service->save($user);
        }
    }
}
