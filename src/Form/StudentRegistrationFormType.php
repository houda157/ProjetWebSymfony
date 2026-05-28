<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class StudentRegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullname', TextType::class, [
                'label' => 'Full name',
                'constraints' => [
                    new Assert\NotBlank(message: 'Full name is required.'),
                    new Assert\Length(max: 100, maxMessage: 'Full name cannot be longer than {{ limit }} characters.'),
                ],
            ])
            ->add('username', TextType::class, [
                'label' => 'Username',
                'constraints' => [
                    new Assert\NotBlank(message: 'Username is required.'),
                    new Assert\Length(max: 50, maxMessage: 'Username cannot be longer than {{ limit }} characters.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'constraints' => [
                    new Assert\NotBlank(message: 'Email is required.'),
                    new Assert\Email(message: 'Enter a valid email address.'),
                    new Assert\Length(max: 255, maxMessage: 'Email cannot be longer than {{ limit }} characters.'),
                ],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Verify your password.',
                'first_options' => [
                    'label' => 'Password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'second_options' => [
                    'label' => 'Repeat password',
                    'attr' => ['autocomplete' => 'new-password'],
                ],
                'constraints' => [
                    new Assert\NotBlank(message: 'Password is required.'),
                    new Assert\Length(min: 8, minMessage: 'Password must have at least {{ limit }} characters.'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_token_id' => 'register_student',
            'data_class' => null,
        ]);
    }
}
