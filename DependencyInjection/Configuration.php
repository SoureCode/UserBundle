<?php

namespace SoureCode\Bundle\User\DependencyInjection;

use ReflectionClass;
use SoureCode\Bundle\User\Form\Type\ChangeEmailType;
use SoureCode\Bundle\User\Form\Type\ChangePasswordType;
use SoureCode\Bundle\User\Form\Type\ForgotPasswordType;
use SoureCode\Bundle\User\Form\Type\UserLoginType;
use SoureCode\Bundle\User\Form\Type\UserRegisterType;
use SoureCode\Component\User\Model\Basic\BasicUser;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('soure_code_user');

        /**
         * @var ArrayNodeDefinition $rootNode
         */
        $rootNode = $treeBuilder->getRootNode();

        // @formatter:off
        $classNode = $rootNode->children()->scalarNode('class');

        $classNode
            ->defaultValue(BasicUser::class)
            ->cannotBeEmpty()
            ->validate()
                ->ifTrue(function (string $value) {
                    /** @var class-string $value */
                    $reflection = new ReflectionClass($value);

                    return !$reflection->implementsInterface(BasicUserInterface::class);
                })
                ->thenInvalid(sprintf('Invalid user class. User class musst be implements at least "%s".', BasicUserInterface::class))
            ->end()
        ;

        $rootNode
            ->children()
            ->scalarNode('model_manager_name')
            ->defaultNull()
        ;

        $this->addRegisterSection($rootNode);
        $this->addLoginSection($rootNode);
        $this->addChangePasswordSection($rootNode);
        $this->addChangeEmailSection($rootNode);
        $this->addForgotPasswordSection($rootNode);
        $this->addActivateSection($rootNode);
        // @formatter:on

        return $treeBuilder;
    }

    private function addRegisterSection(ArrayNodeDefinition $rootNode): void
    {
        // @formatter:off
        $node = $rootNode
            ->children()
            ->arrayNode('register')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('form')
            ->defaultValue(UserRegisterType::class)
        ;

        $node->children()
            ->scalarNode('success_route')
            ->isRequired()
        ;
        // @formatter:on
    }

    private function addChangeEmailSection(ArrayNodeDefinition $rootNode): void
    {
        // @formatter:off
        $node = $rootNode
            ->children()
            ->arrayNode('change_email')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('form')
            ->defaultValue(ChangeEmailType::class)
        ;

        $node->children()
            ->scalarNode('request_success_route')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('change_success_route')
            ->isRequired()
        ;

        // @formatter:on
    }

    private function addActivateSection(ArrayNodeDefinition $rootNode): void
    {
        // @formatter:off
        $node = $rootNode
            ->children()
            ->arrayNode('activate')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('success_route')
            ->isRequired()
        ;
        // @formatter:on
    }

    private function addForgotPasswordSection(ArrayNodeDefinition $rootNode): void
    {
        // @formatter:off
        $node = $rootNode
            ->children()
            ->arrayNode('forgot_password')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('request_form')
            ->defaultValue(ForgotPasswordType::class)
        ;

        $node->children()
            ->scalarNode('change_form')
            ->defaultValue(ChangePasswordType::class)
        ;

        $node->children()
            ->scalarNode('logged_in_route')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('request_success_route')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('changed_route')
            ->isRequired()
        ;
        // @formatter:on
    }

    private function addLoginSection(ArrayNodeDefinition $rootNode): void
    {
        // @formatter:off
        $node = $rootNode
            ->children()
            ->arrayNode('login')
            ;

        $node->children()
            ->scalarNode('form')
            ->defaultValue(UserLoginType::class)
        ;

        $node->children()
            ->scalarNode('logged_in_route')
            ->defaultNull()
        ;
        // @formatter:on
    }

    private function addChangePasswordSection(ArrayNodeDefinition $rootNode): void
    {
        // @formatter:off
        $node = $rootNode
            ->children()
            ->arrayNode('change_password')
            ->isRequired()
        ;

        $node->children()
            ->scalarNode('form')
            ->defaultValue(ChangePasswordType::class)
        ;

        $node->children()
            ->scalarNode('success_route')
            ->isRequired()
        ;
        // @formatter:on
    }
}
