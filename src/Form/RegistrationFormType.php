<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'First Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your first name']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your first name'
                ]
            ])
            ->add('lastName', TextType::class, [
                'label' => 'Last Name',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your last name']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your last name'
                ]
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new NotBlank(['message' => 'Please enter your email']),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your email'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'The password fields must match.',
                'first_options' => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
                'mapped' => false, // This field is not mapped to any entity property
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => 8,
                        'minMessage' => 'Your password must be at least {{ limit }} characters long',
                        // max length allowed by Symfony for security reasons
                        'max' => 4096,
                    ]),
                    new Regex([
                        'pattern' => '/\d/',
                        'message' => 'Your password must contain at least one digit',
                    ]),
                    new Regex([
                        'pattern' => '/[A-Z]/',
                        'message' => 'Your password must contain at least one uppercase letter',
                    ]),
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter your password'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
