<?php

namespace SoureCode\Bundle\User\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use SoureCode\Component\User\Updater\CanonicalizeUserFieldsUpdaterInterface;

class UserEventSubscriber implements EventSubscriber
{
    protected UserServiceInterface $service;

    protected CanonicalizeUserFieldsUpdaterInterface $updater;

    public function __construct(UserServiceInterface $service, CanonicalizeUserFieldsUpdaterInterface $updater)
    {
        $this->service = $service;
        $this->updater = $updater;
    }

    public function prePersist(LifecycleEventArgs $event): void
    {
        $user = $event->getEntity();

        $this->updateUserFields($user);
    }

    private function updateUserFields(object $user): void
    {
        if (!$user instanceof BasicUserInterface) {
            return;
        }

        $this->updater->update($user);
        $this->service->updatePassword($user);
    }

    public function preUpdate(LifecycleEventArgs $event): void
    {
        $user = $event->getEntity();
        $this->updateUserFields($user);
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }
}
