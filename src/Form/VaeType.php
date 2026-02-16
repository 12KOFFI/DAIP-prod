<?php

namespace App\Form;

use App\Entity\Centre;
use App\Entity\Projet;
use App\Entity\Vae;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VaeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Nom de la VAE',
                'required' => true
            ])
            ->add('projet', EntityType::class, [
                'class' => Projet::class,
                'choice_label' => 'nom',
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Projet associé',
                'placeholder' => 'Sélectionnez un projet',
                'required' => true
            ])
            ->add('centres', EntityType::class, [
                'class' => Centre::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => false,
                'attr' => [
                    'class' => 'form-select',
                    'data-choices' => 'true',
                    'data-options' => '{"removeItemButton": true, "searchEnabled": true}'
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Centres  Vae associés',
                'placeholder' => 'Sélectionnez un ou plusieurs centres',
                'required' => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Vae::class,
        ]);
    }
}
