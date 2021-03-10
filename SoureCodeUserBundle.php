<?php

namespace SoureCode\Bundle\User;

use Doctrine\Bundle\DoctrineBundle\DependencyInjection\Compiler\DoctrineOrmMappingsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class SoureCodeUserBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $this->addRegisterMappingsPass($container);
    }

    private function addRegisterMappingsPass(ContainerBuilder $container): void
    {
        $basicMapping = [
            __DIR__.'/Resources/config/doctrine/basic' => 'SoureCode\Component\User\Model\Basic',
        ];

        $advancedMapping = [
            __DIR__.'/Resources/config/doctrine/advanced' => 'SoureCode\Component\User\Model\Advanced',
        ];

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                $basicMapping,
                ['sourecode.user.model_manager_name'],
                'sourecode.user.user_type_basic'
            )
        );

        $container->addCompilerPass(
            DoctrineOrmMappingsPass::createXmlMappingDriver(
                $advancedMapping,
                ['sourecode.user.model_manager_name'],
                'sourecode.user.user_type_advanced'
            )
        );
    }
}
