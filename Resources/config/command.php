<?php

use SoureCode\Bundle\User\Command\UserCreateCommand;
use SoureCode\Bundle\User\Command\UserDemoteCommand;
use SoureCode\Bundle\User\Command\UserPromoteCommand;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Command
    // ============================
    $services->set('sourecode.user.command.create', UserCreateCommand::class)
        ->tag('console.command')
        ->lazy()
        ->args(
            [
                service('sourecode.user.service.user'),
                service('validator'),
            ]
        );

    $services->set('sourecode.user.command.promote', UserPromoteCommand::class)
        ->tag('console.command')
        ->lazy()
        ->args(
            [
                service('sourecode.user.service.user'),
            ]
        );

    $services->set('sourecode.user.command.demote', UserDemoteCommand::class)
        ->tag('console.command')
        ->lazy()
        ->args(
            [
                service('sourecode.user.service.user'),
            ]
        );
};
