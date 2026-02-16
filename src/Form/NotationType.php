<?php

namespace App\Form;

use App\Entity\Critere;
use App\Entity\Notation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('note', null, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'max' => 20,
                    'step' => 0.5
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Note',
                'required' => true,
                'help' => 'Note sur 20 (0.5 par 0.5)'
            ])
            ->add('commentaire', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Commentaire',
                'required' => false
            ])
           
            ->add('critere', EntityType::class, [
                'class' => Critere::class,
                'choice_label' => 'libelle',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Critère',
                'placeholder' => 'Sélectionnez un critère',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Notation::class,
        ]);
    }
}
