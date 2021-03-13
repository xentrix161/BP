<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Meno',
                'attr' => [
                    'required' => true,
                    'class' => 'formInput',
                ]
            ])
            ->add('surname', TextType::class, [
                'label' => 'Priezvisko',
                'attr' => [
                    'required' => true,
                    'class' => 'formInput',
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'required' => true,
                    'class' => 'formInput',
                ]
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Heslo',
                'required' => true,
                'attr' => [
                    'class' => 'formInput',
                    'value' => ""
                ]
            ])
            ->add('password2', PasswordType::class, [
                'label' => 'Kontrolné heslo',
                'required' => true,
                'attr' => [
                    'class' => 'formInput',
                    'value' => ""
                ]
            ])
            ->add('Save', SubmitType::class, [
                'attr' => [
                    'class'=> 'button'
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
