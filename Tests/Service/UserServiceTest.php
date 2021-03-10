<?php

namespace SoureCode\Bundle\User\Tests\Service;

use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Registry;
use SoureCode\Bundle\Token\Exception\RuntimeException as TokenRuntimeException;
use SoureCode\Bundle\User\Exception\LogicException;
use SoureCode\Bundle\User\Exception\RuntimeException;
use SoureCode\Bundle\User\Repository\UserRepository;
use SoureCode\Bundle\User\Tests\AbstractUserTestCase;
use SoureCode\Bundle\User\Tests\Mock\Entity\FooUser;
use SoureCode\Bundle\User\Tests\Mock\Model\UnsupportedUser;
use SoureCode\Bundle\User\ValueObject\TokenTypes;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;

class UserServiceTest extends AbstractUserTestCase
{
    public function testVerifyChangeEmailTokenNotFound(): void
    {
        // Assert
        $this->expectException(TokenRuntimeException::class);

        // Arrange
        static::bootKernel();
        $service = $this->getService();

        // Act
        $service->verifyChangeEmail('invalid_token');
    }

    public function testVerifyChangeEmailTokenExpired(): void
    {
        // Assert
        $this->expectException(TokenRuntimeException::class);

        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();
        $this->login($user);
        $service->changeEmail('test@bar.com');
        $token = $this->getTokenService()->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);

        self::assertNotNull($token);

        $token->setCreatedAt((new DateTime())->sub(new DateInterval('PT10H')));
        $this->getTokenService()->save($token);

