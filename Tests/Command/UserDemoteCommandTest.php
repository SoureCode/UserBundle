<?php

namespace SoureCode\Bundle\User\Tests\Command;

use SoureCode\Bundle\User\Command\UserDemoteCommand;
use SoureCode\Bundle\User\Tests\AbstractUserTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class UserDemoteCommandTest extends AbstractUserTestCase
{
    public function testExecuteInvalidRole(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(UserDemoteCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $this->addTestUser();

        // Act
        $commandTester->execute(
            [
                'email' => 'foo@bar.com',
                'role' => 'bar',
            ]
        );

        // Assert
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Invalid role', $output);
    }

    public function testExecuteRoleUser(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(UserDemoteCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $this->addTestUser();

        // Act
        $commandTester->execute(
            [
                'email' => 'foo@bar.com',
                'role' => 'ROLE_USER',
            ]
        );

        // Assert
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Can not remove role', $output);
    }

    public function testExecuteUserNotFound(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(UserDemoteCommand::getDefaultName());
        $commandTester = new CommandTester($command);

        // Act
        $commandTester->execute(
            [
                'email' => 'foo@bar.com',
                'role' => 'ROLE_ADMIN',
            ]
        );

        // Assert
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Could not found user', $output);
    }

    public function testExecuteHasNotRole(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(UserDemoteCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $this->addTestUser();

        // Act
        $commandTester->execute(
            [
                'email' => 'foo@bar.com',
                'role' => 'ROLE_ADMIN',
            ]
        );

        // Assert
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('does not have role', $output);
    }

    public function testExecute(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(UserDemoteCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $user = $this->addTestUser();
        $user->addRole('ROLE_ADMIN');
        $this->getService()->save($user);

        // Act
        $commandTester->execute(
            [
                'email' => 'foo@bar.com',
                'role' => 'ROLE_ADMIN',
            ]
        );

        // Assert
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Successfully removed role', $output);
    }
}
