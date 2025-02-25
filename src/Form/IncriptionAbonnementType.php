<?php

namespace App\Form;

use App\Entity\Abonnements;
use App\Entity\InscriptionAbonnement;
use App\Entity\user;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IncriptionAbonnementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('mode_paiement',ChoiceType::class, [
                'choices' => [
                    'Flouci' => 'Flouci',
                    'D17' => 'D17',
                    'Carte Bancaire' => 'Carte Bancaire',
                ],
                'mapped' => true,
                'required' => true,
                'label'=>'Mode de paiement &nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('renouvellement_auto',ChoiceType::class, [
                'choices' => [
                    'Oui' => True,
                    'Non' => False,
                ],
                'mapped' => true,
                'required' => true,
                'label'=>'Renouvellement automatique &nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('save', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InscriptionAbonnement::class,
        ]);
    }
}
