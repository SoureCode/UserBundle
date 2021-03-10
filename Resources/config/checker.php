<?php

use SoureCode\Bundle\User\Checker\UserChecker;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Checker
    // ============================
    $services->set('sourecode.user.checker', UserChecker::class)
        ->lazy();
};
