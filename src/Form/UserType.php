<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            // src/Form/UserType.php

            ->add('role', ChoiceType::class, [
                'choices' => [
                    'User' => 'ROLE_USER',
                    'Admin' => 'ROLE_ADMIN',
                    'Super Admin' => 'ROLE_SUPER_ADMIN',
                ],
                'label' => 'Rôle',
                'multiple' => false, // Un seul rôle peut être sélectionné
                'expanded' => false,  // Afficher sous forme de boutons radio
            ])
            ->add('password')
            ->add('name')
            ->add('firstname')
            ->add('username')
            ->add('dateOB', DateType::class, [
                'widget' => 'single_text', // Utilise un input de type "date" en HTML5
                'format' => 'yyyy-MM-dd', // Format de la date
                'required' => true, // Rend le champ obligatoire
            ])
            ->add('pp', FileType::class, [
                'label' => 'Photo de profil',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image au format JPG ou PNG',
                    ])
                ],
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                    'Autres' => 'autres',
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('interests', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'custom-select',
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
