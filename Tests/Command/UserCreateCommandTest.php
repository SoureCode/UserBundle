<?php

namespace SoureCode\Bundle\User\Tests\Command;

use SoureCode\Bundle\User\Command\UserCreateCommand;
use SoureCode\Bundle\User\Tests\AbstractUserTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Validator\Constraints\Email;

class UserCreateCommandTest extends AbstractUserTestCase
{
    public function testExecuteValidation(): void
    {
        $kernel = static::bootKernel();
        $application = new Application($kernel);

        $command = $application->find(UserCreateCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'email' => 'foo', // not an email
            'password' => 'fooBAR123!&_-',
        ]);

        $output = $commandTester->getDisplay();

        self::assertStringContainsString(Email::INVALID_FORMAT_ERROR, $output);
    }

    public function testExecute(): void
    {
        // Arrange
        $kernel = static::bootKernel();
        $application = new Application($kernel);
        $command = $application->find(UserCreateCommand::getDefaultName());
        $commandTester = new CommandTester($command);
        $service = $this->getService();

        // Act
        $commandTester->execute([
            'email' => 'foo@bar.com',
            'password' => 'fooBAR123!&_-',
        ]);

        // Assert
        $output = $commandTester->getDisplay();

        self::assertStringContainsString('Successfully created user.', $output);
        self::assertNotNull($service->findUserByEmail('foo@bar.com'));
    }
}
