<?php

namespace SoureCode\Bundle\User\Form\Type;

use SoureCode\Bundle\User\Form\Model\ForgotPasswordRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'sourecode.user.form.forgot_password.email',
                'attr' => [
                    'placeholder' => 'sourecode.user.form.forgot_password.email',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => ForgotPasswordRequest::class,
            ]
        );
    }

    public function getBlockPrefix(): string
    {
        return 'sourecode_user_forgot_password';
    }
}
