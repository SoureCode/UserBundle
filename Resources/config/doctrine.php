<?php

use Doctrine\Persistence\ObjectManager;
use SoureCode\Bundle\User\Repository\UserRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Object Manager
    // ============================
    $services->set('sourecode.user.object_manager', ObjectManager::class)
        ->lazy()
        ->args(
            [
                param('sourecode.user.config.model_manager_name'),
            ]
        );

    // ============================
    // = Repository
    // ============================
    $services->set('sourecode.user.repository.user', UserRepository::class)
        ->tag('doctrine.repository_service')
        ->lazy()
        ->args(
            [
                service('sourecode.user.doctrine_registry'),
                param('sourecode.user.config.class'),
            ]
        );

    $services
        ->alias(UserRepository::class, 'sourecode.user.repository.user')
        ->public();
};
