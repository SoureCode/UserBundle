<?php

use SoureCode\Bundle\User\EventSubscriber\LastLoginEventSubscriber;
use SoureCode\Bundle\User\EventSubscriber\UserEventSubscriber;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = EventSubscriber
    // ============================
    $services->set('sourecode.user.event_subscriber.last_login', LastLoginEventSubscriber::class)
        ->tag('kernel.event_subscriber')
        ->lazy()
        ->args(
            [
                service('sourecode.user.service.user'),
            ]
        );

    $services->set('sourcecode.user.event_subscriber.user', UserEventSubscriber::class)
        ->tag('doctrine.event_subscriber')
        ->lazy()
        ->args(
            [
                service('sourecode.user.service.user'),
                service('sourecode.user.updater.canonicalize_user_fields'),
            ]
        );
};
