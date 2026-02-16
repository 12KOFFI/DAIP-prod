<?php

namespace App\Form;

use App\Entity\Critere;
use App\Entity\GrilleEvaluation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CritereType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('grilleEvaluation', EntityType::class, [
                'class' => GrilleEvaluation::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'placeholder' => 'Sélectionnez une grille d\'évaluation'
            ])
            ->add('bareme', null, [
                'label' => 'Barème',
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'step' => 1
                ],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'help' => 'Points maximum attribuables pour ce critère'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Critere::class,
            'is_jury' => false,
        ]);

        $resolver->setAllowedTypes('is_jury', 'bool');
    }
}
