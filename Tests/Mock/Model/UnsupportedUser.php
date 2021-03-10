<?php

namespace SoureCode\Bundle\User\Tests\Mock\Model;

use Symfony\Component\Security\Core\User\UserInterface;

class UnsupportedUser implements UserInterface
{
    protected ?int $id = null;
    protected string $email = '';
    protected ?string $salt = null;
    protected ?string $password = null;
    protected array $roles = ['ROLE_USER'];
    protected ?string $plainPassword = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function getSalt(): ?string
    {
        return $this->salt;
    }

    public function getUsername(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        $this->plainPassword = null;
    }
}
