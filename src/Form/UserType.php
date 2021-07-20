<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email')
            ->add('roles')
            ->add('password')
            ->add('pseudo')
            ->add('lastname')
            ->add('firstname')
            ->add('description')
            ->add('picture')
            ->add('address')
            ->add('zip_code')
            ->add('city')
            ->add('holiday_mode')
            ->add('status')
            ->add('latitude')
            ->add('longitude')
            ->add('created_at')
            ->add('updated_at')
            ->add('chats')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
