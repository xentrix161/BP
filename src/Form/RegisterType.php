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
                'attr' => [
                    'placeholder' => 'Zadaj meno',
                    'autofocus' => true,
                    'class'=> 'formInput'
                ]
            ])
            ->add('surname', TextType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj priezvisko',
                    'class'=> 'formInput'
                ]
            ])
            ->add('email', EmailType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj svoj email',
                    'class'=> 'formInput'
                ]
            ])
            ->add('password', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj heslo',
                    'class'=> 'formInput'
                ]
            ])
            ->add('password2', PasswordType::class, [
                'attr' => [
                    'placeholder' => 'Zadaj heslo znovu',
                    'class'=> 'formInput'
                ]
            ])
            ->add('Registruj', SubmitType::class, [
                'attr' => [
                    'class'=> 'btn btn-form',
                    'id' => 'btn-register'
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
