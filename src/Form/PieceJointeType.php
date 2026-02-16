<?php

namespace App\Form;

use App\Entity\Candidature;
use App\Entity\PieceJointe;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PieceJointeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Nom du fichier',
                'required' => true
            ])
            ->add('chemin', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Chemin du fichier',
                'required' => true,
                'help' => 'Chemin d\'accès au fichier sur le serveur'
            ])
            ->add('type', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Type de pièce jointe',
                'required' => true
            ])
            ->add('numPieceJointe', null, [
                'attr' => ['class' => 'form-control'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Numéro de pièce jointe',
                'required' => true
            ])
            ->add('candidature', EntityType::class, [
                'class' => Candidature::class,
                'choice_label' => function($candidature) {
                    $formationName = $candidature->getFormation() ? $candidature->getFormation()->getLibelle() : 'Aucune formation';
                    return $candidature->getNom() . ' ' . $candidature->getPrenom() . ' - ' . $formationName;
                },
                'attr' => ['class' => 'form-select'],
                'row_attr' => ['class' => 'mb-3'],
                'label' => 'Candidature',
                'placeholder' => 'Sélectionnez une candidature',
                'required' => true
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PieceJointe::class,
        ]);
    }
}
