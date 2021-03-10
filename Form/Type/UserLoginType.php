<?php

namespace SoureCode\Bundle\User\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class UserLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                '_email',
                TextType::class,
                [
                    'label' => 'sourecode.user.form.login.email',
                    'attr' => [
                        'placeholder' => 'sourecode.user.form.login.email',
                    ],
                ]
            )
            ->add(
                '_password',
                PasswordType::class,
                [
                    'label' => 'sourecode.user.form.login.password',
                    'attr' => [
                        'placeholder' => 'sourecode.user.form.login.password',
                    ],
                ]
            )
            ->add(
                '_remember_me',
                CheckboxType::class,
                [
                    'label' => 'sourecode.user.form.login.remember_me',
                    'data' => true,
                    'required' => false,
                ]
            );
    }

    public function getBlockPrefix(): string
    {
        return 'sourecode_user_login';
    }
}
