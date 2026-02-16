<?php

namespace App\Form;

use App\Entity\Metier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use App\Entity\NiveauEtude;
use App\Entity\Diplome;
use App\Entity\Secteur;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MetierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('niveauEtude', EntityType::class, [
                'class' => NiveauEtude::class,
                'choice_label' => 'libelle',
                'label' => 'Niveau d\'étude associé',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'placeholder' => 'Sélectionnez un niveau',
            ])
            ->add('secteur', EntityType::class, [
                'class' => Secteur::class,
                'choice_label' => 'nom',
                'label' => 'Secteur d\'activité',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'placeholder' => 'Sélectionnez un secteur',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('diplomes', EntityType::class, [
                'class' => Diplome::class,
                'choice_label' => 'libelle',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-select',
                    'data-choices' => 'true',
                    'data-placeholder' => 'Sélectionnez un ou plusieurs diplômes'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Diplômes associés',
                'required' => false,
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image descriptive',
                'required' => false,
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou GIF)',
                    ])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Metier::class,
        ]);
    }
}
