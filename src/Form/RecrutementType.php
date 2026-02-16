<?php

namespace App\Form;

use App\Entity\Projet;
use App\Entity\Recrutement;
use App\Entity\Centre;
use App\Entity\Metier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\ColorType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\File;

class RecrutementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Libellé du recrutement',
                'required' => true
            ])
            ->add('dateDebut', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Date de début',
                'required' => true
            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Date de fin',
                'required' => true
            ])
          
            ->add('ageMin', IntegerType::class, [
                'label' => 'Âge minimum',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 16, 'max' => 100, 'placeholder' => 'Ex: 18'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('ageMax', IntegerType::class, [
                'label' => 'Âge maximum',
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 16, 'max' => 100, 'placeholder' => 'Ex: 35'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('dateLimiteAge', DateType::class, [
                'label' => 'Date limite pour l\'âge',
                'widget' => 'single_text',
                'required' => false,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
              
            ])
            ->add('nationalite', TextType::class, [
                'label' => 'Nationalité',
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Ivoirienne'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'OUVERT' => 'OUVERT',
                    'FERME' => 'FERME',
                    'EN ATTENTE' => 'EN ATTENTE',
                    'ACHEVE' => 'ACHEVE',
                ],
                'placeholder' => 'Sélectionnez un statut',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Statut',
                'required' => true
            ])
            ->add('texte_annonce', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 5
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Texte de l\'annonce ( texte Bannière)',
                'required' => false
            ])
           
           
            ->add('imageAnnonce', FileType::class, [
                'label' => 'Image illustrant les conditions de candidature',
                'mapped' => false,
                'required' => false,
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
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('date_inscription', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Période d\'inscription ( info Bannière)',
                'required' => false
            ])
            ->add('listeParcours', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'liste-parcours-collection'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'label' => false, // Supprime le label du champ principal
                'entry_options' => [
                    'label' => false, // Supprime le label pour chaque élément du prototype
                ],
            ])
            ->add('conCandidature', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'con-candidature-collection'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'label' => false, // Supprime le label du champ principal
                'entry_options' => [
                    'label' => false, // Supprime le label pour chaque élément du prototype
                ],
            ])
            ->add('DosCandidature', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'attr' => ['class' => 'dos-candidature-collection'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'label' => false, // Supprime le label du champ principal
                'entry_options' => [
                    'label' => false, // Supprime le label pour chaque élément du prototype
                ],
            ])

            ->add('texteParcours', null, [
                'attr' => [
                    'class' => 'form-control tinymce',
                    'rows' => 5
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Texte descriptif des parcours',
                'required' => false
            ])
            ->add('metiers', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Métiers concernés',
            ])
            
            ->add('image', FileType::class, [
                'label' => 'Image principale (affichée dans les offres)',
                'mapped' => false,
                'required' => false,
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
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/svg+xml',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un logo valide (JPEG, PNG ou SVG)',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                ]
            ])
            ->add('banniere', FileType::class, [
                'label' => 'Bannière',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG ou PNG)',
                    ])
                ],
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                ]
            ])
                        ->add('centre', EntityType::class, [
                'class' => Centre::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],

            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recrutement::class,
        ]);
    }
}
