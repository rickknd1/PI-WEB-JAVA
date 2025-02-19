<?php

namespace App\Form;

use App\Entity\LieuCulturels;
use App\Entity\Media;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class MediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('link', FileType::class, [
            'label' => 'Choisissez un fichier média',
            'mapped' => false, // Not mapped to the `link` property directly
            'required' => false, // Optional
            'constraints' => [
                new File([
                    'maxSize' => '5M', // Maximum file size: 5 MB
                    'mimeTypes' => [
                        'image/jpeg',
                        'image/jpg',
                        'image/png',
                        'image/gif',
                        'image/webp',
                        'video/mp4',
                        'audio/mpeg',
                    ],
                    'mimeTypesMessage' => 'Veuillez télécharger un fichier valide (jpeg, png, gif, webp, mp4, mp3)',
                ]),
            ],
            'attr' => [
                'class' => 'form-control',
            ],
        ])
        ->add('type', ChoiceType::class, [
            'choices' => [
                'Image' => 'image',
                'Vidéo' => 'video',
            ],
            'placeholder' => 'Sélectionnez le type de média',
            'required' => true,
        ])
        
            ->add('lieux', EntityType::class, [
                'class' => LieuCulturels::class,
                'choice_label' => 'id',
                'label' => 'Lieu culturel associé',
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Media::class,
        ]);
    }
}

