<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\Community;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use function Webmozart\Assert\Tests\StaticAnalysis\length;

class CommunityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class,[
                'constraints' => new Assert\Length([
                    'min' => 3,
                    'minMessage' => 'Votre nom est trop court ! Min 3 character',
                ]),
            ])
            ->add('description', TextareaType::class,[
                'constraints' => new Assert\Length([
                    'min' => 3,
                    'minMessage' => 'Votre description est trop court ! Min 3 character',
                    ]),
            ])
            ->add('cover' ,FileType::class,[
                'label' => 'Choisissez une image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Please upload a valid image (jpeg, png, gif, webp)',
                    ]),
                    new Assert\NotBlank([
                        'message' => 'Cover cannot be empty'
                    ]),
                ],
            ])
            ->add('id_categorie', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'nom',
                'label' => 'Catégorie&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add("save", SubmitType::class,[
                'label' => 'Enregistrer'
            ])
            ->add('_token', HiddenType::class, [
                'mapped' => false,
            ]);
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Community::class,
        ]);
    }
}
