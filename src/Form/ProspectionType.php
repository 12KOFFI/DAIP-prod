<?php

namespace App\Form;

use App\Entity\Prospection;
use App\Entity\CfaEtablissement;
use App\Entity\StructureAccueil;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProspectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date', null, [
                'label' => 'Date de prospection',
                'widget' => 'single_text',
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('cfaEtablissement', EntityType::class, [
                'label' => 'CFA/Établissement',
                'class' => CfaEtablissement::class,
                'choice_label' => 'nomEtablissement',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez un CFA/Établissement'
            ])
            ->add('structureAcceuil', EntityType::class, [
                'label' => 'Structure d\'accueil',
                'class' => StructureAccueil::class,
                'choice_label' => 'nomStructure',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez une structure d\'accueil'
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Prospection::class,
        ]);
    }
}
