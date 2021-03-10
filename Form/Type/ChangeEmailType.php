<?php

namespace SoureCode\Bundle\User\Form\Type;

use SoureCode\Bundle\User\Form\Model\ChangeEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                RepeatedType::class,
                [
                    'type' => EmailType::class,
                    'required' => true,
                    'invalid_message' => 'sourecode.user.form.change_email.invalid',
                    'first_options' => [
                        'label' => 'sourecode.user.form.change_email.label',
                        'attr' => [
                            'placeholder' => 'sourecode.user.form.change_email.label',
                        ],
                    ],
                    'second_options' => [
                        'label' => 'sourecode.user.form.change_email.repeat',
                        'attr' => [
                            'placeholder' => 'sourecode.user.form.change_email.repeat',
                        ],
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ChangeEmail::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'sourecode_user_change_email';
    }
}
