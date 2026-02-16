<?php

namespace App\Form;

use App\Entity\Diplome;
use App\Entity\Filiere;
use App\Entity\Metier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DiplomeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('libelle', null, [
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Entrez le nom du diplôme'
                ],
                'row_attr' => ['class' => 'mb-3']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Diplome::class,
        ]);
    }
}
