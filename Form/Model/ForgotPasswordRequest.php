<?php

namespace SoureCode\Bundle\User\Form\Model;

class ForgotPasswordRequest
{
    protected ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }
}
