<?php

use SoureCode\Bundle\User\Controller\SecurityController;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Controller
    // ============================
    $services->set('sourecode.user.controller.security', SecurityController::class)
        ->tag('controller.service_arguments')
        ->lazy()
        ->args(
            [
                service('security.authentication_utils'),
                service('security.authorization_checker'),
                service('router'),
                service('sourecode.user.service.user'),
                service('sourecode.token.service.token'),
            ]
        )
        ->bind('$registerConfig', param('sourecode.user.config.register'))
        ->bind('$loginConfig', param('sourecode.user.config.login'))
        ->bind('$changePasswordConfig', param('sourecode.user.config.change_password'))
        ->bind('$changeEmailConfig', param('sourecode.user.config.change_email'))
        ->bind('$activateConfig', param('sourecode.user.config.activate'))
        ->bind('$forgotPasswordConfig', param('sourecode.user.config.forgot_password'))
        ->call(
            'setContainer',
            [
                service('service_container'),
            ]
        );
};
