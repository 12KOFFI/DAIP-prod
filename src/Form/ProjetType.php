<?php

namespace App\Form;

use App\Entity\partenaire;
use App\Entity\Projet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\File;

class ProjetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Nom du projet',
                'required' => true
            ])
            ->add('description', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Description',
                'required' => true
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
            ->add('logo', FileType::class, [
                'label' => 'Logo du projet',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou WebP)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image illustrative',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2048k',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG ou WebP)',
                    ])
                ],
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('id_partenaire', EntityType::class, [
                'class' => partenaire::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'by_reference' => false,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Partenaires',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Projet::class,
        ]);
    }
}
