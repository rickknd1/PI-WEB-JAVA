<?php

namespace App\Form;

use App\Entity\Categories;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email')
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Administrateur' => 'ROLE_ADMIN',
                    'Super Administrateur' => 'ROLE_SUPER_ADMIN',
                    'Simple Utilisateur' => 'ROLE_USER',
                ],
                'multiple' => true, // Un seul rôle sélectionnable
                'expanded' => false, // Menu déroulant
                'placeholder' => 'Utilisateur (par défaut)',
                'required' => false,
            ])


            ->add('password')
            ->add('name')
            ->add('firstname')
            ->add('username')
            ->add('dateOB', null, [
                'widget' => 'single_text'
            ])
            ->add('gender', ChoiceType::class, [
                'choices' => [
                    'Homme' => 'homme',
                    'Femme' => 'femme',
                    'Autres' => 'autres',
                ],
                'expanded' => false, // Affiche un menu déroulant
                'multiple' => false, // Un seul choix possible
            ])

            ->add('interests', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'nom',
                'multiple' => true, // Permet la sélection multiple
                'expanded' => false, // Transforme le menu en liste de cases à cocher
            ])

            ->add("save", SubmitType::class,[
                'label' => 'Enregistrer'
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
