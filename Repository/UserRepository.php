<?php

namespace SoureCode\Bundle\User\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use function get_class;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @template T as BasicUserInterface
 *
 * @extends ServiceEntityRepository<T>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    /**
     * @param class-string<T> $userClass
     */
    public function __construct(ManagerRegistry $registry, string $userClass)
    {
        parent::__construct($registry, $userClass);
    }

    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof BasicUserInterface) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->_em->persist($user);
        $this->_em->flush();
    }
}
