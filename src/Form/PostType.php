<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;



class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('visibility', ChoiceType::class, [
                'choices' => [
                    'Public' => 'public',
                    'Private' => 'private',
                    'Restricted' => 'restricted',
                ],
                'required' => true, // Set to false if optional
                'empty_data' => 'public', // Default value if nothing is selected
            ])
            ->add('titre', TextType::class, [
                'label' => 'Titre',
                'attr' => ['placeholder' => 'Saisissez le titre'],
                'constraints' => [
                    new NotBlank(['message' => 'Le titre ne peut pas être vide.']),
                    new Length([
                        'min' => 5,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères.',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères.'
                    ])
                ]
            ])
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
            ->add('file', FileType::class, [
                'label' => 'Choisissez une image',
                'mapped' => false,
                'required' => false,
            ])
            ->add("save", SubmitType::class,[
                'label' => 'Envoyer'
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
