<?php

namespace SoureCode\Bundle\User\Service;

use DateTime;
use Doctrine\Persistence\ObjectManager;
use SoureCode\Bundle\Token\Service\TokenServiceInterface;
use SoureCode\Bundle\User\Exception\LogicException;
use SoureCode\Bundle\User\Exception\RuntimeException;
use SoureCode\Bundle\User\Repository\UserRepository;
use SoureCode\Bundle\User\ValueObject\TokenTypes;
use SoureCode\Component\Token\Model\TokenInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use SoureCode\Component\User\Model\CredentialHolderInterface;
use SoureCode\Component\User\Updater\CanonicalizeUserFieldsUpdaterInterface;
use SoureCode\Component\User\Updater\PasswordUpdaterInterface;
use Symfony\Component\Security\Core\Security;

/**
 * @template T as BasicUserInterface
 * @implements UserServiceInterface<T>
 */
class UserService implements UserServiceInterface
{
    /**
     * @var UserRepository<T>
     */
    protected UserRepository $repository;

    /**
     * @var class-string<T>
     */
    protected string $class;
    protected ObjectManager $manager;
    protected TokenServiceInterface $tokenService;
    protected UserMailer $mailer;
    protected PasswordUpdaterInterface $passwordUpdater;
    protected CanonicalizeUserFieldsUpdaterInterface $userFieldsUpdater;
    protected Security $security;

    /**
     * @param UserRepository<T> $repository
     * @param class-string<T>   $class
     */
    public function __construct(
        ObjectManager $manager,
        TokenServiceInterface $tokenService,
        UserMailer $mailer,
        UserRepository $repository,
        PasswordUpdaterInterface $passwordUpdater,
        CanonicalizeUserFieldsUpdaterInterface $userFieldsUpdater,
        Security $security,
        string $class,
    ) {
        $this->repository = $repository;
        $this->class = $class;
        $this->manager = $manager;
        $this->tokenService = $tokenService;
        $this->mailer = $mailer;
        $this->passwordUpdater = $passwordUpdater;
        $this->userFieldsUpdater = $userFieldsUpdater;
        $this->security = $security;
    }

    /**
     * {@inheritDoc}
     */
    public function register(BasicUserInterface $user): void
    {
        $this->save($user);

        $token = $this->tokenService->create($user, TokenTypes::REGISTER);

        $this->mailer->sendRegister($user, $token);
    }

    /**
     * {@inheritDoc}
     */
    public function save(mixed $user): void
    {
        $this->manager->persist($user);
        $this->manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function updatePassword(CredentialHolderInterface $credentialHolder): void
    {
        if (null !== $credentialHolder->getPlainPassword()) {
            $this->passwordUpdater->update($credentialHolder);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function activate(string $tokenValue): void
    {
        /**
         * @var TokenInterface $token
         */
        $token = $this->tokenService->findByValue($tokenValue);
        $this->tokenService->validate($token);
        $user = $this->findByToken($token);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $this->tokenService->remove($token);

        $user->setVerifiedAt(new DateTime());
        $user->enable();
        $this->save($user);

        $this->mailer->sendWelcome($user);
    }

    /**
     * {@inheritDoc}
     */
    public function findByToken(TokenInterface $token): ?BasicUserInterface
    {
        $this->tokenService->validate($token);

        if ($token->getResourceType() !== $this->class) {
            throw new LogicException('Invalid resource type in token.');
        }

        $id = $token->getResourceId();

        /**
         * @var ?T
         */
        $user = $this->get($id);

        if (!$user) {
            return null;
        }

        return $user;
    }

    /**
     * {@inheritDoc}
     */
    public function get(?int $id): ?BasicUserInterface
    {
        return $this->repository->find($id);
    }

    /**
     * {@inheritDoc}
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getRepository(): UserRepository
    {
        return $this->repository;
    }

    public function requestForgotPassword(string $email): void
    {
        $user = $this->findUserByEmail($email);

        if (!$user) {
            throw new RuntimeException('User not found');
        }

        // Start remove old token
        $token = $this->tokenService->findByResourceAndType($user, TokenTypes::FORGOT_PASSWORD);

        if ($token) {
            $this->manager->remove($token);
            $this->manager->flush();
        }
        // End remove old token

        $token = $this->tokenService->create($user, TokenTypes::FORGOT_PASSWORD);

        $this->mailer->sendForgotPasswordRequest($user, $token);
    }

    /**
     * {@inheritDoc}
     */
    public function findUserByEmail(string $email): ?BasicUserInterface
    {
        return $this->repository->findOneBy(['canonicalEmail' => $this->userFieldsUpdater->canonicalizeEmail($email)]);
    }

    public function changeEmail(string $email): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            throw new LogicException('Must be logged in to change email.');
        }

        if (!($user instanceof BasicUserInterface)) {
            throw new RuntimeException('Invalid user.');
        }

        // Start remove old token
        $token = $this->tokenService->findByResourceAndType($user, TokenTypes::CHANGE_EMAIL);

        if ($token) {
            $this->manager->remove($token);
            $this->manager->flush();
        }
        // Stop remove old token

        $token = $this->tokenService->create($user, TokenTypes::CHANGE_EMAIL);
        $token->setData($email);
        $this->tokenService->save($token);

        $this->mailer->sendChangeEmail($user, $token, $email);
    }

    public function verifyChangeEmail(string $tokenValue): void
    {
        /**
         * @var TokenInterface $token
         */
        $token = $this->tokenService->findByValue($tokenValue);
        $this->tokenService->validate($token);
        $user = $this->findByToken($token);

        if (!$user) {
            throw new RuntimeException('User not found.');
        }

        $user->setEmail($token->getData());
        $this->tokenService->remove($token);
        $this->save($user);
    }

    /**
     * {@inheritDoc}
     */
    public function remove(BasicUserInterface $user): void
    {
        $this->manager->remove($user);
        $this->manager->flush();
    }
}
