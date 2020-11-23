<?php

namespace App\Form;

use App\Entity\User;

//use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Mime\Email;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name', TextType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj meno',
                    'autofocus' => true,
                    'class'=> 'formInput'
                ]
            ])
            ->add('surename', TextType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj priezvisko',
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj svoj email'
                ]
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj heslo',
                ]

            ])
            ->add('Registruj', SubmitType::class, [
                'attr' => [
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
