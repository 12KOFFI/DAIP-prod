<?php

namespace App\Form;

use App\Entity\CfaEtablissement;
use App\Entity\Formation;
use App\Entity\Projet;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('description', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('dateDebut', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('dateFin', null, [
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('ageMinimum', null, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Âge minimum',
                    'title' => 'Âge minimum requis pour la formation'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'help' => 'L\'âge minimum requis pour s\'inscrire'
            ])
            ->add('ageMaximum', null, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Âge maximum',
                    'title' => 'Âge maximum autorisé pour la formation'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'help' => 'L\'âge maximum autorisé pour s\'inscrire'
            ])
            ->add('publicCible', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Public cible',
                    'title' => 'Ex: Jeunes diplômés, demandeurs d\'emploi, etc.'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'help' => 'Exemple: Jeunes diplômés, Demandeurs d\'emploi, etc.'
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Brouillon' => 'Brouillon',
                    'Publié' => 'Publié',
                    'Archivé' => 'Archivé'
                ],
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('image', FileType::class, [
                'label' => 'Image principale',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('banniere', FileType::class, [
                'label' => 'Bannière',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('logo', FileType::class, [
                'label' => 'Logo',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/*'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('cfa_etablissement', EntityType::class, [
                'class' => CfaEtablissement::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez un établissement CFA'
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez un projet'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Formation::class,
        ]);
    }
}
