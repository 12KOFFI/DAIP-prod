<?php

namespace App\Form;

use App\Entity\Centre;
use App\Entity\Metier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CentreType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('type', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('region', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('departement', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('adresse', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('contacts', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('metiers', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => [
                    'class' => 'form-select',
                    'data-choices' => 'true',
                    'data-options' => '{"removeItemButton": true, "searchEnabled": true}'
                ],
                'row_attr' => [
                    'class' => 'mb-3 form-group'
                ],
                'label_attr' => [
                    'class' => 'form-label'
                ],
                'placeholder' => 'Sélectionnez un ou plusieurs métiers',
                'help' => 'Sélectionnez les métiers disponibles dans ce centre',
                'help_attr' => [
                    'class' => 'form-text text-muted'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Centre::class,
        ]);
    }
}
