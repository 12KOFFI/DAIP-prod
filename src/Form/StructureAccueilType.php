<?php

namespace App\Form;

use App\Entity\StructureAccueil;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StructureAccueilType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomStructure', null, [
                'label' => 'Nom de la structure prospecter <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'Type de structure prospecter <span class="text-danger">*</span>',
                'label_html' => true,
                'choices' => [
                    'Entreprise' => 'ENTREPRISE',
                    'Atelier Artisanal' => 'ATELIER ARTISANAL'
                ],
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
            ])
            ->add('localite', null, [
                'label' => 'Localité <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('telephoneStructure', TextType::class, [
                'label' => 'Contact de la structure <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 10,
                    'inputmode' => 'numeric',
                    'pattern' => '^\\d{10}$',
                    'placeholder' => '10 chiffres (sans +225)'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('secteurActivite', null, [
                'label' => 'Secteur d\'activité <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('nomResponsable', null, [
                'label' => 'Nom du chef d\'entreprise ou du maître artisan <span class="text-danger">*</span>',
                'label_html' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'nom complet'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
            ->add('telephoneResponsable', TextType::class, [
                'label' => 'Contact du chef d\'entreprise ou du maître artisan <span class="text-danger">*</span>',
                 'label_html' => true,
                'attr' => [
                    'class' => 'form-control',
                    'maxlength' => 10,
                    'inputmode' => 'numeric',
                    'pattern' => '^\\d{10}$',
                    'placeholder' => '10 chiffres (sans +225)'
                ],
                'row_attr' => ['class' => 'mb-3']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => StructureAccueil::class,
        ]);
    }
}
