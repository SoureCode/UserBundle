<?php

use SoureCode\Component\User\Encoder\PasswordEncoder;
use SoureCode\Component\User\Encoder\PasswordEncoderInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Encoder
    // ============================
    $services->set('sourecode.user.encoder.password', PasswordEncoder::class)
        ->lazy()
        ->args(
            [
                service('security.encoder_factory'),
            ]
        )
        ->alias(PasswordEncoderInterface::class, 'sourecode.user.encoder.password');
};
