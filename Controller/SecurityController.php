<?php

namespace SoureCode\Bundle\User\Controller;

use SoureCode\Bundle\Token\Service\TokenServiceInterface;
use SoureCode\Bundle\User\Exception\RuntimeException;
use SoureCode\Bundle\User\Form\Model\ChangeEmail;
use SoureCode\Bundle\User\Form\Model\ForgotPasswordRequest;
use SoureCode\Bundle\User\Service\UserServiceInterface;
use SoureCode\Component\Token\Model\TokenInterface;
use SoureCode\Component\User\Model\Basic\BasicUserInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        protected AuthenticationUtils $authenticationUtils,
        protected AuthorizationCheckerInterface $authorizationChecker,
        protected RouterInterface $router,
        protected UserServiceInterface $service,
        protected TokenServiceInterface $tokenService,
    ) {
    }

    public function loginAction(array $loginConfig): Response
    {
        $route = $loginConfig['logged_in_route'];
        $formType = $loginConfig['form'];

        if ($route && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute($route);
        }

        $lastError = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        $form = $this->createForm($formType);

        return $this->render(
            '@SoureCodeUser/Security/login.html.twig',
            [
                'form' => $form->createView(),
                'last_username' => $lastUsername,
                'last_error' => $lastError,
            ]
        );
    }

    public function registerAction(Request $request, array $registerConfig): Response
    {
        $formType = $registerConfig['form'];
        $user = new ($this->service->getClass())();
        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->register($user);

            $this->addFlash('success', 'sourecode.user.flash.registered');

            return $this->redirectToRoute($registerConfig['success_route']);
        }

        return $this->render(
            '@SoureCodeUser/Security/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function changePasswordAction(Request $request, array $changePasswordConfig): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $formType = $changePasswordConfig['form'];
        /**
         * @var BasicUserInterface $user
         */
        $user = $this->getUser();
        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->updatePassword($user);
            $this->service->save($user);

            $this->addFlash('success', 'sourecode.user.flash.password_changed');

            return $this->redirectToRoute($changePasswordConfig['success_route']);
        }

        return $this->render(
            '@SoureCodeUser/Security/change_password.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function changeEmailAction(Request $request, array $changeEmailConfig): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        $formType = $changeEmailConfig['form'];
        $data = new ChangeEmail();
        $form = $this->createForm($formType, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var string $email
             */
            $email = $data->getEmail();
            $this->service->changeEmail($email);

            $this->addFlash('success', 'sourecode.user.flash.change_email_requested');

            return $this->redirectToRoute($changeEmailConfig['request_success_route']);
        }

        return $this->render(
            '@SoureCodeUser/Security/change_email.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function forgotPasswordRequestAction(Request $request, array $forgotPasswordConfig): Response
    {
        $route = $forgotPasswordConfig['logged_in_route'];

        if ($route && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse($this->router->generate($route));
        }

        $formType = $forgotPasswordConfig['request_form'];
        $data = new ForgotPasswordRequest();
        $form = $this->createForm($formType, $data);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /**
             * @var string $email
             */
            $email = $data->getEmail();
            $this->service->requestForgotPassword($email);

            $this->addFlash('success', 'sourecode.user.flash.forgot_password_requested');

            return $this->redirectToRoute($forgotPasswordConfig['request_success_route']);
        }

        return $this->render(
            '@SoureCodeUser/Security/forgot_password_request.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function forgotPasswordChangeAction(Request $request, string $token, array $forgotPasswordConfig): Response
    {
        $route = $forgotPasswordConfig['logged_in_route'];

        if ($route && $this->authorizationChecker->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            return new RedirectResponse($this->router->generate($route));
        }

        $formType = $forgotPasswordConfig['change_form'];
        /**
         * @var TokenInterface $foundToken
         */
        $foundToken = $this->tokenService->findByValue($token);
        $this->tokenService->validate($foundToken);
        $user = $this->service->findByToken($foundToken);

        if (!$user) {
            throw $this->createNotFoundException();
        }

        $form = $this->createForm($formType, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->service->updatePassword($user);
            $this->service->save($user);
            $this->tokenService->remove($foundToken);

            $this->addFlash('success', 'sourecode.user.flash.forgot_password_changed');

            return $this->redirectToRoute($forgotPasswordConfig['changed_route']);
        }

        return $this->render(
            '@SoureCodeUser/Security/forgot_password_change.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function activateAction(string $token, array $activateConfig): Response
    {
        $this->service->activate($token);

        $this->addFlash('success', 'sourecode.user.flash.activated');

        return $this->redirectToRoute($activateConfig['success_route']);
    }

    public function changeEmailVerifyAction(string $token, array $changeEmailConfig): Response
    {
        $this->service->verifyChangeEmail($token);

        $this->addFlash('success', 'sourecode.user.flash.change_email_changed');

        return $this->redirectToRoute($changeEmailConfig['change_success_route']);
    }

    /**
     * Login check action. This action should never be called.
     */
    public function checkAction(): Response
    {
        throw new RuntimeException('You must configure the check path to be handled by the firewall.');
    }

    /**
     * Logout action. This action should never be called.
     */
    public function logoutAction(): Response
    {
        throw new RuntimeException('You must configure the logout path to be handled by the firewall.');
    }
}