        // Act
        /**
         * @var string $tokenValue
         */
        $tokenValue = $token->getValue();
        $service->verifyChangeEmail($tokenValue);
    }

    public function testVerifyChangeEmailUserNotFound(): void
    {
        // Assert
        $this->expectException(RuntimeException::class);

        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();
        $this->login($user);
        $service->changeEmail('test@bar.com');
        $token = $this->getTokenService()->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);

        self::assertNotNull($token);
        $service->remove($user);
        // Recreate token
        $this->getTokenService()->save($token);

        // Act
        /**
         * @var string $tokenValue
         */
        $tokenValue = $token->getValue();
        $service->verifyChangeEmail($tokenValue);
    }

    public function testVerifyChangeEmail(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();
        $this->login($user);
        $service->changeEmail('test@bar.com');
        $token = $this->getTokenService()->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);

        self::assertNotNull($token);
        /**
         * @var string $tokenValue
         */
        $tokenValue = $token->getValue();

        // Act
        $service->verifyChangeEmail($tokenValue);

        // Assert
        $token = $this->getTokenService()->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);
        self::assertNull($token);
        self::assertSame('test@bar.com', $user->getEmail());
    }

    public function testRemove(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();

        // Act
        $service->remove($user);

        // Assert
        static::assertNull($service->findUserByEmail('foo@bar.com'));
    }

    public function testFindByToken(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $tokenService = $this->getTokenService();
        $user = $this->addTestUser();
        $token = $tokenService->create($user, 'test');

        // Act
        /**
         * @var BasicUserInterface $foundUser
         */
        $foundUser = $service->findByToken($token);

        // Assert
        static::assertNotNull($foundUser);
        /**
         * @var int $foundId
         */
        $foundId = $foundUser->getId();
        /**
         * @var int $id
         */
        $id = $user->getId();
        static::assertSame($id, $foundId);
    }

    public function testRequestForgotPassword(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();

        // Act
        $service->requestForgotPassword('foo@bar.com');

        // Assert
        static::assertNotNull($this->getTokenService()->findByResourceAndType($user, TokenTypes::FORGOT_PASSWORD));
        static::assertEmailCount(1);
        static::assertQueuedEmailCount(1);
    }

    public function testChangeEmailNotLoggedIn(): void
    {
        // Assert
        $this->expectException(LogicException::class);

        // Arrange
        static::bootKernel();
        $service = $this->getService();

        // Act
        $service->changeEmail('test@bar.com');
    }

    public function testChangeEmailInvalidUser(): void
    {
        // Assert
        $this->expectException(RuntimeException::class);

        // Arrange
        static::bootKernel();
        $this->login(new UnsupportedUser());
        $service = $this->getService();

        // Act
        $service->changeEmail('test@bar.com');
    }

    public function testChangeEmail(): void
    {
        // Arrange
        static::bootKernel();
        $user = $this->addTestUser();
        $this->login($user);

        $tokenService = $this->getTokenService();
        $tokenService->create($user, TokenTypes::CHANGE_EMAIL);

        $oldToken = $tokenService->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);
        self::assertNotNull($oldToken);

        // Act
        $service = $this->getService();
        $service->changeEmail('test@bar.com');

        // Assert
        $newToken = $tokenService->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);
        self::assertNotNull($newToken, 'New token was created');
        /**
         * @var string $oldTokenValue
         */
        $oldTokenValue = $oldToken->getValue();
        self::assertNull($tokenService->findByValue($oldTokenValue), 'Old token was removed');
        self::assertEmailCount(1);
        self::assertQueuedEmailCount(1);
    }

    public function testSaveAndRemove(): void
    {
        // Arrange
        $container = static::bootKernel()->getContainer();
        $service = $this->getService();
        $user = new FooUser();
        $user->setEmail('bar@test.com');
        $user->setPlainPassword('test');
        $service->updatePassword($user);
        /**
         * @var Registry $doctrine
         */
        $doctrine = $container->get('doctrine');
        $manager = $doctrine->getManager();

        // Act
        $service->save($user);

        // Assert
        self::assertTrue($manager->contains($user));
        self::assertNotNull($user->getId());

        // Act
        $service->remove($user);

        // Assert
        self::assertFalse($manager->contains($user));
        self::assertNull($service->findUserByEmail('bar@test.com'));
    }

    public function testActivate(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $tokenService = $this->getTokenService();
        $user = $this->addTestUser();
        $token = $tokenService->create($user, TokenTypes::REGISTER);

        /**
         * @var string $value
         */
        $value = $token->getValue();

        self::assertNull($user->getVerifiedAt());
        self::assertFalse($user->isEnabled());

        // Act
        $service->activate($value);

        // Assert
        self::assertNull($tokenService->findByValue($value));
        self::assertNotNull($user->getVerifiedAt());
        self::assertTrue($user->isEnabled());
        self::assertEmailCount(1);
        self::assertQueuedEmailCount(1);
    }

    public function testGet(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();

        // Act
        $actual = $service->get($user->getId());

        // Assert
        self::assertSame($user, $actual);
    }

    public function testUpdatePassword(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();

        $previousPassword = $user->getPassword();

        self::assertNotNull($previousPassword);
        self::assertNull($user->getPlainPassword());

        $user->setPlainPassword('bar');
        self::assertNotNull($user->getPlainPassword());

        // Act
        $service->updatePassword($user);

        // Assert
        self::assertNotNull($user->getPassword());
        self::assertNull($user->getPlainPassword());
        self::assertNotSame($previousPassword, $user->getPassword());
    }

    public function testRegister(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $tokenService = $this->getTokenService();
        $user = new FooUser();
        $user->setEmail('foo@bar.com');
        $user->setPlainPassword('foo');

        // Act
        $service->register($user);

        // Assert
        static::assertNull($user->getPlainPassword());
        static::assertNotNull($user->getPassword());
        static::assertNotNull($tokenService->findByResourceAndType($user, TokenTypes::REGISTER));
        static::assertQueuedEmailCount(1);
        static::assertEmailCount(1);
    }

    public function testGetClass(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();

        // Act
        $actual = $service->getClass();

        // Assert
        self::assertSame(FooUser::class, $actual);
    }

    public function testGetRepository(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();

        // Act
        $repository = $service->getRepository();

        // Assert
        self::assertInstanceOf(UserRepository::class, $repository);
    }

    public function testFindUserByEmail(): void
    {
        // Arrange
        static::bootKernel();
        $service = $this->getService();
        $user = $this->addTestUser();

        // Act
        $actual = $service->findUserByEmail('foo@bar.com');

        // Assert
        self::assertSame($user, $actual);
    }
}
