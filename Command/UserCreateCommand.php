<?php

namespace SoureCode\Bundle\User\Command;

use DateTime;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserCreateCommand extends Command
{
    protected static $defaultName = 'sourecode:user:create';

    protected UserServiceInterface $service;

    protected ValidatorInterface $validator;

    public function __construct(UserServiceInterface $service, ValidatorInterface $validator)
    {
        $this->service = $service;
        $this->validator = $validator;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Creates a new user.')
            ->setHelp('This command allows you to create a new user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('password', InputArgument::REQUIRED, 'The password of the user');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $password
         */
        $password = $input->getArgument('password');
        /**
         * @var string $email
         */
        $email = $input->getArgument('email');
        $class = $this->service->getClass();
        $user = new ($class)();

        $user->setPlainPassword($password);
        $user->setEmail($email);
        $user->setVerifiedAt(new DateTime());
        $user->enable();

        /**
         * @var ConstraintViolationList $violations
         */
        $violations = $this->validator->validate($user);

        if ($violations->count() > 0) {
            $io->error((string) $violations);

            return Command::FAILURE;
        }

        $this->service->save($user);

        $io->success('Successfully created user.');

        return Command::SUCCESS;
    }
}
