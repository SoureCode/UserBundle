<?php

namespace SoureCode\Bundle\User\Form\Type;

use SoureCode\Bundle\Common\Form\Type\AbstractResourceType;
use Symfony\Component\Form\FormBuilderInterface;

class ChangePasswordType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedPasswordType::class);
    }

    public function getBlockPrefix(): string
    {
        return 'sourecode_user_change_password';
    }
}
