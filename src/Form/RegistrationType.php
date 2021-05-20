<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'attr' => [
                    'maxlenght' => false,
                ],
                'required' => false
            ])
            ->add('prenom', TextType::class, [
                'attr' => [
                    'maxlenght' => false,
                ],
                'required' => false
            ])
            ->add('email', TextType::class, [
                'attr' => [
                    'maxlenght' => false,
                ],
                'required' => false
            ])
            ->add('username', TextType::class, [
                'attr' => [
                    'maxlenght' => false,
                ],
                'required' => false
            ])
            ->add('password', PasswordType::class)
            ->add('confirm_password', PasswordType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
