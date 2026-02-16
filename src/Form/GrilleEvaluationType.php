<?php

namespace App\Form;

use App\Entity\GrilleEvaluation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GrilleEvaluationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4
                ],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Nom',
                'help' => 'Saisissez le nom de la grille d\'évaluation'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => GrilleEvaluation::class,
        ]);
    }
}
