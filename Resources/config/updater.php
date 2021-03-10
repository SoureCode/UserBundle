<?php

use SoureCode\Component\User\Updater\CanonicalizeUserFieldsUpdater;
use SoureCode\Component\User\Updater\CanonicalizeUserFieldsUpdaterInterface;
use SoureCode\Component\User\Updater\PasswordUpdater;
use SoureCode\Component\User\Updater\PasswordUpdaterInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Updater
    // ============================
    $services->set('sourecode.user.updater.password', PasswordUpdater::class)
        ->lazy()
        ->args(
            [
                service('sourecode.user.encoder.password'),
            ]
        )
        ->alias(PasswordUpdaterInterface::class, 'sourecode.user.updater.password');

    $services->set('sourecode.user.updater.canonicalize_user_fields', CanonicalizeUserFieldsUpdater::class)
        ->lazy()
        ->args(
            [
                service('sourecode.user.canonicalizer.email'),
            ]
        )
        ->alias(CanonicalizeUserFieldsUpdaterInterface::class, 'sourecode.user.updater.canonicalize_user_fields');
};
