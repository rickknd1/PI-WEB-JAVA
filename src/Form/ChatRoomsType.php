<?php

namespace App\Form;

use App\Entity\ChatRooms;
use App\Entity\Community;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ChatRoomsType extends AbstractType
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
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Public' => 'Public',
                    'Private' => 'Private'
                ],
                'mapped' => true,
                'required' => true,
                'label'=>'Type&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('community', EntityType::class, [
                'class' => Community::class,
                'choice_label' => 'nom',
                'label'=>'Community&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add("save", SubmitType::class,[
                'label' => 'Enregistrer'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ChatRooms::class,
        ]);
    }
}
