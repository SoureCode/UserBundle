<?php

namespace SoureCode\Bundle\User\Provider;

use function get_class;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class UserProvider implements UserProviderInterface
{
    /**
     * @var class-string<BasicUserInterface>
     */
    protected string $class;

    protected UserServiceInterface $service;

    /**
     * @param class-string<BasicUserInterface> $class
     */
    public function __construct(UserServiceInterface $service, string $class)
    {
        $this->class = $class;
        $this->service = $service;
    }

    /**
     * {@inheritDoc}
     */
    public function loadUserByUsername(string $username): UserInterface
    {
        $user = $this->service->findUserByEmail($username);

        if (!$user) {
            throw new UsernameNotFoundException(sprintf('User with email "%s" does not exist.', $username));
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function refreshUser(UserInterface $user): BasicUserInterface
    {
        // serves only as a type guard here
        if (!$user instanceof BasicUserInterface) {
            throw new UnsupportedUserException(sprintf('Expected an instance of "%s", but got "%s".', BasicUserInterface::class, get_class($user)));
        }

        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Expected an instance of "%s", but got "%s".', $this->class, get_class($user)));
        }

        $id = $user->getId();
        $reloadedUser = $this->service->get($id);

        if (null === $reloadedUser) {
            throw new UsernameNotFoundException(sprintf('User with ID "%s" could not be reloaded.', $id ?? ''));
        }

        return $reloadedUser;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsClass(string $class): bool
    {
        return $this->class === $class || is_subclass_of($class, $this->class);
    }
}
