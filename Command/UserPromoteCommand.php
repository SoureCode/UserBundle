<?php

namespace SoureCode\Bundle\User\Command;

use SoureCode\Bundle\User\Service\UserServiceInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use function Symfony\Component\String\u;

class UserPromoteCommand extends Command
{
    protected static $defaultName = 'sourecode:user:promote';

    protected UserServiceInterface $service;

    public function __construct(UserServiceInterface $service)
    {
        $this->service = $service;

        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Promotes a user.')
            ->setHelp('This command allows you to add a role to a user.')
            ->addArgument('email', InputArgument::REQUIRED, 'The email of the user.')
            ->addArgument('role', InputArgument::REQUIRED, 'The role');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        /**
         * @var string $email
         */
        $email = $input->getArgument('email');

        /**
         * @var string $roleInput
         */
        $roleInput = $input->getArgument('role');
        $role = u($roleInput)->upper();

        if (!$role->startsWith('ROLE_')) {
            $io->error(sprintf('Invalid role "%s".', $role->toString()));

            return Command::FAILURE;
        }

        if ($role->equalsTo('ROLE_USER')) {
            $io->error(sprintf('Can not add role "%s".', $role->toString()));

            return Command::FAILURE;
        }

        $user = $this->service->findUserByEmail($email);

        if (!$user) {
            $io->error(sprintf('Could not found user by input "%s".', $email));

            return Command::FAILURE;
        }

        if ($user->hasRole($role->toString())) {
            $io->info(sprintf('User "%s" already has role "%s".', $user->getUsername(), $role->toString()));

            return Command::SUCCESS;
        }

        $user->addRole($role->toString());
        $this->service->save($user);

        $io->success(sprintf('Successfully added role "%s" to user "%s".', $role->toString(), $user->getUsername()));

        return Command::SUCCESS;
    }
}
