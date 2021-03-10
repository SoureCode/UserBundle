<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes) {
    $routes->add('sourecode_user_register', '/register')
        ->methods(['GET', 'POST'])
        ->controller('sourecode.user.controller.security::registerAction');

    $routes->add('sourecode_user_activate', '/activate/{token}')
        ->controller('sourecode.user.controller.security::activateAction')
        ->methods(['GET']);

    $routes->add('sourecode_user_login', '/login')
        ->methods(['GET', 'POST'])
        ->controller('sourecode.user.controller.security::loginAction');

    $routes->add('sourecode_user_logout', '/logout')
        ->controller('sourecode.user.controller.security::logoutAction')
        ->methods(['GET']);

    $routes->add('sourecode_user_change_password', '/change_password')
        ->controller('sourecode.user.controller.security::changePasswordAction')
        ->methods(['GET', 'POST']);

    $routes->add('sourecode_user_change_email', '/change_email')
        ->controller('sourecode.user.controller.security::changeEmailAction')
        ->methods(['GET', 'POST']);

    $routes->add('sourecode_user_change_email_verify', '/change_email/{token}')
        ->controller('sourecode.user.controller.security::changeEmailVerifyAction')
        ->methods(['GET', 'POST']);

    $routes->add('sourecode_user_forgot_password_request', '/forgot_password')
        ->controller('sourecode.user.controller.security::forgotPasswordRequestAction')
        ->methods(['GET', 'POST']);

    $routes->add('sourecode_user_forgot_password_change', '/forgot_password/{token}')
        ->controller('sourecode.user.controller.security::forgotPasswordChangeAction')
        ->methods(['GET', 'POST']);
};
