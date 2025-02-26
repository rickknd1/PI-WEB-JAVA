<?php

namespace App\Form;

use App\Entity\Community;
use App\Entity\Events;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormEvent;
use function PHPUnit\Framework\greaterThan;

class EventsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'constraints' => new Assert\Length([
                    'min' => 3,
                    'minMessage' => 'Votre nom est trop court ! Min 3 caractères',
                ]),
            ])
            ->add('description', TextareaType::class, [
                'constraints' => new Assert\Length([
                    'min' => 3,
                    'minMessage' => 'Votre description est trop courte ! Min 3 caractères',
                ]),
            ])
            ->add('cover', FileType::class, [
                'label' => 'Choisissez une image',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Assert\File([
                        'maxSize' => '5M',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (jpeg, png, gif, webp)',
                    ]),
                    new Assert\NotBlank([
                        'message' => 'Cover cannot be empty'
                    ]),
                ],
            ])
            ->add('startedAt', null, [
                'widget' => 'single_text'
            ])
            ->add('finishAt', null, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\GreaterThan([
                        'propertyPath' => 'parent.all[startedAt].data',
                        'message' => 'The finish date must be after the start date.'
                    ])
                ]
            ])
            ->add('lieu')
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Présentiel' => 'Presentiel',
                    'En Ligne' => 'En Ligne'
                ],
                'mapped' => true,
                'required' => true,
                'label'=>'Type&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('acces', ChoiceType::class, [
                'choices' => [
                    'Public' => 'Public',
                    'Private' => 'Private'
                ],
                'mapped' => true,
                'required' => true,
                'label'=>'Acces&nbsp&nbsp&nbsp',
                'label_html' => true,
            ])
            ->add('id_community', EntityType::class, [
                'class' => Community::class,
                'choice_label' => 'nom',
                'label' => 'Nom de la Communauté&nbsp&nbsp&nbsp',
                'label_html' => true,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data instanceof Events && $data->getType() === 'En Ligne') {
                $form->add('link', TextType::class, [
                    'required' => false,
                ]);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if (!isset($data['type'])) {
                return;
            }

            if ($data['type'] === 'En Ligne') {
                $form->add('link', TextType::class, [
                    'required' => true,
                ]);
            } else {
                $data['link'] = null;
                $event->setData($data);
            }
        });

        $builder->add("save", SubmitType::class, [
            'label' => 'Enregistrer'
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Events::class,
            'allow_extra_fields' => true,
        ]);
    }
}

