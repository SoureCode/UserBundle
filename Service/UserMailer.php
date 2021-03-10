<?php

namespace SoureCode\Bundle\User\Service;

use SoureCode\Bundle\Token\Service\TokenServiceInterface;
use SoureCode\Component\Token\Model\TokenInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserMailer
{
    public function __construct(
        protected MailerInterface $mailer,
        protected TokenServiceInterface $tokenService,
        protected TranslatorInterface $translator,
    ) {
    }

    public function sendChangeEmail(BasicUserInterface $user, TokenInterface $token, string $email): void
    {
        $templatedEmail = new TemplatedEmail();
        $templatedEmail->htmlTemplate('@SoureCodeUser/Email/change-email.html.twig');

        $templatedEmail->to(new Address($email, $user->getUsername()));
        $templatedEmail->subject($this->translator->trans('sourecode.user.email.change_email.title', ['%name%' => $user->getUsername()]));

        $templatedEmail->context(
            [
                'user' => $user,
                'token' => $token,
                'expires_at' => $this->tokenService->getExpiresAt($token),
            ]
        );

        $this->mailer->send($templatedEmail);
    }

    public function sendForgotPasswordRequest(BasicUserInterface $user, TokenInterface $token): void
    {
        $templatedEmail = new TemplatedEmail();
        $templatedEmail->htmlTemplate('@SoureCodeUser/Email/forgot-password.html.twig');

        /**
         * @var string $email
         */
        $email = $user->getEmail();
        $username = $user->getUsername();

        $templatedEmail->to(new Address($email, $username));
        $templatedEmail->subject($this->translator->trans('sourecode.user.email.forgot_password.title', ['%name%' => $user->getUsername()]));

        $templatedEmail->context(
            [
                'user' => $user,
                'token' => $token,
                'expires_at' => $this->tokenService->getExpiresAt($token),
            ]
        );

        $this->mailer->send($templatedEmail);
    }

    public function sendWelcome(BasicUserInterface $user): void
    {
        $templatedEmail = new TemplatedEmail();
        $templatedEmail->htmlTemplate('@SoureCodeUser/Email/user-activated.html.twig');

        /**
         * @var string $email
         */
        $email = $user->getEmail();
        $username = $user->getUsername();

        $templatedEmail->to(new Address($email, $username));
        $templatedEmail->subject($this->translator->trans('sourecode.user.email.activated.title', ['%name%' => $user->getUsername()]));

        $templatedEmail->context(
            [
                'user' => $user,
            ]
        );

        $this->mailer->send($templatedEmail);
    }

    public function sendRegister(BasicUserInterface $user, TokenInterface $token): void
    {
        $templatedEmail = new TemplatedEmail();
        $templatedEmail->htmlTemplate('@SoureCodeUser/Email/user-signed-up.html.twig');

        /**
         * @var string $email
         */
        $email = $user->getEmail();
        $username = $user->getUsername();

        $templatedEmail->to(new Address($email, $username));
        $templatedEmail->subject($this->translator->trans('sourecode.user.email.signed_up.title', ['%name%' => $user->getUsername()]));

        $templatedEmail->context(
            [
                'expires_at' => $this->tokenService->getExpiresAt($token),
                'token' => $token,
                'user' => $user,
            ]
        );

        $this->mailer->send($templatedEmail);
    }
}
