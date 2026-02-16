<?php

namespace App\Form;

use App\Entity\CfaMetier;
use App\Entity\Metier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class CfaMetierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('metier', EntityType::class, [
                'class' => Metier::class,
                'choice_label' => 'nom',
                'placeholder' => 'Sélectionnez un métier',
                'attr' => ['class' => 'form-control'],
                'label' => 'Métier',
                'required' => true,
            ])
            ->add('effectif', IntegerType::class, [
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0,
                    'placeholder' => 'Nombre d\'apprentis'
                ],
                'label' => 'Effectif',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'L\'effectif est obligatoire'
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'L\'effectif doit être supérieur ou égal à 0'
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CfaMetier::class,
        ]);
    }
}
