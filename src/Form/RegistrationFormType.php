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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Last name is required'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Your last name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your last name cannot be longer than {{ limit }} characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Your last name can only contain letters, spaces, hyphens and apostrophes'
                    ])
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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'First name is required'
                    ]),
                    new Assert\Length([
                        'min' => 2,
                        'max' => 50,
                        'minMessage' => 'Your first name must be at least {{ limit }} characters long',
                        'maxMessage' => 'Your first name cannot be longer than {{ limit }} characters'
                    ]),
                    new Assert\Regex([
                        'pattern' => '/^[a-zA-ZÀ-ÿ\-\' ]+$/',
                        'message' => 'Your first name can only contain letters, spaces, hyphens and apostrophes'
                    ])
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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Email address is required'
                    ]),
                    new Assert\Email([
                        'message' => 'Please enter a valid email address',
                        'mode' => 'strict'
                    ]),
                    new Assert\Length([
                        'max' => 180,
                        'maxMessage' => 'Email address cannot be longer than {{ limit }} characters'
                    ])
                ]
            ])
            ->add('gender', ChoiceType::class, [
                'label' => 'Gender',
                'choices' => [
                    'Prefer not to say' => 'not_specified',
                    'Male' => 'male',
                    'Female' => 'female',
                    'Non-binary' => 'non_binary',
                    'Other' => 'other'
                ],
                'placeholder' => 'Select your gender',
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Please select your gender'
                    ])
                ]
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
                'constraints' => [
                    new Assert\NotBlank([
                        'message' => 'Date of birth is required'
                    ]),
                    new Assert\LessThanOrEqual([
                        'value' => "-13 years",
                        'message' => 'You must be at least 13 years old to register'
                    ]),
                    new Assert\GreaterThanOrEqual([
                        'value' => "-100 years",
                        'message' => 'Please enter a valid date of birth'
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
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