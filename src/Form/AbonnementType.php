<?php

namespace App\Form;

use App\Entity\Abonnements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class AbonnementType extends AbstractType
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
            ->add('prix', NumberType::class, [
                'constraints' => new Assert\GreaterThanOrEqual([
                        'value' => 10,
                        'message' => 'Le prix ne peut pas être inférieur à 10.',
                ]),
            ])
            ->add('avantages', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => ['attr' => ['class' => 'advantage-field']],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true, // Enables dynamic adding of new fields
                'prototype_name' => '__name__', // Placeholder for the dynamic name
                'attr' => ['class' => 'avantages'], // Class for the container
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Normal' => 'Normal',
                    'Premium' => 'Premium'
                ],
                'label'=>'Type&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Abonnements::class,
        ]);
    }
}
