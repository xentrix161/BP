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

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Name', TextType::class, [
                'label' => 'Meno',
                'attr' => [
                    'placeholder' => 'Zadajte meno',
                    'autofocus' => true,
                    'class' => 'formInput',
                ]
            ])
            ->add('surname', TextType::class, [
                'label' => 'Priezvisko',
                'attr' => [
                    'placeholder' => 'Zadajte priezvisko',
                    'class'=> 'formInput'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'Zadajte svoj email',
                    'class'=> 'formInput'
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Heslo',
                'attr' => [
                    'placeholder' => 'Zadajte heslo',
                    'class'=> 'formInput'
                ]
            ])
            ->add('password2', PasswordType::class, [
                'label' => 'Kontrolné heslo',
                'attr' => [
                    'placeholder' => 'Zadajte heslo znovu',
                    'class'=> 'formInput',
                ]

            ])
            ->add('Zaregistrovat', SubmitType::class, [
                'attr' => [
                    'class'=> 'btn btn-form',
                    'id' => 'btn-register'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
