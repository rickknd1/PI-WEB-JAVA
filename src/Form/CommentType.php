<?php

namespace App\Form;

use App\Entity\Comment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label' => 'Votre commentaire',
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le commentaire est obligatoire.']),
                    new Assert\Length([
                        'min' => 3,
                        'max' => 1000,
                        'minMessage' => 'Le commentaire doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le commentaire ne peut pas dépasser {{ limit }} caractères.'
                    ]),
                ],
            ])
            ->add('created_at', null, [
                'widget' => 'single_text',
            ])
            ->add('updated_at', null, [
                'widget' => 'single_text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
