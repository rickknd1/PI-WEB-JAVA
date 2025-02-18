<?php

namespace App\Form;

use App\Entity\Gamifications;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GamificationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('description')
            ->add('type_abonnement', ChoiceType::class, [
                'choices' => [
                    'Normal' => 'Normal',
                    'Premium' => 'Premium',
                ],
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
