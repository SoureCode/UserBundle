<?php

namespace SoureCode\Bundle\User\Form\Type;

use SoureCode\Bundle\Common\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;

class UserRegisterType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'email',
                EmailType::class,
                [
                    'label' => 'sourecode.user.form.register.email',
                    'attr' => [
                        'placeholder' => 'sourecode.user.form.register.email',
                    ],
                ]
            )
            ->add('plainPassword', RepeatedPasswordType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'sourecode_user_register';
    }
}
