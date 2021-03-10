<?php

use SoureCode\Bundle\User\Provider\UserProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Provider
    // ============================
    $services->set('sourecode.user.provider', UserProvider::class)
        ->public()
        ->lazy()
        ->args(
            [
                service('sourecode.user.service.user'),
                param('sourecode.user.config.class'),
            ]
        );
};
