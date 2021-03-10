<?php

namespace SoureCode\Bundle\User\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RepeatedPasswordType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'type' => PasswordType::class,
                'required' => true,
                'invalid_message' => 'sourecode.user.form.password.invalid',
                'first_options' => [
                    'label' => 'sourecode.user.form.password.label',
                    'attr' => [
                        'placeholder' => 'sourecode.user.form.password.label',
                    ],
                ],
                'second_options' => [
                    'label' => 'sourecode.user.form.password.repeat',
                    'attr' => [
                        'placeholder' => 'sourecode.user.form.password.repeat',
                    ],
                ],
            ]
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getParent(): string
    {
        return RepeatedType::class;
    }
}
