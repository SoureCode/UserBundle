<?php

namespace SoureCode\Bundle\User\DependencyInjection;

use SoureCode\Component\User\Model\Advanced\AdvancedUserInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class SoureCodeUserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->populateConfiguration($container, $config);

        if (is_subclass_of($config['class'], AdvancedUserInterface::class)) {
            $container->setParameter('sourecode.user.user_type_basic', true);
            $container->setParameter('sourecode.user.user_type_advanced', true);
        } elseif (is_subclass_of($config['class'], BasicUserInterface::class)) {
            $container->setParameter('sourecode.user.user_type_basic', true);
        }

        $loader->load('doctrine.php');

        $container->setAlias('sourecode.user.doctrine_registry', new Alias('doctrine', false));

        $definition = $container->getDefinition('sourecode.user.object_manager');
        $definition->setFactory(
            [
                new Reference('sourecode.user.doctrine_registry'),
                'getManager',
            ]
        );

        $loader->load('canonicalizer.php');
        $loader->load('checker.php');
        $loader->load('command.php');
        $loader->load('controller.php');
        $loader->load('encoder.php');
        $loader->load('event_subscriber.php');
        $loader->load('form.php');
        $loader->load('provider.php');
        $loader->load('services.php');
        $loader->load('updater.php');
    }

    private function populateConfiguration(ContainerBuilder $container, array $config): void
    {
        foreach ($config as $key => $value) {
            $container->setParameter(sprintf('sourecode.user.config.%s', $key), $value);
        }
    }
}
