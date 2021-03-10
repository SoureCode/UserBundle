<?php

use SoureCode\Bundle\User\Form\Type\ChangePasswordType;
use SoureCode\Bundle\User\Form\Type\UserRegisterType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Form
    // ============================
    $services->set('sourecode.user.form.user_register_type', UserRegisterType::class)
        ->lazy()
        ->args(
            [
                param('sourecode.user.config.class'),
            ]
        )
        ->tag('form.type');

    $services->set('sourecode.user.form.change_password_type', ChangePasswordType::class)
        ->lazy()
        ->args(
            [
                param('sourecode.user.config.class'),
            ]
        )
        ->tag('form.type');
};
