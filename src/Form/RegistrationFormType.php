<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Categories;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\String\UnicodeString;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $currentYear = (int)date('Y');

        $builder
            ->add('name', TextType::class, [
                'label' => 'Last Name',
                'attr' => [
                    'maxlength' => 50,
                    'placeholder' => 'Enter your last name',
                    'autocomplete' => 'family-name'
                ],

                'trim' => true
            ])
            ->add('firstname', TextType::class, [
                'label' => 'First Name',
                'attr' => [
                    'maxlength' => 50,
                    'placeholder' => 'Enter your first name',
                    'autocomplete' => 'given-name'
                ],

                'trim' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email Address',
                'attr' => [
                    'maxlength' => 180,
                    'placeholder' => 'example@domain.com',
                    'autocomplete' => 'email'
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
            ->add('dateOB', DateType::class, [
                'label' => 'Date of Birth',
                'widget' => 'choice',
                'years' => range($currentYear - 100, $currentYear - 13),
                'format' => 'dd MM yyyy',
                'placeholder' => [
                    'year' => 'Year',
                    'month' => 'Month',
                    'day' => 'Day'
                ],

            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'label' => 'Password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Enter your password'
                    ]
                ],
                'second_options' => [
                    'label' => 'Confirm Password',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'placeholder' => 'Confirm your password'
                    ]
                ],
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Password is required'
                    ]),
                    new Assert\Length([
                        'min' => 8,
                        'max' => 4096,
                        'minMessage' => 'Your password must be at least {{ limit }} characters long'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
                        'message' => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character'
                    ])
                ]
            ])
            ->add('interests', EntityType::class, [
                'class' => Categories::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'choice_attr' => function (Categories $category) {
                    return [
                        'data-cover' => $category->getCover(),
                        'data-description' => $category->getDescription()
                    ];
                },
                'constraints' => [
                    new Assert\Count([
                        'min' => 2,
                        'max' => 4,
                        'minMessage' => 'Please select at least {{ limit }} interests',
                        'maxMessage' => 'You cannot select more than {{ limit }} interests'
                    ])
                ],
                'by_reference' => false
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'label' => false,
                'constraints' => [
                    new Assert\IsTrue([
                        'message' => 'You must accept our Terms of Service and Privacy Policy to register'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate', // Désactive la validation HTML5
            ],
            'validation_groups' => ['Default', 'Registration']
        ]);
    }

}