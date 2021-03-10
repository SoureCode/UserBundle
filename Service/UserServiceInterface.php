<?php

namespace SoureCode\Bundle\User\Service;

use SoureCode\Bundle\User\Form\Model\ChangeEmail;
use SoureCode\Bundle\User\Repository\UserRepository;
use SoureCode\Component\Token\Model\TokenInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use SoureCode\Component\User\Model\CredentialHolderInterface;

/**
 * @template T as BasicUserInterface
 */
interface UserServiceInterface
{
    /**
     * @return ?T
     */
    public function findUserByEmail(string $email): ?BasicUserInterface;

    public function get(?int $id): ?BasicUserInterface;

    /**
     * @param T $user
     */
    public function register(BasicUserInterface $user): void;

    /**
     * @param T $user
     */
    public function save(BasicUserInterface $user): void;

    public function updatePassword(CredentialHolderInterface $credentialHolder): void;

    /**
     * @return ?T
     */
    public function findByToken(TokenInterface $token): ?BasicUserInterface;

    public function activate(string $tokenValue): void;

    /**
     * @return UserRepository<T>
     */
    public function getRepository(): UserRepository;

    /**
     * @return class-string<T>
     */
    public function getClass(): string;

    public function requestForgotPassword(string $usernameOrEmail): void;

    public function changeEmail(string $email): void;

    public function verifyChangeEmail(string $tokenValue): void;

    /**
     * @param T $user
     */
    public function remove(BasicUserInterface $user): void;
}
