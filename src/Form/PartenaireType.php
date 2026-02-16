<?php

namespace App\Form;

use App\Entity\Partenaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class PartenaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Nom du partenaire',
                'required' => true
            ])
            ->add('logoFile', FileType::class, [
                'label' => 'Logo',
                'required' => false,
                'mapped' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, GIF)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'help' => 'Formats acceptés : JPG, PNG, GIF (max 2Mo)'
            ])
            ->add('email', null, [
                'attr' => [
                    'class' => 'form-control',
                    'type' => 'email'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Email',
                'required' => true
            ])
            ->add('contact', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Contact',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Partenaire::class,
        ]);
    }
}
