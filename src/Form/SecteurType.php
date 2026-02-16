<?php

namespace App\Form;

use App\Entity\Secteur;
use App\Entity\Metier;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecteurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom du secteur',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Bâtiment Travaux Publics (BTP)'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => true
            ])
            ->add('dureeFormation', IntegerType::class, [
                'label' => 'Durée de formation (mois)',
                'attr' => ['class' => 'form-control', 'min' => 1, 'max' => 24, 'placeholder' => '3 ou 6'],
                'row_attr' => ['class' => 'mb-3'],
                'required' => false,
                'help' => 'Durée en mois (ex: 3 ou 6)'
            ]);
           
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Secteur::class,
        ]);
    }
}
