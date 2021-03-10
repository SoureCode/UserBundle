<?php

namespace SoureCode\Bundle\User\Tests;

use SoureCode\Bundle\Common\SoureCodeCommonBundle;
use SoureCode\Bundle\Token\Service\TokenServiceInterface;
use SoureCode\Bundle\Token\SoureCodeTokenBundle;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use SoureCode\Bundle\User\SoureCodeUserBundle;
use SoureCode\Bundle\User\Tests\Mock\Entity\FooUser;
use SoureCode\Bundle\User\ValueObject\TokenTypes;
use SoureCode\BundleTest\Configurator\KernelConfigurator;
use SoureCode\BundleTest\TestCase\AbstractWebTestCase;
use SoureCode\BundleTest\TestCase\DoctrineSetupTrait;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Twig\Extra\TwigExtraBundle\TwigExtraBundle;

abstract class AbstractUserTestCase extends AbstractWebTestCase
{
    use DoctrineSetupTrait;

    protected static function getKernelConfigurator(): KernelConfigurator
    {
        $configurator = parent::getKernelConfigurator();

        $configurator->addRouteFile(__DIR__.'/../Resources/config/routing.php');
        $configurator->setBundle(TwigBundle::class);
        $configurator->setBundle(TwigExtraBundle::class);
        $configurator->extend(
            FrameworkBundle::class,
            [
                'test' => true,
                'translator' => [
                    'default_path' => __DIR__.'/Mock/translations',
                ],
                'session' => [
                    'storage_id' => 'session.storage.mock_file',
                    'cookie_secure' => 'auto',
                    'cookie_samesite' => 'lax',
                ],
                'profiler' => [
                    'collect' => false,
                ],
                'validation' => [
                    'not_compromised_password' => false,
                ],
                'mailer' => [
                    'dsn' => '%env(MAILER_DSN)%',
                    'envelope' => [
                        'sender' => 'test@test.com',
                    ],
                    'headers' => [
                        'from' => 'FooBar <test@test.com>',
                    ],
                ],
            ]
        );
        $configurator->setBundle(
            SecurityBundle::class,
            'security',
            [
                'encoders' => [
                    FooUser::class => [
                        'algorithm' => 'auto',
                    ],
                ],
                'providers' => [
                    'sourecode_user_provider' => [
                        'id' => 'sourecode.user.provider',
                    ],
                ],
                'firewalls' => [
                    'main' => [
                        'anonymous' => true,
                        'user_checker' => 'sourecode.user.checker',
                    ],
                ],
            ]
        );
        $configurator->setBundle(SoureCodeCommonBundle::class);
        $configurator->setBundle(
            SoureCodeTokenBundle::class,
            'soure_code_token',
            [
                'tokens' => [
                    'foo' => [
                        'expiration' => 'PT1H',
                        'length' => 6,
                    ],
                    'bar' => [
                        'expiration' => 'PT4H',
                        'length' => 10,
                    ],
                    'test' => [
                        'expiration' => 'PT4H',
                        'length' => 10,
                    ],
                    TokenTypes::CHANGE_EMAIL => [
                        'expiration' => 'PT4H',
                        'length' => 10,
                    ],
                    TokenTypes::FORGOT_PASSWORD => [
                        'expiration' => 'PT4H',
                        'length' => 10,
                    ],
                    TokenTypes::REGISTER => [
                        'expiration' => 'PT4H',
                        'length' => 10,
                    ],
                ],
            ]
        );

        $configurator->setBundle(
            SoureCodeUserBundle::class,
            'soure_code_user',
            [
                'class' => FooUser::class,
                'register' => [
                    'success_route' => 'app_name',
                ],
                'login' => [
                    'logged_in_route' => 'app_name',
                ],
                'change_password' => [
                    'success_route' => 'app_name',
                ],
                'activate' => [
                    'success_route' => 'app_name',
                ],
                'forgot_password' => [
                    'logged_in_route' => 'app_name',
                    'request_success_route' => 'app_name',
                    'changed_route' => 'app_name',
                ],
                'change_email' => [
                    'request_success_route' => 'app_name',
                    'change_success_route' => 'app_name',
                ],
            ]
        );

        return $configurator;
    }

    protected static function bootKernel(array $options = [])
    {
        $kernel = parent::bootKernel($options);

        static::prepareDatabase();

        return $kernel;
    }

    protected function getTokenService(): TokenServiceInterface
    {
        $container = static::$kernel->getContainer();

        /**
         * @var TokenServiceInterface $service
         */
        $service = $container->get(TokenServiceInterface::class);

        return $service;
    }

    protected function login(UserInterface $user): void
    {
        $token = new UsernamePasswordToken($user, $user->getPassword(), 'main', $user->getRoles());
        /**
         * @var TokenStorageInterface $tokenStorage
         */
        $tokenStorage = static::$kernel->getContainer()->get('security.token_storage');
        /**
         * @var EventDispatcherInterface $eventDispatcher
         */
        $eventDispatcher = static::$kernel->getContainer()->get('event_dispatcher');
        $tokenStorage->setToken($token);
        $event = new InteractiveLoginEvent(new Request(), $token);

        $eventDispatcher->dispatch($event, SecurityEvents::INTERACTIVE_LOGIN);
    }

    protected function addTestUser(): BasicUserInterface
    {
        $service = $this->getService();

        $user = new FooUser();
        $user->setEmail('foo@bar.com');
        $user->setPlainPassword('foo');

        $service->updatePassword($user);

        $service->save($user);

        return $user;
    }

    protected function getService(): UserServiceInterface
    {
        $container = static::$kernel->getContainer();
        /**
         * @var UserServiceInterface $service
         */
        $service = $container->get(UserServiceInterface::class);

        return $service;
    }

    protected function setUp(): void
    {
        $this->setupDoctrine();

        parent::setUp();
    }

    protected function tearDown(): void
    {
        static::clearDatabase();

        parent::tearDown();
    }

    protected function getDoctrineMappings(): array
    {
        return [
            'SoureCodeTokenBundle' => [
                'prefix' => 'SoureCode\Component\Token\Model',
                'type' => 'xml',
            ],
            'SoureCodeUserBundle' => [
                'prefix' => 'SoureCode\Component\User\Model\Advanced',
                'type' => 'xml',
            ],
            'SoureCodeUserTest' => [
                'type' => 'annotation',
                'dir' => __DIR__.'/Mock/Entity',
                'prefix' => 'SoureCode\Bundle\User\Tests\Mock\Entity',
                'is_bundle' => false,
            ],
        ];
    }

    protected function getDoctrineMigrations(): array
    {
        return [
            'SoureCode\Bundle\Token\Migrations' => __DIR__.'/../vendor/sourecode/token-bundle/Migrations',
            'SoureCode\Bundle\User\Tests\Mock\Migrations' => __DIR__.'/Mock/Migrations',
        ];
    }
}
