<?php
// src/Form/EventType.php
//
// Définition du formulaire pour créer/éditer un événement.
// Remplace : le HTML <form> dans public/create-event.php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Event Title',
                'attr' => [
                    'placeholder' => 'e.g. Flutter Workshop',
                ],
                'constraints' => [
                    new NotBlank(message: 'Please enter a title'),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                    'placeholder' => 'What is this event about?',
                ],
            ])
            ->add('eventDate', DateTimeType::class, [
                'label' => 'Date & Time',
                'widget' => 'single_text',  // utilise l'input HTML5 datetime-local
                'constraints' => [
                    new NotBlank(message: 'Please pick a date'),
                ],
            ])
            ->add('place', TextType::class, [
                'label' => 'Location',
                'required' => false,
                'attr' => [
                    'placeholder' => 'e.g. INSAT Hall A',
                ],
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Event Image',
                'mapped' => false,  // pas lié directement à une propriété de Event
                'required' => false,
                'attr' => ['accept' => 'image/*'],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Please upload a JPG, PNG, or WebP image',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
        ]);
    }
}