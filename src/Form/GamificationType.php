<?php

namespace App\Form;

use App\Entity\Abonnements;
use App\Entity\Gamifications;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class GamificationType extends AbstractType
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
            ->add('description')
            ->add('type_abonnement', EntityType::class, [
                'class' => Abonnements::class,
                'choice_label' => 'nom',
                'mapped' => true,
                'required' => true,
                'label'=>'Type abonnement&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Recompense' => 'Recompense',
                    'Reduction' => 'Reduction'
                ],
                'mapped' => true,
                'required' => true,
                'label'=>'Type&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('condition_gamification')
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Gamifications::class,
        ]);
    }
}
