<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class,  [
                'label' => 'Názov',
            ])
            ->add('desc', TextType::class, [
                'label' => 'Popis',
            ])
            ->add('price', TextType::class, [
                'label' => 'Cena',
            ])
            ->add('img', FileType::class, [
                'required' => $options['img_is_required'],
                'mapped' => false,
                'label' => 'Prosím vložte obrázok'
            ])
            ->add('available')
            ->add('amount')
            ->add('cat_id')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'img_is_required' => false
        ]);

        $resolver->setAllowedTypes('img_is_required', 'bool');
    }
}
