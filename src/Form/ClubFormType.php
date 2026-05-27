<?php

namespace App\Form;

use App\Entity\Club;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ClubFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Club Name',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Enter club name',
                ],
                'label_attr' => ['class' => 'form-label fw-semibold text-muted'],
            ])
            ->add('category', TextType::class, [
                'label' => 'Category',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'e.g. Tech, Sports, Arts',
                ],
                'label_attr' => ['class' => 'form-label fw-semibold text-muted'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Tell us about your club...',
                ],
                'label_attr' => ['class' => 'form-label fw-semibold text-muted'],
            ])
            ->add('coverImgFile', FileType::class, [
                'label' => 'Cover Image',
                'mapped' => false,  // not directly tied to a Club property
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
                'label_attr' => ['class' => 'form-label fw-semibold text-muted'],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Please upload a valid image (JPG, PNG, WebP)',
                    ),
                ],
            ])
            ->add('profileImgFile', FileType::class, [
                'label' => 'Profile Image',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'accept' => 'image/*'],
                'label_attr' => ['class' => 'form-label fw-semibold text-muted'],
                'constraints' => [
                    new File(
                        maxSize: '2M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Please upload a valid image (JPG, PNG, WebP)',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Club::class,
        ]);
    }
}
