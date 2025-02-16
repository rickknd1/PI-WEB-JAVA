<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextType::class, [
                'label' => 'texte',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'le texte est obligatoire.']),
                    new Assert\Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Le texte doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le texte ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('file')
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('update_at', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
