<?php

use SoureCode\Bundle\User\Service\UserMailer;
use SoureCode\Bundle\User\Service\UserService;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->defaults()
        ->public();

    // ============================
    // = Service
    // ============================
    $services->set('sourecode.user.service.mailer', UserMailer::class)
        ->lazy()
        ->args(
            [
                service('mailer'),
                service('sourecode.token.service.token'),
                service('translator'),
            ]
        );

    $services->set('sourecode.user.service.user', UserService::class)
        ->lazy()
        ->args(
            [
                service('sourecode.user.object_manager'),
                service('sourecode.token.service.token'),
                service('sourecode.user.service.mailer'),
                service('sourecode.user.repository.user'),
                service('sourecode.user.updater.password'),
                service('sourecode.user.updater.canonicalize_user_fields'),
                service('security.helper'),
                param('sourecode.user.config.class'),
            ]
        )
        ->alias(UserServiceInterface::class, 'sourecode.user.service.user');
};
