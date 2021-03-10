<?php

use SoureCode\Component\Common\Canonicalizer\Canonicalizer;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return function (ContainerConfigurator $container) {
    $services = $container->services();

    // ============================
    // = Canonicalizer
    // ============================
    $services->set('sourecode.user.canonicalizer.email', Canonicalizer::class)
        ->lazy();
};
